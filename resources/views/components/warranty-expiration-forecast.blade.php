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
    $id = 'warranty_' . substr(md5($title), 0, 8);
    $refreshUrl = $refreshUrl ?? null;
@endphp

<div id="{{ $id }}" class="kpi-card"
    style="background: {{ $bg }}; border-radius:8px; padding:16px; box-shadow:0 1px 2px rgba(16,24,40,0.04); font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial; height:220px; display:flex; flex-direction:column;">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px">
        <div style="font-size:14px; color:{{ $textColor }}; font-weight:700">{{ $title }}</div>
        <div style="display:flex; align-items:center; gap:8px">
            <div style="display:flex; align-items:center; gap:8px; position:relative">
                <button id="{{ $id }}_filter_btn" type="button" aria-expanded="false" aria-haspopup="dialog"
                    aria-label="Filter by month" title="Filter by month"
                    style="display:inline-flex; align-items:center; gap:8px; background:transparent; border:none; padding:6px; border-radius:6px; cursor:pointer; color:{{ $textColor }};">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M3 7h18" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        <path d="M7 11h10" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        <path d="M7 15h10" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    </svg>
                    <span id="{{ $id }}_filter_label"
                        style="font-size:13px; display:none; color:{{ $textColor }}; white-space:nowrap;">Filter</span>
                </button>

                <div id="{{ $id }}_filter_dropdown" role="dialog" aria-modal="false"
                    style="position:absolute; right:0; top:42px; z-index:50; display:none; background:{{ $bg }}; border-radius:8px; padding:12px; box-shadow:0 6px 18px rgba(0,0,0,0.12); width:320px;">
                    <div style="font-size:14px; font-weight:700; margin-bottom:8px; color:{{ $textColor }}">Select
                        month range</div>
                    <div style="display:flex; gap:8px; margin-bottom:8px;">
                        <input id="{{ $id }}_start" type="month"
                            style="flex:1; padding:8px; border-radius:6px; border:1px solid #e6e9ef; background:transparent; color:{{ $textColor }};">
                        <input id="{{ $id }}_end" type="month"
                            style="flex:1; padding:8px; border-radius:6px; border:1px solid #e6e9ef; background:transparent; color:{{ $textColor }};">
                    </div>
                    <div style="display:flex; justify-content:space-between; gap:8px; margin-top:6px;">
                        <button id="{{ $id }}_clear" type="button"
                            style="background:transparent; border:1px solid #e6e9ef; padding:8px 12px; border-radius:6px; cursor:pointer; color:{{ $textColor }}">Clear</button>
                        <div style="display:flex; gap:8px;">
                            <button id="{{ $id }}_cancel" type="button"
                                style="background:transparent; border:1px solid #e6e9ef; padding:8px 12px; border-radius:6px; cursor:pointer; color:{{ $textColor }}">Cancel</button>
                            <button id="{{ $id }}_apply" type="button"
                                style="background:{{ $accent }}; color:#fff; border:none; padding:8px 12px; border-radius:6px; cursor:pointer;">Apply</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div style="flex:1; display:flex; align-items:center; position:relative;">
        <svg class="warranty-forecast-svg" width="100%" height="100%" viewBox="0 0 300 120"
            preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" role="img"
            aria-label="warranty forecast chart" style="display:block;">
            <defs>
                <linearGradient id="grad-{{ $id }}" x1="0" x2="0" y1="0"
                    y2="1">
                    <stop offset="0%" stop-color="{{ $accent }}" stop-opacity="0.14" />
                    <stop offset="100%" stop-color="{{ $accent }}" stop-opacity="0" />
                </linearGradient>
            </defs>
            <polyline class="warranty-line" points="" fill="none" stroke="{{ $accent }}"
                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
            <path class="warranty-fill" d="" fill="url(#grad-{{ $id }})" opacity="0.9"></path>
            <g class="warranty-points"></g>
        </svg>
        <div class="warranty-tooltip"
            style="position:absolute;pointer-events:none;display:none;padding:6px 8px;background:#111;color:#fff;border-radius:4px;font-size:12px;z-index:10;box-shadow:0 4px 12px rgba(0,0,0,0.12);">
        </div>
    </div>

    <div style="margin-top:8px; display:flex; justify-content:space-between; align-items:center;">
        <div style="font-size:12px; color:{{ $textColor }}">
            {{-- Forecast trend (relative) --}}
        </div>
        <div style="font-size:12px; color:#6b7280">&nbsp;</div>
    </div>

    <script>
        (function() {
            var root = document.getElementById('{{ $id }}');
            if (!root) return;
            var refreshUrl = root.dataset.refreshUrl || null;
            var origSeries = @json($series) || [];
            var origLabels = @json($labels) || [];
            var displaySeries = origSeries.slice();
            var displayLabels = origLabels.slice();
            var svg = root.querySelector('svg.warranty-forecast-svg');
            var line = root.querySelector('.warranty-line');
            var fill = root.querySelector('.warranty-fill');
            var pointsGroup = root.querySelector('g.warranty-points');
            var tooltip = root.querySelector('.warranty-tooltip');

            // parse label strings to month-start dates for reliable comparison
            var labelDates = (origLabels || []).map(function(l) {
                if (!l) return new Date(NaN);
                var d = new Date(l);
                if (!isNaN(d)) return new Date(d.getFullYear(), d.getMonth(), 1);
                // fallback parse 'M Y' where M is short english month
                var parts = String(l).trim().split(/\s+/);
                var mnames = {
                    Jan: 0,
                    Feb: 1,
                    Mar: 2,
                    Apr: 3,
                    May: 4,
                    Jun: 5,
                    Jul: 6,
                    Aug: 7,
                    Sep: 8,
                    Oct: 9,
                    Nov: 10,
                    Dec: 11
                };
                var mon = mnames[parts[0]];
                var yr = parseInt(parts[1], 10);
                if (typeof mon === 'number' && !isNaN(yr)) return new Date(yr, mon, 1);
                return new Date(NaN);
            });

            function monthKeyFromYYYYMM(yyyymm) {
                var p = String(yyyymm || '').split('-');
                if (p.length < 2) return NaN;
                var y = parseInt(p[0], 10);
                var m = parseInt(p[1], 10);
                if (isNaN(y) || isNaN(m)) return NaN;
                return y * 100 + m;
            }

            function monthKeyFromDate(d) {
                return d.getFullYear() * 100 + (d.getMonth() + 1);
            }

            function formatMonthLabelFromYYYYMM(v) {
                if (!v) return 'Filter';
                var p = String(v).split('-');
                if (p.length < 2) return v;
                var y = parseInt(p[0], 10);
                var m = parseInt(p[1], 10) - 1;
                var dt = new Date(y, m, 1);
                try {
                    return dt.toLocaleDateString(undefined, {
                        month: 'short',
                        year: 'numeric'
                    });
                } catch (e) {
                    return String(v);
                }
            }

            function render() {
                var series = displaySeries || [];
                var labels = displayLabels || [];
                var rect = root.getBoundingClientRect();
                var w = Math.max(120, rect.width || 300);
                var h = Math.max(60, (rect.height - 64) || 120);
                svg.setAttribute('viewBox', '0 0 ' + Math.round(w) + ' ' + Math.round(h));
                var padTop = 10,
                    padBottom = 16;
                if (!series || !Array.isArray(series) || series.length < 2) {
                    var y = h * 0.75;
                    line.setAttribute('points', '0,' + y + ' ' + w + ',' + y);
                    fill.setAttribute('d', 'M0,' + h + ' L' + w + ',' + h + ' L' + w + ',' + y + ' L0,' + y + ' Z');
                    pointsGroup.innerHTML = '';
                    return;
                }
                var min = Math.min.apply(null, series);
                var max = Math.max.apply(null, series);
                if (min === max) {
                    min = min - 1;
                    max = max + 1;
                }
                var coords = series.map(function(v, i) {
                    var x = (i / (series.length - 1)) * w;
                    var y = h - ((v - min) / (max - min)) * (h - padTop - padBottom) - padBottom;
                    return {
                        x: x,
                        y: y,
                        v: v,
                        i: i
                    };
                });
                var pts = coords.map(function(p) {
                    return p.x.toFixed(2) + ',' + p.y.toFixed(2);
                }).join(' ');
                line.setAttribute('points', pts);
                var pathD = 'M' + coords.map(function(p) {
                    return p.x + ',' + p.y;
                }).join(' L ') + ' L ' + w + ',' + h + ' L 0,' + h + ' Z';
                fill.setAttribute('d', pathD);
                pointsGroup.innerHTML = '';
                coords.forEach(function(p) {
                    var c = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                    c.setAttribute('cx', p.x);
                    c.setAttribute('cy', p.y);
                    c.setAttribute('r', 5);
                    c.setAttribute('fill', '#fff');
                    c.setAttribute('stroke', '{{ $accent }}');
                    c.setAttribute('stroke-width', 2);
                    c.setAttribute('data-i', p.i);
                    c.style.cursor = 'pointer';
                    c.addEventListener('mouseenter', function(ev) {
                        var idx = parseInt(this.getAttribute('data-i'));
                        var lbl = labels[idx] || ('Point ' + (idx + 1));
                        var val = series[idx];
                        tooltip.style.display = 'block';
                        tooltip.innerHTML = '<strong style="display:block;margin-bottom:4px;">' + lbl +
                            '</strong>' + String(val);
                        var rootRect = root.getBoundingClientRect();
                        var svgRect = svg.getBoundingClientRect();
                        var left = (svgRect.left - rootRect.left) + p.x + 8;
                        var top = (svgRect.top - rootRect.top) + p.y - 40;
                        var tRectW = tooltip.offsetWidth || 120;
                        if (left + tRectW > rootRect.width - 8) left = rootRect.width - tRectW - 8;
                        if (left < 8) left = 8;
                        if (top < 8) top = (svgRect.top - rootRect.top) + p.y + 12;
                        tooltip.style.left = left + 'px';
                        tooltip.style.top = top + 'px';
                    });
                    c.addEventListener('mouseleave', function() {
                        tooltip.style.display = 'none';
                    });
                    pointsGroup.appendChild(c);
                });
            }

            // filtering helpers
            function applyMonthFilter(startVal, endVal) {
                if (!startVal || !endVal) {
                    displaySeries = origSeries.slice();
                    displayLabels = origLabels.slice();
                    render();
                    return;
                }
                var sk = monthKeyFromYYYYMM(startVal);
                var ek = monthKeyFromYYYYMM(endVal);
                var ns = [],
                    nl = [];
                for (var i = 0; i < labelDates.length; i++) {
                    var d = labelDates[i];
                    if (isNaN(d)) continue;
                    var k = monthKeyFromDate(d);
                    if (k >= sk && k <= ek) {
                        ns.push(origSeries[i]);
                        nl.push(origLabels[i]);
                    }
                }
                displaySeries = ns;
                displayLabels = nl;
                render();
            }

            function doLivewireEmit(evt, payload) {
                if (window.Livewire && typeof window.Livewire.emit === 'function') {
                    window.Livewire.emit(evt, payload);
                    return true;
                }
                if (window.livewire && typeof window.livewire.emit === 'function') {
                    window.livewire.emit(evt, payload);
                    return true;
                }
                return false;
            }

            function doRefreshRequest(startVal, endVal) {
                if (!refreshUrl) return;
                var url = refreshUrl + '?start=' + encodeURIComponent(startVal || '') + '&end=' + encodeURIComponent(
                    endVal || '');
                fetch(url, {
                        credentials: 'same-origin',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(function(r) {
                        if (!r.ok) throw new Error('Network error');
                        return r.json();
                    })
                    .then(function(data) {
                        if (Array.isArray(data.series)) {
                            origSeries = data.series;
                            origLabels = data.labels || origLabels;
                            labelDates = (origLabels || []).map(function(l) {
                                var d = new Date(l);
                                if (!isNaN(d)) return new Date(d.getFullYear(), d.getMonth(), 1);
                                var parts = String(l).trim().split(/\s+/);
                                var mnames = {
                                    Jan: 0,
                                    Feb: 1,
                                    Mar: 2,
                                    Apr: 3,
                                    May: 4,
                                    Jun: 5,
                                    Jul: 6,
                                    Aug: 7,
                                    Sep: 8,
                                    Oct: 9,
                                    Nov: 10,
                                    Dec: 11
                                };
                                var mon = mnames[parts[0]];
                                var yr = parseInt(parts[1], 10);
                                if (typeof mon === 'number' && !isNaN(yr)) return new Date(yr, mon, 1);
                                return new Date(NaN);
                            });
                            displaySeries = origSeries.slice();
                            displayLabels = origLabels.slice();
                            render();
                        }
                    })
                    .catch(function(e) {
                        console.error('warranty refresh failed', e);
                    });
            }

            // initial render
            render();

            // wire filter UI
            var fid = '{{ $id }}';
            var btn = document.getElementById(fid + '_filter_btn');
            var flabel = document.getElementById(fid + '_filter_label');
            var dropdown = document.getElementById(fid + '_filter_dropdown');
            var start = document.getElementById(fid + '_start');
            var end = document.getElementById(fid + '_end');
            var applyBtn = document.getElementById(fid + '_apply');
            var cancel = document.getElementById(fid + '_cancel');
            var clear = document.getElementById(fid + '_clear');

            if (btn) {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (!dropdown) return;
                    var open = dropdown.style.display === 'block';
                    dropdown.style.display = open ? 'none' : 'block';
                    btn.setAttribute('aria-expanded', String(!open));
                });
            }
            if (applyBtn) {
                applyBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (!start.value || !end.value) return;
                    var s = start.value;
                    var en = end.value;
                    flabel.textContent = formatMonthLabelFromYYYYMM(s) + ' - ' + formatMonthLabelFromYYYYMM(en);
                    flabel.style.display = 'inline-block';
                    if (dropdown) dropdown.style.display = 'none';
                    if (btn) btn.setAttribute('aria-expanded', 'false');
                    var detail = {
                        id: fid,
                        start: s,
                        end: en
                    };
                    document.dispatchEvent(new CustomEvent('warrantyFilterChanged', {
                        detail: detail
                    }));
                    doLivewireEmit('warrantyFilterChanged', detail);
                    if (refreshUrl) {
                        doRefreshRequest(s, en);
                    } else {
                        applyMonthFilter(s, en);
                    }
                });
            }
            if (cancel) {
                cancel.addEventListener('click', function(e) {
                    if (dropdown) dropdown.style.display = 'none';
                    if (btn) btn.setAttribute('aria-expanded', 'false');
                });
            }
            if (clear) {
                clear.addEventListener('click', function(e) {
                    if (start) start.value = '';
                    if (end) end.value = '';
                    if (flabel) {
                        flabel.textContent = 'Filter';
                        flabel.style.display = 'none';
                    }
                    if (dropdown) dropdown.style.display = 'none';
                    if (btn) btn.setAttribute('aria-expanded', 'false');
                    var detail = {
                        id: fid,
                        start: null,
                        end: null
                    };
                    document.dispatchEvent(new CustomEvent('warrantyFilterChanged', {
                        detail: detail
                    }));
                    doLivewireEmit('warrantyFilterChanged', detail);
                    if (refreshUrl) {
                        doRefreshRequest('', '');
                    } else {
                        applyMonthFilter('', '');
                    }
                });
            }

            document.addEventListener('click', function(ev) {
                if (!dropdown) return;
                if (dropdown.style.display === 'block' && !dropdown.contains(ev.target) && ev.target !== btn &&
                    !btn.contains(ev.target)) {
                    dropdown.style.display = 'none';
                    if (btn) btn.setAttribute('aria-expanded', 'false');
                }
            });

            // re-render on resize
            if (window.ResizeObserver) {
                var ro = new ResizeObserver(function() {
                    render();
                });
                ro.observe(svg);
            } else {
                window.addEventListener('resize', render);
            }
        })();
    </script>

</div>
