@php
    // Retrieve all assets for dashboard analytics
    $assets = \App\Models\Asset::AssetsForShow()->get();

    // Initialize collection for predicted replacements
    $predicted = collect();
    $now = \Carbon\Carbon::now();

    // For each asset, calculate remaining useful life and replacement recommendation
    foreach ($assets as $a) {
        // Skip assets without purchase date
        if (!$a->purchase_date) {
            continue;
        }
        try {
            // Parse purchase date
            $purchased = \Carbon\Carbon::parse($a->purchase_date);
        } catch (\Exception $e) {
            continue;
        }
        // Calculate asset age in years
        $ageMonths = $purchased->diffInMonths($now);
        $ageYears = round($ageMonths / 12, 1);

        // Determine expected lifespan (years) using asset/model fields or warranty
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
        // Fallback to default lifespan if none found
        if (!$expected) {
            $expected = 5.0;
        }

        // Calculate remaining useful life
        $remaining = round($expected - $ageYears, 1);

        // Get most recent maintenance record for failure/recent service info
        $lastMaint = \App\Models\Maintenance::where('asset_id', $a->id)->orderByDesc('start_date')->first();
        $recentFailure = false;
        $lastDate = null;
        $lastType = null;
        if ($lastMaint) {
            $lastDate = $lastMaint->start_date ? \Carbon\Carbon::parse($lastMaint->start_date)->toDateString() : null;
            $lastType = $lastMaint->asset_maintenance_type ?? null;
            // Mark as recent failure if within last 180 days
            $recentFailure = $lastMaint->start_date
                ? \Carbon\Carbon::parse($lastMaint->start_date)->diffInDays($now) <= 180
                : false;
        }

        // Compute recommended replacement date
        if ($remaining <= 0) {
            $recommended = $now->toDateString();
        } else {
            $recommended = \Carbon\Carbon::now()
                ->addMonths(ceil($remaining * 12))
                ->startOfMonth()
                ->toDateString();
        }

        // Add asset prediction data to collection
        $predicted->push([
            'id' => $a->id,
            'name' => $a->name ?: ($a->asset_tag ?: 'Asset #' . $a->id),
            'category' => optional($a->category)->name ?: optional(optional($a->model)->category)->name ?: '',
            'age_years' => $ageYears,
            'expected_life_years' => round($expected, 1),
            'remaining_years' => $remaining,
            'recent_failure' => $recentFailure,
            'last_maintenance' => $lastDate,
            'last_type' => $lastType,
            'recommended_date' => $recommended,
        ]);
    }

    // Sort by lowest remaining useful life and take top 12 for display
    $predicted = $predicted->sortBy('remaining_years')->values()->take(12);
@endphp
<div class="box box-default">
    <div class="box-header with-border">
        <h2 class="box-title" style="font-weight:700">Assets Predicted For Replacement</h2>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse" aria-hidden="true">
                <x-icon type="minus" />
                <span class="sr-only">{{ trans('general.collapse') }}</span>
            </button>
        </div>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            @if ($predicted->isEmpty())
                <div class="text-muted">No assets with replacement predictions available.</div>
            @else
                <table id="dashPredictedReplacements" class="table table-striped snipe-table" data-toggle="table"
                    data-fixed-table-toolbar="true" data-cookie-id-table="dashPredictedReplacements" data-height="500"
                    data-pagination="false">
                    <thead>
                        <tr>
                            <th>Asset Name</th>
                            <th>Category</th>
                            <th>Age (yrs)</th>
                            <th>Expected Lifespan (yrs)</th>
                            <th>Remaining Useful Life (yrs)</th>
                            <th>Condition / Recent Failure</th>
                            <th>Recommended Replacement Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($predicted as $p)
                            <tr>
                                <td>{{ $p['name'] }}</td>
                                <td>{{ $p['category'] }}</td>
                                <td>{{ $p['age_years'] }}</td>
                                <td>{{ $p['expected_life_years'] }}</td>
                                <td style="font-weight:700">
                                    {{ $p['remaining_years'] <= 0 ? 'Overdue' : $p['remaining_years'] }}</td>
                                <td>
                                    @if ($p['recent_failure'])
                                        <span class="text-danger">Recent failure
                                            ({{ $p['last_maintenance'] }})
                                        </span>
                                    @elseif($p['last_maintenance'])
                                        <span class="text-muted">Last serviced
                                            {{ $p['last_maintenance'] }}</span>
                                    @else
                                        <span class="text-muted">No recent service</span>
                                    @endif
                                </td>
                                <td>{{ $p['recommended_date'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
    <div class="box-footer text-center" style="background:transparent;border-top:0;padding-top:8px;">
        <a href="{{ route('hardware.index') }}" class="btn btn-primary btn-sm"
            style="width:100%">{{ trans('general.viewall') }}</a>
    </div>
</div>
