@php
    $assets = \App\Models\Asset::AssetsForShow()->get();
    $predicted = collect();
    $now = \Carbon\Carbon::now();
    foreach ($assets as $a) {
        if (!$a->purchase_date) {
            continue;
        }
        try {
            $purchased = \Carbon\Carbon::parse($a->purchase_date);
        } catch (\Exception $e) {
            continue;
        }
        $ageMonths = $purchased->diffInMonths($now);
        $ageYears = round($ageMonths / 12, 1);
        $expected = null;
        if (data_get($a, 'expected_life_years')) {
            $expected = (float) data_get($a, 'expected_life_years');
        } elseif (data_get($a, 'expected_life')) {
            $expected = (float) data_get($a, 'expected_life');
        } elseif (!empty($a->warranty_months)) {
            $expected = max(((float) $a->warranty_months / 12) * 1.5, 3);
        } elseif (data_get($a, 'model.expected_life_years')) {
            $expected = (float) data_get($a, 'model.expected_life_years');
        }
        if (!$expected) {
            $expected = 5.0;
        }
        $remaining = round($expected - $ageYears, 1);
        $lastMaint = \App\Models\Maintenance::where('asset_id', $a->id)->orderByDesc('start_date')->first();
        $recentFailure = false;
        $lastDate = null;
        $lastType = null;
        if ($lastMaint) {
            $lastDate = $lastMaint->start_date
                ? \Carbon\Carbon::parse($lastMaint->start_date)->toDateString()
                : null;
            $lastType = $lastMaint->asset_maintenance_type ?? null;
            $recentFailure = $lastMaint->start_date
                ? \Carbon\Carbon::parse($lastMaint->start_date)->diffInDays($now) <= 180
                : false;
        }
        if ($remaining <= 0) {
            $recommended = $now->toDateString();
        } else {
            $recommended = \Carbon\Carbon::now()
                ->addMonths(ceil($remaining * 12))
                ->startOfMonth()
                ->toDateString();
        }
        $predicted->push([
            'id' => $a->id,
            'name' => $a->name ?: ($a->asset_tag ?: 'Asset #' . $a->id),
            'category' => optional($a->category)->name ?: optional($a->model)->name ?: '',
            'age_years' => $ageYears,
            'expected_life_years' => round($expected, 1),
            'remaining_years' => $remaining,
            'recent_failure' => $recentFailure,
            'last_maintenance' => $lastDate,
            'last_type' => $lastType,
            'recommended_date' => $recommended,
        ]);
    }
    $predicted = $predicted->sortBy('remaining_years')->values()->take(12);
    $recommended = $predicted
        ->map(function ($p) {
            $asset = \App\Models\Asset::find($p['id']);
            $ageYears = null;
            if ($asset && $asset->purchase_date) {
                try {
                    $ageYears = round(
                        \Carbon\Carbon::parse($asset->purchase_date)->diffInDays(
                            \Carbon\Carbon::now(),
                        ) / 365,
                        1,
                    );
                } catch (\Exception $e) {
                    $ageYears = null;
                }
            }
            $avgRepairCost =
                (float) (\App\Models\Maintenance::where('asset_id', $p['id'])->avg('cost') ?? 0);
            if ($p['recent_failure']) {
                $reason = 'High Failure';
            } elseif ($ageYears !== null && $ageYears >= 5) {
                $reason = 'Age';
            } elseif ($avgRepairCost > 500) {
                $reason = 'Cost of Repair';
            } else {
                $reason = 'Age';
            }
            if ($p['remaining_years'] <= 0 || $p['recent_failure']) {
                $priority = 'High';
            } elseif ($p['remaining_years'] <= 2) {
                $priority = 'Medium';
            } else {
                $priority = 'Low';
            }
            $estCost =
                $asset && $asset->purchase_cost
                    ? \App\Helpers\Helper::formatCurrencyOutput($asset->purchase_cost)
                    : trans('general.unknown');
            return array_merge($p, [
                'reason' => $reason,
                'priority' => $priority,
                'estCost' => $estCost,
                'age_years_calc' => $ageYears,
            ]);
        })
        ->values()
        ->take(12);
@endphp
<div class="box box-default" style="background:#fff;border-radius:6px;box-shadow:0 2px 6px rgba(0,0,0,0.08);">
    <div class="box-header with-border">
        <h2 class="box-title" style="font-weight:700">Recommended Assets for Replacement</h2>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse" aria-hidden="true">
                <x-icon type="minus" />
                <span class="sr-only">{{ trans('general.collapse') }}</span>
            </button>
            <div class="btn-group">
                <button type="button" class="btn btn-box-tool dropdown-toggle" data-toggle="dropdown"
                    aria-expanded="false">
                    <x-icon type="more-vert" />
                </button>
            </div>
        </div>
    </div>
    <div class="box-body" style="padding:8px;">
        <div style="max-height:500px; overflow-y:auto;">
            <ul class="list-group" style="margin-bottom:0;">
                @forelse($recommended as $r)
                    <li class="list-group-item"
                        style="display:flex;align-items:center;justify-content:space-between;padding:12px 10px; @if ($r['priority'] == 'High') border-left:4px solid #d9534f; box-shadow: inset 0 0 0 1px rgba(217,83,79,0.04); @else border-left:4px solid transparent; @endif">
                        <div style="flex:1;min-width:0;padding-right:10px;">
                            <div
                                style="font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:14px;">
                                {{ $r['name'] }}</div>
                            <div
                                style="display:flex;gap:12px;margin-top:6px;font-size:12px;color:#6b7280;align-items:center;flex-wrap:wrap;">
                                <div>Reason: <strong
                                        style="color:#374151;margin-left:6px;">{{ $r['reason'] }}</strong>
                                </div>
                                <div>Age: <strong
                                        style="color:#374151;margin-left:6px;">{{ $r['age_years_calc'] !== null ? $r['age_years_calc'] . ' yrs' : '—' }}</strong>
                                </div>
                            </div>
                        </div>
                        <div
                            style="display:flex;flex-direction:column;align-items:flex-end;gap:6px;min-width:140px;">
                            <div>
                                @if ($r['priority'] == 'High')
                                    <span class="label label-danger"
                                        style="font-weight:700;padding:6px 10px;background:#d9534f;color:#fff;border-radius:12px;">High</span>
                                @elseif($r['priority'] == 'Medium')
                                    <span class="label label-warning"
                                        style="font-weight:700;padding:6px 10px;background:#f0ad4e;color:#fff;border-radius:12px;">Medium</span>
                                @else
                                    <span class="label label-default"
                                        style="font-weight:700;padding:6px 10px;background:#6c757d;color:#fff;border-radius:12px;">Low</span>
                                @endif
                            </div>
                            <div style="font-weight:700;color:#111">{{ $r['estCost'] }}</div>
                        </div>
                    </li>
                @empty
                    <li class="list-group-item text-muted">No recommended assets at this time.</li>
                @endforelse
            </ul>
        </div>
    </div>
    <div class="box-footer text-center" style="background:transparent;border-top:0;padding-top:8px;">
        <a href="{{ route('hardware.index') }}" class="btn btn-primary btn-sm"
            style="width:100%">{{ trans('general.viewall') }}</a>
    </div>
</div>
