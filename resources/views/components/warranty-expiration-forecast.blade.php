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
                    aria-label="Filter by asset" title="Filter by asset"
                    style="display:inline-flex; align-items:center; gap:8px; background:transparent; border:none; padding:6px; border-radius:6px; cursor:pointer; color:{{ $textColor }};">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M3 5h18" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        <path d="M6 12h12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        <path d="M10 19h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    </svg>
                    <span id="{{ $id }}_filter_label"
                        style="font-size:13px; display:none; color:{{ $textColor }}; white-space:nowrap;">Filter</span>
                </button>

                <div id="{{ $id }}_filter_dropdown" role="dialog" aria-modal="false"
                    style="position:absolute; right:0; top:42px; z-index:50; display:none; background:{{ $bg }}; border-radius:8px; padding:12px; box-shadow:0 6px 18px rgba(0,0,0,0.12); width:320px;">
                    <div style="font-size:14px; font-weight:700; margin-bottom:8px; color:{{ $textColor }}">Select
                        asset</div>
                    <div style="display:flex; flex-direction:column; gap:8px; margin-bottom:8px;">
                        <input id="{{ $id }}_asset_search" type="search" placeholder="Search assets"
                            style="padding:8px; border-radius:6px; border:1px solid #e6e9ef; background:transparent; color:{{ $textColor }};">
                        <div id="{{ $id }}_asset_list"
                            style="max-height:220px; overflow:auto; border-radius:6px; padding:6px; border:1px solid #e6e9ef; background:transparent;">
                            <label style="display:flex; align-items:center; gap:8px; padding:6px;">
                                <input id="{{ $id }}_toggle_all" type="checkbox" style="width:16px;height:16px">
                                <span style="font-size:13px; color:{{ $textColor }}">Select All</span>
                            </label>
                            @php $assetsList = $assets ?? []; @endphp
                            @foreach ($assetsList as $asset)
                                <label data-asset-name="{{ $asset['label'] }}"
                                    style="display:flex; align-items:center; gap:8px; padding:6px;">
                                    <input type="checkbox" class="filter-asset-checkbox" data-asset-id="{{ $asset['id'] }}"
                                        value="{{ $asset['id'] }}" style="width:16px;height:16px">
                                    <span
                                        style="font-size:13px; color:{{ $textColor }}; overflow:hidden; white-space:nowrap; text-overflow:ellipsis; flex:1">{{ $asset['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
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
            var assetsData = @json($assets ?? []) || [];
            var displaySeries = origSeries.slice();
            var displayLabels = origLabels.slice();
            var svg = root.querySelector('svg.warranty-forecast-svg');
            var line = root.querySelector('.warranty-line');
            var fill = root.querySelector('.warranty-fill');
            var pointsGroup = root.querySelector('g.warranty-points');
            var tooltip = root.querySelector('.warranty-tooltip');

            // the chart itself is static for warranty forecast; filtering is only exposed via asset selection UI

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

            // initial render
            render();

            // wire filter UI
            var fid = '{{ $id }}';
            var btn = document.getElementById(fid + '_filter_btn');
            var flabel = document.getElementById(fid + '_filter_label');
            var dropdown = document.getElementById(fid + '_filter_dropdown');
            var assetSearch = document.getElementById(fid + '_asset_search');
            var assetList = document.getElementById(fid + '_asset_list');
            var toggleAll = document.getElementById(fid + '_toggle_all');
            var applyBtn = document.getElementById(fid + '_apply');
            var cancel = document.getElementById(fid + '_cancel');
            var clear = document.getElementById(fid + '_clear');

            function getAssetCheckboxes() {
                if (!assetList) return [];
                return Array.prototype.slice.call(assetList.querySelectorAll('input.filter-asset-checkbox'));
            }

            function updateToggleAllState() {
                if (!toggleAll || !assetList) return;
                var boxes = getAssetCheckboxes();
                var visible = boxes.filter(function(b) {
                    var wrapper = b.closest('[data-asset-name]');
                    return !wrapper || wrapper.style.display !== 'none';
                });
                if (visible.length === 0) {
                    toggleAll.checked = false;
                    toggleAll.indeterminate = false;
                    return;
                }
                var allChecked = visible.every(function(b) {
                    return b.checked;
                });
                var someChecked = visible.some(function(b) {
                    return b.checked;
                });
                toggleAll.checked = allChecked;
                toggleAll.indeterminate = !allChecked && someChecked;
            }

            function bindAssetCheckboxListeners() {
                var boxes = getAssetCheckboxes();
                boxes.forEach(function(b) {
                    b.addEventListener('change', function() {
                        updateToggleAllState();
                    });
                });
            }

            bindAssetCheckboxListeners();
            updateToggleAllState();

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
                    var boxes = getAssetCheckboxes();
                    var selected = boxes.filter(function(b) {
                        return b.checked;
                    }).map(function(b) {
                        return b.value;
                    });
                    if (selected.length === 0) return;
                    var first = boxes.find(function(b) { return b.checked; });
                    var firstLabel = first ? first.closest('[data-asset-name]').dataset.assetName : 'Asset';
                    flabel.textContent = selected.length === 1 ? firstLabel : firstLabel + ' +' + (selected.length - 1) + ' More';
                    flabel.style.display = 'inline-block';
                    if (dropdown) dropdown.style.display = 'none';
                    if (btn) btn.setAttribute('aria-expanded', 'false');
                    var detail = {
                        id: fid,
                        asset_ids: selected
                    };
                    document.dispatchEvent(new CustomEvent('warrantyFilterChanged', {
                        detail: detail
                    }));
                    doLivewireEmit('warrantyFilterChanged', detail);
                    updateToggleAllState();
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
                    var boxes = getAssetCheckboxes();
                    boxes.forEach(function(b) { b.checked = false; });
                    if (flabel) {
                        flabel.textContent = 'Filter';
                        flabel.style.display = 'none';
                    }
                    if (dropdown) dropdown.style.display = 'none';
                    if (btn) btn.setAttribute('aria-expanded', 'false');
                    var detail = {
                        id: fid,
                        asset_ids: []
                    };
                    document.dispatchEvent(new CustomEvent('warrantyFilterChanged', {
                        detail: detail
                    }));
                    doLivewireEmit('warrantyFilterChanged', detail);
                    updateToggleAllState();
                });
            }

            if (assetSearch && assetList) {
                assetSearch.addEventListener('input', function() {
                    var q = String(this.value || '').toLowerCase().trim();
                    var items = assetList.querySelectorAll('[data-asset-name]');
                    items.forEach(function(it) {
                        var name = String(it.dataset.assetName || '').toLowerCase();
                        it.style.display = (!q || name.indexOf(q) !== -1) ? '' : 'none';
                    });
                    updateToggleAllState();
                });
            }

            if (toggleAll) {
                toggleAll.addEventListener('change', function() {
                    var boxes = getAssetCheckboxes();
                    var checked = !!this.checked;
                    boxes.forEach(function(b) {
                        var wrapper = b.closest('[data-asset-name]');
                        if (wrapper && wrapper.style.display === 'none') return;
                        b.checked = checked;
                    });
                    updateToggleAllState();
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
