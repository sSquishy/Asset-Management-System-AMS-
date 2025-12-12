{{--
    Asset Depreciation Overview (3-in-1 carousel)
    - Pass `metrics` (array of objects with `value`, `subtitle`, optional `trend`)
    - This component has a fixed height so dashboard columns align visually.
    - Main content area is scrollable if needed.
--}}
@php
use Illuminate\Support\Str;
$title = $title ?? 'Asset Depreciation Overview';
$metrics = $metrics ?? []; // array of ['value' => string, 'subtitle' => string, 'trend' => [nums]]
$bg = $bg ?? '#ffffff';
$textColor = $textColor ?? '#1f2937';
$accent = $accent ?? '#2563eb';
$id = 'kpi_'.substr(md5($title),0,8);
@endphp

<div id="{{ $id }}" class="kpi-card" style="background: {{ $bg }}; border-radius:8px; padding:18px; box-shadow:0 1px 2px rgba(16,24,40,0.04); font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial; height:220px; display:flex; flex-direction:column;">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px">
        <div style="font-size:14px; color:{{ $textColor }}; font-weight:600">{{ $title }}</div>
        <div style="display:flex; gap:8px; align-items:center">
            <button type="button" class="kpi-prev" aria-label="previous" style="background:transparent;border:none;padding:6px;border-radius:6px;cursor:pointer;color:{{ $textColor }}">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            <button type="button" class="kpi-next" aria-label="next" style="background:transparent;border:none;padding:6px;border-radius:6px;cursor:pointer;color:{{ $textColor }}">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
        </div>
    </div>

    <div class="kpi-body" style="flex:1; overflow:auto;">
        <div style="display:flex; align-items:baseline; gap:12px;">
        <div style="flex:1">
            <div class="kpi-value" style="font-size:28px; font-weight:700; color:{{ $textColor }}">&ndash;</div>
            <div class="kpi-sub" style="font-size:12px; color:{{ $textColor }}; margin-top:4px">&nbsp;</div>
        </div>
        <div style="width:88px; text-align:right; color:#6b7280; font-size:12px">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="3" fill="#ffffff" stroke="#e6e9ef"/><path d="M7 14h10M7 10h6" stroke="#9aa4b2" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        </div>

        <div style="margin-top:12px; display:flex; align-items:center; gap:12px">
        <svg class="kpi-spark-svg" width="200" height="40" viewBox="0 0 200 40" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="trend sparkline" style="display:none">
            <polyline class="kpi-spark" points="" fill="none" stroke="{{ $accent }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" opacity="0.95" />
        </svg>
        <div class="kpi-trend-label" style="font-size:12px;color:{{ $textColor }}">&nbsp;</div>
        </div>
    </div>

    {{-- Inline script for carousel rendering (uses metrics passed from server) --}}
    <script>
    (function(){
        var root = document.getElementById('{{ $id }}');
        if(!root) return;
        var metrics = @json($metrics);
        if(!metrics || !metrics.length) return;
        var idx = 0;
        var valueEl = root.querySelector('.kpi-value');
        var subEl = root.querySelector('.kpi-sub');
        var svg = root.querySelector('svg.kpi-spark-svg');
        var poly = root.querySelector('polyline.kpi-spark');
        var trendLabel = root.querySelector('.kpi-trend-label');
        function render(){
            var m = metrics[idx];
            valueEl.textContent = m.value || '';
            subEl.textContent = m.subtitle || '';
            if(m.trend && Array.isArray(m.trend) && m.trend.length>1){
                var vals = m.trend.map(Number);
                var min = Math.min.apply(null, vals);
                var max = Math.max.apply(null, vals);
                if(min === max){ min = min - 1; max = max + 1; }
                var w = 200, h = 40;
                var pts = vals.map(function(v,i){
                    var x = (i/(vals.length-1))*w;
                    var y = h - ((v-min)/(max-min))*h;
                    return x.toFixed(2)+','+y.toFixed(2);
                }).join(' ');
                poly.setAttribute('points', pts);
                svg.style.display = 'inline-block';
                trendLabel.textContent = m.trendLabel || '6-month trend';
            } else {
                svg.style.display = 'none';
                trendLabel.textContent = '';
            }
        }
        root.querySelector('.kpi-prev').addEventListener('click', function(){ idx = (idx - 1 + metrics.length) % metrics.length; render(); });
        root.querySelector('.kpi-next').addEventListener('click', function(){ idx = (idx + 1) % metrics.length; render(); });
        render();
    })();
    </script>

</div>
