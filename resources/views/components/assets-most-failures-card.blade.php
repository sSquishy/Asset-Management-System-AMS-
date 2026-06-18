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
                aria-label="Filter by category" title="Filter by category"
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
                <div style="font-size:14px; font-weight:700; margin-bottom:8px; color:{{ $textColor }}">Select
                    category</div>
                <div style="display:flex; flex-direction:column; gap:8px; margin-bottom:8px;">
                    <input id="{{ $id }}_category_search" type="search" placeholder="Search"
                        style="padding:8px; border-radius:6px; border:1px solid #e6e9ef; background:transparent; color:{{ $textColor }};">
                    <div id="{{ $id }}_category_list"
                        style="max-height:220px; overflow:auto; border-radius:6px; padding:6px; border:1px solid #e6e9ef; background:transparent;">
                        <label style="display:flex; align-items:center; gap:8px; padding:6px;">
                            <input id="{{ $id }}_toggle_all" type="checkbox" style="width:16px;height:16px">
                            <span style="font-size:13px; color:{{ $textColor }}">Select All</span>
                        </label>
                        @php $cats = $categories ?? []; @endphp
                        @foreach ($cats as $c)
                            <label data-cat-name="{{ $c['name'] }}"
                                style="display:flex; align-items:center; gap:8px; padding:6px;">
                                <input type="checkbox" class="filter-cat-checkbox" data-cat-id="{{ $c['id'] }}"
                                    value="{{ $c['id'] }}" style="width:16px;height:16px">
                                <span
                                    style="font-size:13px; color:{{ $textColor }}; overflow:hidden; white-space:nowrap; text-overflow:ellipsis; flex:1">{{ $c['name'] }}</span>
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
                <div data-failure-start="{{ $failureStart }}" data-category-id="{{ $it['category_id'] ?? '' }}"
                    style="display:flex; align-items:center; gap:12px;">
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
        var categorySearch = document.getElementById(id + "_category_search");
        var categoryList = document.getElementById(id + "_category_list");
        var toggleAll = document.getElementById(id + "_toggle_all");
        var categoriesData = @json($categories ?? []);
        var apply = document.getElementById(id + "_apply");
        var cancel = document.getElementById(id + "_cancel");
        var clear = document.getElementById(id + "_clear");
        var listEl = document.getElementById(id + "_list");

        function getCategoryCheckboxes() {
            if (!categoryList) return [];
            return Array.prototype.slice.call(categoryList.querySelectorAll('input.filter-cat-checkbox'));
        }

        function formatCategoryDisplay(selected) {
            if (!selected) return 'Filter';
            var ids = Array.isArray(selected) ? selected : [selected];
            if (ids.length === 0) return 'Filter';
            var names = ids.map(function(id) {
                var f = (categoriesData || []).find(function(c) {
                    return String(c.id) === String(id);
                });
                return f ? f.name : String(id);
            });
            if (names.length === 1) return names[0];
            // Show only the first selected category and indicate how many more are selected
            if (names.length > 1) return names[0] + ' +' + (names.length - 1) + ' More';
            return 'Filter';
        }

        function updateToggleAllState() {
            if (!toggleAll || !categoryList) return;
            var boxes = getCategoryCheckboxes();
            var visible = boxes.filter(function(b) {
                var wrapper = b.closest('[data-cat-name]');
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

        function bindCategoryCheckboxListeners() {
            var boxes = getCategoryCheckboxes();
            boxes.forEach(function(b) {
                b.addEventListener('change', function() {
                    updateToggleAllState();
                });
            });
        }

        // initialize listeners/state
        bindCategoryCheckboxListeners();
        updateToggleAllState();

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

        function doRefreshRequestCategory(categoryIds) {
            if (!refreshUrl || !listEl) return;
            var params = new URLSearchParams();
            if (Array.isArray(categoryIds)) {
                categoryIds.forEach(function(cid) {
                    params.append('category_ids[]', cid);
                });
            } else if (categoryIds) {
                params.append('category_ids[]', categoryIds);
            }
            var url = refreshUrl + (params.toString() ? ('?' + params.toString()) : '');
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

        function applyLocalFilterCategory(categoryIds) {
            if (!listEl) return;
            var rows = listEl.querySelectorAll('[data-category-id]');
            var prevScroll = listEl.scrollTop;
            var ids = categoryIds;
            if (!ids) ids = [];
            if (!Array.isArray(ids)) ids = [ids];
            if (ids.length === 0) {
                rows.forEach(function(r) {
                    r.style.display = 'flex';
                });
                listEl.scrollTop = prevScroll;
                return;
            }
            var s = new Set(ids.map(String));
            rows.forEach(function(r) {
                var v = r.dataset.categoryId || '';
                if (!v) {
                    r.style.display = 'none';
                    return;
                }
                if (s.has(String(v))) r.style.display = 'flex';
                else r.style.display = 'none';
            });
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
                var boxes = getCategoryCheckboxes();
                var selected = boxes.filter(function(b) {
                    return b.checked;
                }).map(function(b) {
                    return b.value;
                });
                label.textContent = formatCategoryDisplay(selected);
                label.style.display = selected && selected.length ? 'inline-block' : 'none';
                if (dropdown) dropdown.style.display = 'none';
                if (btn) btn.setAttribute('aria-expanded', 'false');
                var detail = {
                    id: id,
                    category_ids: selected
                };
                document.dispatchEvent(new CustomEvent('assetsFailuresFilterChanged', {
                    detail: detail
                }));
                doLivewireEmit('assetsFailuresFilterChanged', detail);
                if (refreshUrl) {
                    doRefreshRequestCategory(selected);
                } else {
                    applyLocalFilterCategory(selected);
                }
            });
        }

        if (cancel) {
            cancel.addEventListener('click', function(e) {
                if (dropdown) dropdown.style.display = 'none';
                if (btn) btn.setAttribute('aria-expanded', 'false');
            });
        }

        // toggle-all behavior: check/uncheck visible checkboxes
        if (toggleAll) {
            toggleAll.addEventListener('change', function() {
                var boxes = getCategoryCheckboxes();
                var checked = !!this.checked;
                boxes.forEach(function(b) {
                    var wrapper = b.closest('[data-cat-name]');
                    if (wrapper && wrapper.style.display === 'none') return;
                    b.checked = checked;
                });
                updateToggleAllState();
            });
        }

        // search/filter the checkbox list
        if (categorySearch && categoryList) {
            categorySearch.addEventListener('input', function() {
                var q = String(this.value || '').toLowerCase().trim();
                var items = categoryList.querySelectorAll('[data-cat-name]');
                items.forEach(function(it) {
                    var name = String(it.dataset.catName || '').toLowerCase();
                    it.style.display = (!q || name.indexOf(q) !== -1) ? '' : 'none';
                });
                updateToggleAllState();
            });
        }

        if (clear) {
            clear.addEventListener('click', function(e) {
                var boxes = getCategoryCheckboxes();
                boxes.forEach(function(b) {
                    b.checked = false;
                });
                if (label) {
                    label.textContent = 'Filter';
                    label.style.display = 'none';
                }
                if (dropdown) dropdown.style.display = 'none';
                if (btn) btn.setAttribute('aria-expanded', 'false');
                var detail = {
                    id: id,
                    category_ids: []
                };
                document.dispatchEvent(new CustomEvent('assetsFailuresFilterChanged', {
                    detail: detail
                }));
                doLivewireEmit('assetsFailuresFilterChanged', detail);
                if (refreshUrl) {
                    doRefreshRequestCategory([]);
                } else {
                    applyLocalFilterCategory([]);
                }
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

    })();
</script>
