@php
    $chartId = 'vendorsMaintenanceChart_'.uniqid();
    $items = isset($items) ? $items : (isset($vendors) ? $vendors : collect());
@endphp

<div id="{{ $chartId }}-root" class="vendors-maintenance-card" style="background:#fff;border-radius:6px;box-shadow:0 2px 6px rgba(0,0,0,0.06);padding:12px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
        <h3 class="font-bold text-lg" style="margin:0;font-weight:700;">Vendors with Most Maintenance or Costs</h3>
        <div style="display:flex;align-items:center;gap:8px;">
            <button aria-label="more" title="more" class="text-gray-500 hover:text-gray-700" style="background:transparent;border:0;padding:6px;color:#6b7280;">· · ·</button>
        </div>
    </div>

    <div class="chart-container" style="position:relative;width:100%;height:300px;">
        <svg class="vendors-chart-svg" width="100%" height="100%" preserveAspectRatio="none"></svg>
        <div class="vendors-chart-tooltip" style="position:absolute;display:none;pointer-events:none;background:rgba(17,17,17,0.95);color:#fff;padding:8px;border-radius:6px;font-size:12px;z-index:50;max-width:260px;"></div>
    </div>

    @if($items && count($items) > 0)
        <div class="vendors-summary" style="margin-top:10px;font-size:13px;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="text-align:left;color:#6b7280;font-size:12px;">
                        <th style="padding:6px 4px;">Vendor</th>
                        <th style="padding:6px 4px;text-align:right;">Jobs</th>
                        <th style="padding:6px 4px;text-align:right;">Total Cost</th>
                        <th style="padding:6px 4px;text-align:right;">Avg Duration</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $it)
                        <tr style="border-top:1px solid #f3f4f6;">
                            <td style="padding:8px 4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:180px;">{{ $it['name'] }}</td>
                            <td style="padding:8px 4px;text-align:right;">{{ $it['jobs'] }}</td>
                            <td style="padding:8px 4px;text-align:right;">{{ $it['total_cost_formatted'] ?? (isset($it['total_cost']) ? '\App\Helpers\Helper::formatCurrencyOutput($it["total_cost"])' : '-') }}</td>
                            <td style="padding:8px 4px;text-align:right;">{{ $it['avg_duration'] !== null ? $it['avg_duration'].' d' : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <script>
        (function(){
            const root = document.getElementById('{{ $chartId }}-root');
            if (!root) return;
            const svg = root.querySelector('svg.vendors-chart-svg');
            const tooltip = root.querySelector('.vendors-chart-tooltip');
            const items = @json(array_values($items->toArray()));

            function render(){
                const rect = root.getBoundingClientRect();
                const width = Math.max(200, rect.width);
                const height = Math.max(120, rect.height - 90);
                while (svg.firstChild) svg.removeChild(svg.firstChild);
                const ns = 'http://www.w3.org/2000/svg';
                svg.setAttribute('viewBox', `0 0 ${width} ${height}`);

                if (!items || items.length === 0) return;

                const padding = { top: 8, right: 8, bottom: 24, left: 8 };
                const chartW = width - padding.left - padding.right;
                const chartH = height - padding.top - padding.bottom;
                const gap = Math.min(16, Math.floor(chartW / (items.length * 6)) + 8);
                const barW = Math.max(8, Math.floor((chartW - gap * (items.length - 1)) / items.length));

                const maxVal = Math.max(...items.map(i => Math.max(i.total_cost || 0, i.jobs || 0)));
                const colorThreshold = maxVal * 0.6;

                items.forEach((it, idx) => {
                    const v = it.total_cost || it.jobs || 0;
                    const h = maxVal ? Math.max(2, Math.round((v / maxVal) * chartH)) : 0;
                    const x = padding.left + idx * (barW + gap);
                    const y = padding.top + (chartH - h);

                    const rectEl = document.createElementNS(ns, 'rect');
                    rectEl.setAttribute('x', x);
                    rectEl.setAttribute('y', y);
                    rectEl.setAttribute('width', barW);
                    rectEl.setAttribute('height', h);
                    const color = (it.total_cost >= colorThreshold) ? '#ef4444' : '#3b82f6';
                    rectEl.setAttribute('fill', color);
                    rectEl.style.cursor = 'pointer';

                    rectEl.addEventListener('mouseenter', function(ev){
                        const html = `<strong style="display:block;margin-bottom:4px;">${escapeHtml(it.name)}</strong>` +
                            `<div>Jobs: <strong>${it.jobs}</strong></div>` +
                            `<div>Total: <strong>${escapeHtml(it.total_cost_formatted || it.total_cost)}</strong></div>` +
                            (it.avg_duration ? `<div>Avg Duration: <strong>${it.avg_duration} d</strong></div>` : '');
                        tooltip.innerHTML = html;
                        tooltip.style.display = 'block';
                        positionTooltip(ev.clientX, ev.clientY);
                    });
                    rectEl.addEventListener('mouseleave', function(){ tooltip.style.display = 'none'; });
                    rectEl.addEventListener('mousemove', function(ev){ positionTooltip(ev.clientX, ev.clientY); });

                    svg.appendChild(rectEl);

                    // x-axis labels
                    const label = document.createElementNS(ns, 'text');
                    label.setAttribute('x', x + barW / 2);
                    label.setAttribute('y', padding.top + chartH + 14);
                    label.setAttribute('font-size', '11');
                    label.setAttribute('fill', '#374151');
                    label.setAttribute('text-anchor', 'middle');
                    label.textContent = it.name.length > 12 ? it.name.substring(0,12) + '…' : it.name;
                    svg.appendChild(label);
                });
            }

            function positionTooltip(clientX, clientY){
                const rootRect = root.getBoundingClientRect();
                const ttRect = tooltip.getBoundingClientRect();
                let left = clientX - rootRect.left + 8;
                let top = clientY - rootRect.top - ttRect.height - 12;
                if (left + ttRect.width > rootRect.width) left = rootRect.width - ttRect.width - 8;
                if (top < 0) top = clientY - rootRect.top + 12;
                tooltip.style.left = left + 'px';
                tooltip.style.top = top + 'px';
            }

            function escapeHtml(s){ if(!s && s!==0) return ''; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

            // responsive
            if (window.ResizeObserver) {
                new ResizeObserver(render).observe(root);
            } else {
                window.addEventListener('resize', render);
            }

            // initial render
            setTimeout(render, 40);
        })();
    </script>

</div>
