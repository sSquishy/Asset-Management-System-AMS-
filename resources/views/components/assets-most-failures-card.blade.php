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
$id = 'failures_'.substr(md5($title),0,8);
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

    @if(empty($items))
        <div style="padding:18px; color:#6b7280; font-size:13px">No failure or maintenance data available to display.</div>
    @else
        @php
            $max = max(array_column($items, 'count'));
            if($max <= 0) $max = 1;
        @endphp
        <div style="flex:1; overflow:auto; display:flex; flex-direction:column; gap:10px; padding-right:6px;">
            @foreach($items as $it)
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="flex:1">
                        <div style="font-size:13px; font-weight:600; color:{{ $textColor }}">{{ $it['label'] }}</div>
                        <div style="font-size:11px; color:{{ $textColor }}">{{ $it['model'] }} · Last: {{ $it['last'] ?? '-' }}</div>
                    </div>
                    <div style="width:160px; display:flex; align-items:center; gap:8px">
                        @php $barPct = round(($it['count'] / $max) * 100); @endphp
                        <div style="flex:1; background:#e6e9ef; height:12px; border-radius:6px; overflow:hidden">
                            <div style="width:{{ $barPct }}%; height:12px; background:{{ $accent }}; border-radius:6px"></div>
                        </div>
                        <div style="width:36px; text-align:right; font-size:12px; color:{{ $textColor }}">{{ $it['count'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>
