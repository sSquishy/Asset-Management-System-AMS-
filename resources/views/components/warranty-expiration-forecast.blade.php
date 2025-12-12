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

    <div style="flex:1; display:flex; align-items:center;">
        <svg class="warranty-forecast-svg" width="100%" height="100%" viewBox="0 0 300 120" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="warranty forecast chart">
            <defs>
                <linearGradient id="grad-{{ $id }}" x1="0" x2="0" y1="0" y2="1">
                    <stop offset="0%" stop-color="{{ $accent }}" stop-opacity="0.14" />
                    <stop offset="100%" stop-color="{{ $accent }}" stop-opacity="0" />
                </linearGradient>
            </defs>
            <polyline class="warranty-line" points="" fill="none" stroke="{{ $accent }}" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
            <path class="warranty-fill" d="" fill="url(#grad-{{ $id }})" opacity="0.9"></path>
        </svg>
    </div>

    <div style="margin-top:8px; display:flex; justify-content:space-between; align-items:center;">
        <div style="font-size:12px; color:{{ $textColor }}">Forecast trend (relative)</div>
        <div style="font-size:12px; color:#6b7280">&nbsp;</div>
    </div>

    <script>
    (function(){
        var root = document.getElementById('{{ $id }}');
        if(!root) return;
        var series = @json($series);
        var svg = root.querySelector('svg.warranty-forecast-svg');
        var line = root.querySelector('.warranty-line');
        var fill = root.querySelector('.warranty-fill');
        if(!series || !Array.isArray(series) || series.length < 2){
            // show a subtle flat line
            line.setAttribute('points', '0,90 300,90');
            fill.setAttribute('d', 'M0,120 L300,120 L300,90 L0,90 Z');
            return;
        }
        var w = 300, h = 120;
        var min = Math.min.apply(null, series);
        var max = Math.max.apply(null, series);
        if(min === max){ min = min - 1; max = max + 1; }
        var pts = series.map(function(v,i){
            var x = (i/(series.length-1))*w;
            var y = h - ((v - min)/(max - min))* (h - 20) - 10; // padding
            return x.toFixed(2)+','+y.toFixed(2);
        }).join(' ');
        line.setAttribute('points', pts);
        // build closed path for fill
        var pathD = 'M' + pts.split(' ').join(' L ') + ' L ' + w + ',' + h + ' L 0,' + h + ' Z';
        fill.setAttribute('d', pathD);
    })();
    </script>

</div>
