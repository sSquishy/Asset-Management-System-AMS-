{{--
    Warranty Expiration Forecast
    - Pass `series` (numeric array) and optional `labels`
    - Card uses fixed height so it lines up with sibling dashboard cards.
--}}
@php
$title = $title ?? 'Warranty Expiration Forecast';
$bg = $bg ?? '#ffffff';
$textColor = $textColor ?? '#1f2937';
$accent = $accent ?? '#ef4444';
$series = $series ?? []; // array of numeric forecast values
$labels = $labels ?? []; // optional labels for months
$id = 'warranty_'.substr(md5($title),0,8);
@endphp

<div id="{{ $id }}" class="kpi-card" style="background: {{ $bg }}; border-radius:8px; padding:16px; box-shadow:0 1px 2px rgba(16,24,40,0.04); font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial; height:220px; display:flex; flex-direction:column;">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px">
        <div style="font-size:14px; color:{{ $textColor }}; font-weight:700">{{ $title }}</div>
        <div style="display:flex; align-items:center; gap:8px">
            <button aria-label="menu" title="Menu" style="background:transparent;border:none;padding:6px;border-radius:6px;cursor:pointer;color:{{ $textColor }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><circle cx="5" cy="12" r="1.6" fill="currentColor"/><circle cx="12" cy="12" r="1.6" fill="currentColor"/><circle cx="19" cy="12" r="1.6" fill="currentColor"/></svg>
            </button>
        </div>
    </div>

    <div style="flex:1; display:flex; align-items:center; position:relative;">
        <svg class="warranty-forecast-svg" width="100%" height="100%" viewBox="0 0 300 120" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="warranty forecast chart" style="display:block;">
            <defs>
                <linearGradient id="grad-{{ $id }}" x1="0" x2="0" y1="0" y2="1">
                    <stop offset="0%" stop-color="{{ $accent }}" stop-opacity="0.14" />
                    <stop offset="100%" stop-color="{{ $accent }}" stop-opacity="0" />
                </linearGradient>
            </defs>
            <polyline class="warranty-line" points="" fill="none" stroke="{{ $accent }}" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
            <path class="warranty-fill" d="" fill="url(#grad-{{ $id }})" opacity="0.9"></path>
            <g class="warranty-points"></g>
        </svg>
        <div class="warranty-tooltip" style="position:absolute;pointer-events:none;display:none;padding:6px 8px;background:#111;color:#fff;border-radius:4px;font-size:12px;z-index:10;box-shadow:0 4px 12px rgba(0,0,0,0.12);"></div>
    </div>

    <div style="margin-top:8px; display:flex; justify-content:space-between; align-items:center;">
        <div style="font-size:12px; color:{{ $textColor }}">
            {{-- Forecast trend (relative) --}}
        </div>
        <div style="font-size:12px; color:#6b7280">&nbsp;</div>
    </div>

    <script>
    (function(){
        var root = document.getElementById('{{ $id }}');
        if(!root) return;
        var series = @json($series) || [];
        var labels = @json($labels) || [];
        var svg = root.querySelector('svg.warranty-forecast-svg');
        var line = root.querySelector('.warranty-line');
        var fill = root.querySelector('.warranty-fill');
        var pointsGroup = root.querySelector('g.warranty-points');
        var tooltip = root.querySelector('.warranty-tooltip');

        function render(){
            // measure the card container to keep chart constrained to the card
            var rect = root.getBoundingClientRect();
            var w = Math.max(120, rect.width || 300);
            var h = Math.max(60, (rect.height - 64) || 120); // reserve space for title/footer

            // update svg viewBox to map coordinates to current size
            svg.setAttribute('viewBox', '0 0 '+Math.round(w)+' '+Math.round(h));

            // padding inside svg
            var padTop = 10, padBottom = 16;

            if(!series || !Array.isArray(series) || series.length < 2){
                // subtle flat line
                var y = h * 0.75;
                line.setAttribute('points', '0,'+y+' '+w+','+y);
                fill.setAttribute('d', 'M0,'+h+' L'+w+','+h+' L'+w+','+y+' L0,'+y+' Z');
                pointsGroup.innerHTML = '';
                return;
            }

            var min = Math.min.apply(null, series);
            var max = Math.max.apply(null, series);
            if(min === max){ min = min - 1; max = max + 1; }

            // compute points
            var coords = series.map(function(v,i){
                var x = (i/(series.length-1))*w;
                var y = h - ((v - min)/(max - min))*(h - padTop - padBottom) - padBottom;
                return {x:x, y:y, v:v, i:i};
            });

            var pts = coords.map(function(p){ return p.x.toFixed(2)+','+p.y.toFixed(2); }).join(' ');
            line.setAttribute('points', pts);
            var pathD = 'M' + coords.map(function(p){ return p.x+','+p.y; }).join(' L ') + ' L ' + w + ',' + h + ' L 0,' + h + ' Z';
            fill.setAttribute('d', pathD);

            // render point circles
            pointsGroup.innerHTML = '';
            coords.forEach(function(p){
                var c = document.createElementNS('http://www.w3.org/2000/svg','circle');
                c.setAttribute('cx', p.x);
                c.setAttribute('cy', p.y);
                c.setAttribute('r', 5);
                c.setAttribute('fill', '#fff');
                c.setAttribute('stroke', '{{ $accent }}');
                c.setAttribute('stroke-width', 2);
                c.setAttribute('data-i', p.i);
                c.style.cursor = 'pointer';

                c.addEventListener('mouseenter', function(ev){
                    var idx = parseInt(this.getAttribute('data-i'));
                    var lbl = labels[idx] || ('Point '+(idx+1));
                    var val = series[idx];
                    tooltip.style.display = 'block';
                    tooltip.innerHTML = '<strong style="display:block;margin-bottom:4px;">'+lbl+'</strong>' + String(val);
                    // position tooltip relative to root, clamped inside the card
                    var rootRect = root.getBoundingClientRect();
                    var svgRect = svg.getBoundingClientRect();
                    // prefer positioning above the point
                    var left = (svgRect.left - rootRect.left) + p.x + 8;
                    var top = (svgRect.top - rootRect.top) + p.y - 40;
                    // ensure tooltip fits horizontally
                    var tRectW = tooltip.offsetWidth || 120;
                    if(left + tRectW > rootRect.width - 8) left = rootRect.width - tRectW - 8;
                    if(left < 8) left = 8;
                    if(top < 8) top = (svgRect.top - rootRect.top) + p.y + 12; // place below if not enough space
                    tooltip.style.left = left+'px';
                    tooltip.style.top = top+'px';
                });
                c.addEventListener('mouseleave', function(){ tooltip.style.display = 'none'; });

                pointsGroup.appendChild(c);
            });
        }

        // initial render
        render();

        // re-render on resize
        var ro = null;
        if (window.ResizeObserver) {
            ro = new ResizeObserver(function(){ render(); });
            ro.observe(svg);
        } else {
            window.addEventListener('resize', render);
        }
    })();
    </script>

</div>
