{{--
    Assets With Most Failures
    - Pass `items` as array of: label, model, count, last
    - Card uses a fixed height and scrolls contents if the list is long.
--}}
@php
    $title = $title ?? 'Assets with Most Failures';
    $bg = $bg ?? '#ffffff';
    $textColor = $textColor ?? '#1f2937';
    $accent = $accent ?? '#0ea5a4';
    $items = $items ?? []; // array of ['label'=>string,'model'=>string,'count'=>int,'last'=>string]
    $id = 'failures_' . substr(md5($title), 0, 8);
    $refreshUrl = $refreshUrl ?? null;
@endphp

<div id="{{ $id }}" class="kpi-card"
    @if ($refreshUrl) data-refresh-url="{{ $refreshUrl }}" @endif
    style="background: {{ $bg }}; border-radius:8px; padding:16px; box-shadow:0 1px 2px rgba(16,24,40,0.04); font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial; height:220px; display:flex; flex-direction:column;">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px">
        <div style="font-size:14px; color:{{ $textColor }}; font-weight:700">{{ $title }}</div>
        <div style="display:flex; align-items:center; gap:8px; position:relative">
            <button id="{{ $id }}_filter_btn" type="button" aria-expanded="false" aria-haspopup="dialog"
                aria-label="Filter by date" title="Filter by date"
                style="display:inline-flex; align-items:center; gap:8px; background:transparent; border:none; padding:6px; border-radius:6px; cursor:pointer; color:{{ $textColor }};">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true">
                    <path d="M3 5h18" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    <path d="M6 12h12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    <path d="M10 19h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                </svg>
                <span id="{{ $id }}_filter_label"
                    style="font-size:13px; display:none; color:{{ $textColor }}; white-space:nowrap;">Filter</span>
            </button>

            <div id="{{ $id }}_filter_dropdown" role="dialog" aria-modal="false"
                style="position:absolute; right:0; top:42px; z-index:50; display:none; background:{{ $bg }}; border-radius:8px; padding:12px; box-shadow:0 6px 18px rgba(0,0,0,0.12); width:320px;">
                <div style="font-size:14px; font-weight:700; margin-bottom:8px; color:{{ $textColor }}">Select date
                    range</div>
                <div style="display:flex; gap:8px; margin-bottom:8px;">
                    <input id="{{ $id }}_start" type="date"
                        style="flex:1; padding:8px; border-radius:6px; border:1px solid #e6e9ef; background:transparent; color:{{ $textColor }};">
                    <input id="{{ $id }}_end" type="date"
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

    @if (empty($items))
        <div style="padding:18px; color:#6b7280; font-size:13px">No failure or maintenance data available to display.
        </div>
    @else
        @php
            $max = max(array_column($items, 'count'));
            if ($max <= 0) {
                $max = 1;
            }
        @endphp
        <div id="{{ $id }}_list"
            style="flex:1; overflow:auto; display:flex; flex-direction:column; gap:10px; padding-right:6px;">
            @foreach ($items as $it)
                @php
                    $failureStart = '';
                    if (!empty($it['last']) && strtotime($it['last']) !== false) {
                        $failureStart = date('Y-m-d', strtotime($it['last']));
                    }
                    $barPct = round(($it['count'] / $max) * 100);
                @endphp
                <div data-failure-start="{{ $failureStart }}" style="display:flex; align-items:center; gap:12px;">
                    <div style="flex:1">
                        <div style="font-size:13px; font-weight:600; color:{{ $textColor }}">{{ $it['label'] }}
                        </div>
                        <div style="font-size:11px; color:{{ $textColor }}">{{ $it['model'] }} · Last:
                            {{ $it['last'] ?? '-' }}</div>
                    </div>
                    <div style="width:160px; display:flex; align-items:center; gap:8px">
                        <div style="flex:1; background:#e6e9ef; height:12px; border-radius:6px; overflow:hidden">
                            <div
                                style="width:{{ $barPct }}%; height:12px; background:{{ $accent }}; border-radius:6px">
                            </div>
                        </div>
                        <div style="width:36px; text-align:right; font-size:12px; color:{{ $textColor }}">
                            {{ $it['count'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>

<script>
    (function() {
        var id = "{{ $id }}";
        var container = document.getElementById(id);
        var refreshUrl = container ? container.dataset.refreshUrl : null;
        var btn = document.getElementById(id + "_filter_btn");
        var label = document.getElementById(id + "_filter_label");
        var dropdown = document.getElementById(id + "_filter_dropdown");
        var start = document.getElementById(id + "_start");
        var end = document.getElementById(id + "_end");
        var apply = document.getElementById(id + "_apply");
        var cancel = document.getElementById(id + "_cancel");
        var clear = document.getElementById(id + "_clear");
        var listEl = document.getElementById(id + "_list");

        function formatDisplay(s, e) {
            if (!s || !e) return "Filter";
            var sd = new Date(s);
            var ed = new Date(e);
            var opts = {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            };
            try {
                return sd.toLocaleDateString(undefined, opts) + " - " + ed.toLocaleDateString(undefined, opts);
            } catch (err) {
                return s + " - " + e;
            }
        }

        function doLivewireEmit(eventName, payload) {
            if (window.Livewire && typeof window.Livewire.emit === 'function') {
                window.Livewire.emit(eventName, payload);
                return true;
            }
            if (window.livewire && typeof window.livewire.emit === 'function') {
                window.livewire.emit(eventName, payload);
                return true;
            }
            return false;
        }

        function doRefreshRequest(startVal, endVal) {
            if (!refreshUrl || !listEl) return;
            var url = refreshUrl + '?start=' + encodeURIComponent(startVal || '') + '&end=' + encodeURIComponent(
                endVal || '');
            fetch(url, {
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    }
                })
                .then(function(resp) {
                    if (!resp.ok) throw new Error('Network error');
                    return resp.text();
                })
                .then(function(html) {
                    listEl.innerHTML = html;
                })
                .catch(function(err) {
                    console.error('Failed to refresh failures list', err);
                });
        }

        function applyLocalFilter(startVal, endVal) {
            if (!listEl) return;
            var rows = listEl.querySelectorAll('[data-failure-start]');
            var prevScroll = listEl.scrollTop;
            // If no start/end provided, show all (use flex to preserve layout)
            if (!startVal || !endVal) {
                rows.forEach(function(r) {
                    r.style.display = 'flex';
                });
                // restore scroll
                listEl.scrollTop = prevScroll;
                return;
            }
            // Compare as YYYY-MM-DD strings (lexicographic safe)
            rows.forEach(function(r) {
                var v = r.dataset.failureStart || '';
                if (!v) {
                    r.style.display = 'none';
                    return;
                }
                if (v >= startVal && v <= endVal) {
                    r.style.display = 'flex';
                } else {
                    r.style.display = 'none';
                }
            });
            // restore scroll
            listEl.scrollTop = prevScroll;
        }

        if (btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                if (!dropdown) return;
                var open = dropdown.style.display === 'block';
                dropdown.style.display = open ? 'none' : 'block';
                btn.setAttribute('aria-expanded', String(!open));
            });
        }

        if (apply) {
            apply.addEventListener('click', function(e) {
                e.preventDefault();
                if (!start.value || !end.value) {
                    return;
                }
                var s = start.value;
                var en = end.value;
                label.textContent = formatDisplay(s, en);
                label.style.display = 'inline-block';
                if (dropdown) dropdown.style.display = 'none';
                if (btn) btn.setAttribute('aria-expanded', 'false');
                var detail = {
                    id: id,
                    start: s,
                    end: en
                };
                var event = new CustomEvent('assetsFailuresFilterChanged', {
                    detail: detail
                });
                document.dispatchEvent(event);
                // emit for Livewire listeners too (if present)
                doLivewireEmit('assetsFailuresFilterChanged', detail);
                // If a refresh URL is provided, request server-side partial, otherwise filter locally
                if (refreshUrl) {
                    doRefreshRequest(s, en);
                } else {
                    applyLocalFilter(s, en);
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
                if (label) {
                    label.textContent = 'Filter';
                    label.style.display = 'none';
                }
                if (dropdown) dropdown.style.display = 'none';
                if (btn) btn.setAttribute('aria-expanded', 'false');
                var detail = {
                    id: id,
                    start: null,
                    end: null
                };
                var event = new CustomEvent('assetsFailuresFilterChanged', {
                    detail: detail
                });
                document.dispatchEvent(event);
                doLivewireEmit('assetsFailuresFilterChanged', detail);
                if (refreshUrl) {
                    doRefreshRequest('', '');
                } else {
                    applyLocalFilter('', '');
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

    })();
</script>
