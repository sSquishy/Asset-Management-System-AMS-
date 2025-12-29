@extends('layouts/default')
{{-- Page title --}}
@section('title')
{{ trans('general.dashboard') }}
@parent
@stop


{{-- Page content --}}
@section('content')

@if ($snipeSettings->dashboard_message!='')
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <!-- /.box-header -->
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        {!!  Helper::parseEscapedMarkedown($snipeSettings->dashboard_message)  !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">

    <!-- panel -->
    <div class="col-lg-2 col-xs-6">
        <a href="{{ route('hardware.index') }}">
            <!-- small hardware box -->
            <div class="dashboard small-box bg-blue">
                <div class="inner">
                    <h3>{{ number_format(\App\Models\Asset::AssetsForShow()->count()) }}</h3>
                    <p>{{ trans('general.assets') }}</p>
                </div>
                <div class="icon" aria-hidden="true">
                    <x-icon type="assets" />
                </div>
                <span class="small-box-footer">
                    {{ trans('general.view_all') }}
                    <x-icon type="arrow-circle-right" />
                </span>
            </div>
        </a>
    </div><!-- ./col -->

    <div class="col-lg-2 col-xs-6">
        <a href="{{ route('licenses.index') }}" aria-hidden="true">
            <!-- small license box -->
            <div class="dashboard small-box bg-blue">
                <div class="inner">
                    <h3>{{ number_format($counts['license']) }}</h3>
                    <p>{{ trans('general.licenses') }}</p>
                </div>
                <div class="icon" aria-hidden="true">
                    <x-icon type="licenses" />
                </div>
                <span class="small-box-footer">
                    {{ trans('general.view_all') }}
                    <x-icon type="arrow-circle-right" />
                </span>
            </div>
        </a>
    </div><!-- ./col -->


    <div class="col-lg-2 col-xs-6">
    <!-- small accessories box -->
        <a href="{{ route('accessories.index') }}">
            <div class="dashboard small-box bg-blue">
                <div class="inner">
                    <h3> {{ number_format($counts['accessory']) }}</h3>
                    <p>{{ trans('general.accessories') }}</p>
                </div>
                <div class="icon" aria-hidden="true">
                    <x-icon type="accessories" />
                </div>
                <span class="small-box-footer">
                    {{ trans('general.view_all') }}
                <x-icon type="arrow-circle-right" />
                </span>
            </div>
        </a>
    </div><!-- ./col -->

    <div class="col-lg-2 col-xs-6">
    <!-- small consumables box -->
        <a href="{{ route('consumables.index') }}">
            <div class="dashboard small-box bg-blue">
                <div class="inner">
                    <h3> {{ number_format($counts['consumable']) }}</h3>
                    <p>{{ trans('general.consumables') }}</p>
                </div>
                <div class="icon" aria-hidden="true">
                    <x-icon type="consumables" />
                </div>
                <span class="small-box-footer">
                    {{ trans('general.view_all') }}
                    <x-icon type="arrow-circle-right" />
                </span>
            </div>
        </a>
    </div><!-- ./col -->

    <div class="col-lg-2 col-xs-6">
        <!-- small components box -->
        <a href="{{ route('components.index') }}">
            <div class="dashboard small-box bg-blue">
                <div class="inner">
                    <h3>{{ number_format($counts['component']) }}</h3>
                    <p>{{ trans('general.components') }}</p>
                </div>
                <div class="icon" aria-hidden="true">
                    <x-icon type="components" />
                </div>
                <span class="small-box-footer">
                    {{ trans('general.view_all') }}
                    <x-icon type="arrow-circle-right" />
                </span>
            </div>
        </a>
    </div><!-- ./col -->

    <div class="col-lg-2 col-xs-6">
        <!-- small users box -->
        <a href="{{ route('users.index') }}">
            <div class="dashboard small-box bg-blue">
                <div class="inner">
                    <h3>{{ number_format($counts['user']) }}</h3>
                    <p>{{ trans('general.people') }}</p>
                </div>
                <div class="icon" aria-hidden="true">
                    <x-icon type="users" />
                </div>
                <span class="small-box-footer">
                    {{ trans('general.view_all') }}
                    <x-icon type="arrow-circle-right" />
                </span>
            </div>
        </a>
    </div><!-- ./col -->

</div>
</div>

@php
    $assets = \App\Models\Asset::AssetsForShow()->get();
    $totalPurchase = $assets->sum('purchase_cost');
    $currentTotal = 0;
    foreach($assets as $a){ $currentTotal += (float)($a->getDepreciatedValue() ?? 0); }
    $depreciationAmount = $totalPurchase - $currentTotal;
    $depreciationPercent = $totalPurchase > 0 ? round(($depreciationAmount / $totalPurchase) * 100, 2) : 0;

    // Build a simple 6-month purchase-cost trend (grouped by purchase_date month)
    $trend = [];
    for($i = 5; $i >= 0; $i--) {
        $start = \Carbon\Carbon::now()->subMonths($i)->startOfMonth();
        $end = \Carbon\Carbon::now()->subMonths($i)->endOfMonth();
        $sum = \App\Models\Asset::AssetsForShow()
                ->whereNotNull('purchase_date')
                ->whereBetween('purchase_date', [$start->toDateString(), $end->toDateString()])
                ->sum('purchase_cost');
        $trend[] = (float) $sum;
    }

    // Build a 6-month warranty expiration forecast (counts per upcoming month)
    $warrantySeries = [];
    $warrantyLabels = [];
    for ($i = 0; $i < 6; $i++) {
        $start = \Carbon\Carbon::now()->addMonths($i)->startOfMonth();
        $end = \Carbon\Carbon::now()->addMonths($i)->endOfMonth();
        $warrantyLabels[] = $start->format('M Y');
        $count = $assets->filter(function($a) use ($start, $end) {
            if (! $a->purchase_date || ! $a->warranty_months) return false;
            try {
                $expiry = \Carbon\Carbon::parse($a->purchase_date)->addMonths($a->warranty_months);
            } catch (\Exception $e) { return false; }
            return $expiry->between($start, $end);
        })->count();
        $warrantySeries[] = (int) $count;
    }

    $metrics = [
        [
            'value' => \App\Helpers\Helper::formatCurrencyOutput($totalPurchase),
            'subtitle' => 'Total Purchase Cost',
            'trend' => $trend,
            'trendLabel' => '6-month purchases'
        ],
        [
            'value' => \App\Helpers\Helper::formatCurrencyOutput($currentTotal),
            'subtitle' => 'Current Total Value (after depreciation)'
        ],
        [
            'value' => \App\Helpers\Helper::formatCurrencyOutput($depreciationAmount),
            'subtitle' => 'Total Depreciation Amount',
        ],
    ];
    // Top suppliers by repairs: compute repairs count, average duration (days) and average cost
    $tv_sort = request()->get('tv_sort', 'repairs');
    $tv_order = strtolower(request()->get('tv_order', 'desc')) === 'asc' ? 'asc' : 'desc';
    $supplierRows = \App\Models\Maintenance::select('supplier_id', \DB::raw('count(*) as repairs'), \DB::raw('avg(case when completion_date is not null then datediff(completion_date, start_date) end) as avg_duration'), \DB::raw('avg(cost) as avg_cost'))
        ->whereNotNull('supplier_id')
        ->groupBy('supplier_id')
        ->get();

    $supplierIds = $supplierRows->pluck('supplier_id')->unique()->filter()->values()->all();
    $suppliers = \App\Models\Supplier::whereIn('id', $supplierIds)->get()->keyBy('id');

    $topSuppliers = $supplierRows->map(function($r) use ($suppliers) {
        $s = $suppliers->get($r->supplier_id);
        return [
            'id' => $r->supplier_id,
            'name' => $s ? $s->name : trans('general.unknown'),
            'repairs' => (int) $r->repairs,
            'avg_duration' => $r->avg_duration !== null ? round($r->avg_duration, 1) : null,
            'avg_cost' => $r->avg_cost !== null ? round($r->avg_cost, 2) : null,
        ];
    });

    // apply simple request-driven sorting on the collection
    if ($tv_sort == 'avg_duration') {
        $topSuppliers = $tv_order == 'asc' ? $topSuppliers->sortBy('avg_duration') : $topSuppliers->sortByDesc('avg_duration');
    } elseif ($tv_sort == 'avg_cost') {
        $topSuppliers = $tv_order == 'asc' ? $topSuppliers->sortBy('avg_cost') : $topSuppliers->sortByDesc('avg_cost');
    } else {
        $topSuppliers = $tv_order == 'asc' ? $topSuppliers->sortBy('repairs') : $topSuppliers->sortByDesc('repairs');
    }

    $topSuppliers = $topSuppliers->values();
@endphp

<div class="row" style="margin-bottom:10px;">
    <div class="col-md-4" style="margin-bottom:12px;">
        @include('components.asset-depreciation-card', ['metrics' => $metrics])
    </div>
    <div class="col-md-4" style="margin-bottom:12px;">
        @include('components.warranty-expiration-forecast', ['series' => $warrantySeries, 'labels' => $warrantyLabels])
    </div>
    <div class="col-md-4" style="margin-bottom:12px;">
        @php
            // Build failures summary: top assets by maintenance count
            $failureRows = \App\Models\Maintenance::select('asset_id', \DB::raw('count(*) as failures'), \DB::raw('max(start_date) as last_failure'))
                ->groupBy('asset_id')
                ->orderByDesc('failures')
                ->take(6)
                ->get();
            $failureItems = [];
            foreach($failureRows as $fr) {
                $asset = \App\Models\Asset::with('model')->find($fr->asset_id);
                if(!$asset) continue;
                $failureItems[] = [
                    'label' => ($asset->name ?: $asset->asset_tag ?: 'Asset #'.$asset->id),
                    'model' => ($asset->model->name ?? $asset->model_number ?? ''),
                    'count' => (int) $fr->failures,
                    'last' => $fr->last_failure ? \Carbon\Carbon::parse($fr->last_failure)->toDateString() : null,
                ];
            }
        @endphp
        @include('components.assets-most-failures-card', ['items' => $failureItems])
    </div>
    {{-- <div class="col-md-6">
        <!-- Optional: place for Depreciation % or details -->
        <div class="box box-default">
            <div class="box-body" style="display:flex; align-items:center; justify-content:center; height:100%">
                <div style="text-align:center">
                    <div style="font-size:22px; font-weight:700">{{ $depreciationPercent }}%</div>
                    <div style="font-size:12px; color:#6b7280">Depreciation % (Overall)</div>
                </div>
            </div>
        </div>
    </div> --}}
</div>

<!-- Top Supplier and Supplier Reliability cards removed -->

@php
    // Predicted replacement table: compute simple RUL (remaining useful life)
    $predicted = collect();
    $now = \Carbon\Carbon::now();
    foreach ($assets as $a) {
        if (! $a->purchase_date) continue;
        try {
            $purchased = \Carbon\Carbon::parse($a->purchase_date);
        } catch (\Exception $e) { continue; }
        $ageMonths = $purchased->diffInMonths($now);
        $ageYears = round($ageMonths / 12, 1);

        // Determine expected lifespan (years) with fallbacks
        $expected = null;
        if (data_get($a, 'expected_life_years')) {
            $expected = (float) data_get($a, 'expected_life_years');
        } elseif (data_get($a, 'expected_life')) {
            $expected = (float) data_get($a, 'expected_life');
        } elseif (!empty($a->warranty_months)) {
            $expected = max((float)$a->warranty_months / 12 * 1.5, 3);
        } elseif (data_get($a, 'model.expected_life_years')) {
            $expected = (float) data_get($a, 'model.expected_life_years');
        }
        if (! $expected) { $expected = 5.0; } // sensible default if nothing found

        $remaining = round($expected - $ageYears, 1);

        // Recent maintenance/failure (within 180 days)
        $lastMaint = \App\Models\Maintenance::where('asset_id', $a->id)->orderByDesc('start_date')->first();
        $recentFailure = false;
        $lastDate = null;
        $lastType = null;
        if ($lastMaint) {
            $lastDate = $lastMaint->start_date ? \Carbon\Carbon::parse($lastMaint->start_date)->toDateString() : null;
            $lastType = $lastMaint->asset_maintenance_type ?? null;
            $recentFailure = $lastMaint->start_date ? \Carbon\Carbon::parse($lastMaint->start_date)->diffInDays($now) <= 180 : false;
        }

        // Recommended replacement date
        if ($remaining <= 0) {
            $recommended = $now->toDateString();
        } else {
            $recommended = \Carbon\Carbon::now()->addMonths(ceil($remaining * 12))->startOfMonth()->toDateString();
        }

        $predicted->push([
            'id' => $a->id,
            'name' => $a->name ?: ($a->asset_tag ?: 'Asset #'.$a->id),
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

    // sort by lowest remaining useful life (most urgent) and take top 12
    $predicted = $predicted->sortBy('remaining_years')->values()->take(12);
@endphp

<!-- Predicted Replacements -->
<div class="row" style="margin-top:10px;">
    <div class="col-md-8">
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
                    @if($predicted->isEmpty())
                        <div class="text-muted">No assets with replacement predictions available.</div>
                    @else
                        <table
                            id="dashPredictedReplacements"
                            class="table table-striped snipe-table"
                            data-toggle="table"
                            data-fixed-table-toolbar="true"
                            data-cookie-id-table="dashPredictedReplacements"
                            data-height="500"
                            data-pagination="false"
                        >
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
                                @foreach($predicted as $p)
                                <tr>
                                    <td>{{ $p['name'] }}</td>
                                    <td>{{ $p['category'] }}</td>
                                    <td>{{ $p['age_years'] }}</td>
                                    <td>{{ $p['expected_life_years'] }}</td>
                                    <td style="font-weight:700">{{ $p['remaining_years'] <= 0 ? 'Overdue' : $p['remaining_years'] }}</td>
                                    <td>
                                        @if($p['recent_failure'])
                                            <span class="text-danger">Recent failure ({{ $p['last_maintenance'] }})</span>
                                        @elseif($p['last_maintenance'])
                                            <span class="text-muted">Last serviced {{ $p['last_maintenance'] }}</span>
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
                <a href="{{ route('hardware.index') }}" class="btn btn-primary btn-sm" style="width:100%">{{ trans('general.viewall') }}</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="box box-default" style="background:#fff;border-radius:6px;box-shadow:0 2px 6px rgba(0,0,0,0.08);">
            <div class="box-header with-border">
                <h2 class="box-title" style="font-weight:700">Recommended Assets for Replacement</h2>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse" aria-hidden="true">
                        <x-icon type="minus" />
                        <span class="sr-only">{{ trans('general.collapse') }}</span>
                    </button>
                    <div class="btn-group">
                        <button type="button" class="btn btn-box-tool dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                            <x-icon type="more-vert" />
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right" role="menu">
                            <li><a href="#">{{ trans('general.export') }}</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="box-body" style="padding:8px;">
                @php
                    // Build recommended list from $predicted and compute age from purchase_date
                    $recommended = $predicted->map(function($p){
                        $asset = \App\Models\Asset::find($p['id']);
                        $ageYears = null;
                        if ($asset && $asset->purchase_date) {
                            try {
                                $ageYears = round(\Carbon\Carbon::parse($asset->purchase_date)->diffInDays(\Carbon\Carbon::now())/365, 1);
                            } catch (\Exception $e) {
                                $ageYears = null;
                            }
                        }

                        $avgRepairCost = (float) (\App\Models\Maintenance::where('asset_id', $p['id'])->avg('cost') ?? 0);

                        // Determine reason (High Failure, Age, Cost of Repair)
                        if ($p['recent_failure']) {
                            $reason = 'High Failure';
                        } elseif ($ageYears !== null && $ageYears >= 5) {
                            $reason = 'Age';
                        } elseif ($avgRepairCost > 500) {
                            $reason = 'Cost of Repair';
                        } else {
                            $reason = 'Age';
                        }

                        // Priority based on remaining years and recent failures
                        if ($p['remaining_years'] <= 0 || $p['recent_failure']) {
                            $priority = 'High';
                        } elseif ($p['remaining_years'] <= 2) {
                            $priority = 'Medium';
                        } else {
                            $priority = 'Low';
                        }

                        $estCost = $asset && $asset->purchase_cost ? \App\Helpers\Helper::formatCurrencyOutput($asset->purchase_cost) : trans('general.unknown');

                        return array_merge($p, [
                            'reason' => $reason,
                            'priority' => $priority,
                            'estCost' => $estCost,
                            'age_years_calc' => $ageYears,
                        ]);
                    })->values()->take(12);
                @endphp

                <div style="max-height:500px; overflow-y:auto;">
                    <ul class="list-group" style="margin-bottom:0;">
                        @forelse($recommended as $r)
                            <li class="list-group-item" style="display:flex;align-items:center;justify-content:space-between;padding:12px 10px; @if($r['priority'] == 'High') border-left:4px solid #d9534f; box-shadow: inset 0 0 0 1px rgba(217,83,79,0.04); @else border-left:4px solid transparent; @endif">
                                <div style="flex:1;min-width:0;padding-right:10px;">
                                    <div style="font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:14px;">{{ $r['name'] }}</div>
                                    <div style="display:flex;gap:12px;margin-top:6px;font-size:12px;color:#6b7280;align-items:center;flex-wrap:wrap;">
                                        <div>Reason: <strong style="color:#374151;margin-left:6px;">{{ $r['reason'] }}</strong></div>
                                        <div>Age: <strong style="color:#374151;margin-left:6px;">{{ $r['age_years_calc'] !== null ? $r['age_years_calc'].' yrs' : '—' }}</strong></div>
                                    </div>
                                </div>
                                <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px;min-width:140px;">
                                    <div>
                                        @if($r['priority'] == 'High')
                                            <span class="label label-danger" style="font-weight:700;padding:6px 10px;background:#d9534f;color:#fff;border-radius:12px;">High</span>
                                        @elseif($r['priority'] == 'Medium')
                                            <span class="label label-warning" style="font-weight:700;padding:6px 10px;background:#f0ad4e;color:#fff;border-radius:12px;">Medium</span>
                                        @else
                                            <span class="label label-default" style="font-weight:700;padding:6px 10px;background:#6c757d;color:#fff;border-radius:12px;">Low</span>
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
                <a href="{{ route('hardware.index') }}" class="btn btn-primary btn-sm" style="width:100%">{{ trans('general.viewall') }}</a>
            </div>
        </div>
    </div>
</div>

    @php
        // Assets nearing warranty expiration (within next N days)
        $warrantySoon = collect();
        $thresholdDays = 60; // consider "nearing" as within next 60 days
        foreach ($assets as $a) {
            if (! $a->purchase_date || empty($a->warranty_months)) continue;
            try {
                $expiry = \Carbon\Carbon::parse($a->purchase_date)->addMonths($a->warranty_months);
            } catch (\Exception $e) { continue; }
            $now = \Carbon\Carbon::now();
            if ($expiry->lt($now)) continue; // already expired
            $daysRemaining = $now->diffInDays($expiry);
            if ($daysRemaining <= $thresholdDays) {
                $assigned = optional($a->assigned_user)->name ?: optional($a->assigned_to)->name ?: optional($a->user)->name ?: '';
                $department = optional($a->location)->name ?: optional($a->department)->name ?: '';
                $supplier = optional($a->supplier)->name ?: '';
                $warrantySoon->push([
                    'asset_tag' => $a->asset_tag ?: '',
                    'name' => $a->name ?: ('Asset #'.$a->id),
                    'category' => optional($a->category)->name ?: optional($a->model)->name ?: '',
                    'assigned' => $assigned,
                    'department' => $department,
                    'warranty_date' => $expiry->toDateString(),
                    'days_remaining' => $daysRemaining,
                    'supplier' => $supplier,
                ]);
            }
        }
        $warrantySoon = $warrantySoon->sortBy('days_remaining')->values();
    @endphp

    <div class="row" style="margin-top:10px;">
        <div class="col-md-8">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title" style="font-weight:700">Asset Nearing Warranty Expiration</h2>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse" aria-hidden="true">
                            <x-icon type="minus" />
                            <span class="sr-only">{{ trans('general.collapse') }}</span>
                        </button>
                    </div>
                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table
                            id="dashWarrantySoon"
                            class="table table-striped snipe-table"
                            data-toggle="table"
                            data-fixed-table-toolbar="true"
                            data-cookie-id-table="dashWarrantySoon"
                            data-height="500"
                            data-search="true"
                            data-pagination="false"
                        >
                            <thead>
                                <tr>
                                    <th>Asset Tag</th>
                                    <th>Asset Name</th>
                                    <th>Category</th>
                                    <th>Assigned To / Department</th>
                                    <th>Warranty Date</th>
                                    <th>Days Remaining</th>
                                    <th>Supplier</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($warrantySoon->isEmpty())
                                    <tr>
                                        <td colspan="7" class="text-muted">No assets nearing warranty expiration.</td>
                                    </tr>
                                @else
                                    @foreach($warrantySoon as $w)
                                    <tr>
                                        <td>{{ $w['asset_tag'] }}</td>
                                        <td>{{ $w['name'] }}</td>
                                        <td>{{ $w['category'] }}</td>
                                        <td>{{ $w['assigned'] ?: $w['department'] }}</td>
                                        <td>{{ $w['warranty_date'] }}</td>
                                        <td style="font-weight:700">{{ $w['days_remaining'] }}</td>
                                        <td>{{ $w['supplier'] }}</td>
                                    </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center col-md-12" style="padding-top: 10px;">
                        <a href="{{ route('hardware.index') }}" class="btn btn-primary btn-sm" style="width: 100%">{{ trans('general.viewall') }}</a>
                    </div>
                </div>
            </div>
        </div>
        @php
            // Vendors maintenance aggregation: top vendors by total cost (or jobs)
            $vendorRows = \App\Models\Maintenance::select('supplier_id', \DB::raw('count(*) as jobs'), \DB::raw('coalesce(sum(cost),0) as total_cost'), \DB::raw('avg(case when completion_date is not null and start_date is not null then datediff(completion_date,start_date) end) as avg_duration'))
                ->whereNotNull('supplier_id')
                ->groupBy('supplier_id')
                ->get();

            $vendorIds = $vendorRows->pluck('supplier_id')->unique()->filter()->values()->all();
            $vendorsMap = \App\Models\Supplier::whereIn('id', $vendorIds)->get()->keyBy('id');

            $vendorItems = collect();
            foreach ($vendorRows as $vr) {
                $s = $vendorsMap->get($vr->supplier_id);
                if (! $s) continue;
                $vendorItems->push([
                    'id' => $vr->supplier_id,
                    'name' => $s->name ?: ('Supplier #'.$vr->supplier_id),
                    'jobs' => (int) $vr->jobs,
                    'total_cost' => (float) $vr->total_cost,
                    'total_cost_formatted' => \App\Helpers\Helper::formatCurrencyOutput($vr->total_cost),
                    'avg_duration' => $vr->avg_duration !== null ? round($vr->avg_duration, 1) : null,
                ]);
            }
            $vendorItems = $vendorItems->sortByDesc('total_cost')->values()->take(8);
        @endphp

        @if($vendorItems->isNotEmpty())
            <div class="col-md-4">
                @include('components.vendors-maintenance-card', ['items' => $vendorItems])
            </div>
        @endif
    
    </div>

    @if ($counts['grand_total'] == 0)

    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header with-border">
                    <h2 class="box-title">{{ trans('general.dashboard_info') }}</h2>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">

                            <div class="progress">
                                <div class="progress-bar progress-bar-yellow" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 60%">
                                    <span class="sr-only">{{ trans('general.60_percent_warning') }}</span>
                                </div>
                            </div>


                            <p><strong>{{ trans('general.dashboard_empty') }}</strong></p>

                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            @can('create', \App\Models\Asset::class)
                            <a class="btn bg-teal" style="width: 100%" href="{{ route('hardware.create') }}">{{ trans('general.new_asset') }}</a>
                            @endcan
                        </div>
                        <div class="col-md-2">
                            @can('create', \App\Models\License::class)
                                <a class="btn bg-maroon" style="width: 100%" href="{{ route('licenses.create') }}">{{ trans('general.new_license') }}</a>
                            @endcan
                        </div>
                        <div class="col-md-2">
                            @can('create', \App\Models\Accessory::class)
                                <a class="btn bg-orange" style="width: 100%" href="{{ route('accessories.create') }}">{{ trans('general.new_accessory') }}</a>
                            @endcan
                        </div>
                        <div class="col-md-2">
                            @can('create', \App\Models\Consumable::class)
                                <a class="btn bg-purple" style="width: 100%" href="{{ route('consumables.create') }}">{{ trans('general.new_consumable') }}</a>
                            @endcan
                        </div>
                        <div class="col-md-2">
                            @can('create', \App\Models\Component::class)
                                <a class="btn bg-yellow" style="width: 100%" href="{{ route('components.create') }}">{{ trans('general.new_component') }}</a>
                            @endcan
                        </div>
                        <div class="col-md-2">
                            @can('create', \App\Models\User::class)
                                <a class="btn bg-light-blue" style="width: 100%" href="{{ route('users.create') }}">{{ trans('general.new_user') }}</a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@else

<!-- recent activity -->
<div class="row">
  <div class="col-md-8">
    <div class="box">
      <div class="box-header with-border">
        <h2 class="box-title">{{ trans('general.recent_activity') }}</h2>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse" aria-hidden="true">
                <x-icon type="minus" />
                <span class="sr-only">{{ trans('general.collapse') }}</span>
            </button>
        </div>
      </div><!-- /.box-header -->
      <div class="box-body">
        <div class="row">
          <div class="col-md-12">
            <div class="table-responsive">

                <table
                    data-cookie-id-table="dashActivityReport"
                    data-height="500"
                    data-pagination="false"
                    data-side-pagination="server"
                    data-id-table="dashActivityReport"
                    data-sort-order="desc"
                    data-sort-name="created_at"
                    id="dashActivityReport"
                    class="table table-striped snipe-table"
                    data-url="{{ route('api.activity.index', ['limit' => 25]) }}">
                    <thead>
                    <tr>
                        <th data-field="icon" data-visible="true" style="width: 40px;" class="hidden-xs" data-formatter="iconFormatter"><span  class="sr-only">{{ trans('admin/hardware/table.icon') }}</span></th>
                        <th class="col-sm-3" data-visible="true" data-field="created_at" data-formatter="dateDisplayFormatter">{{ trans('general.date') }}</th>
                        <th class="col-sm-2" data-visible="true" data-field="admin" data-formatter="usersLinkObjFormatter">{{ trans('general.created_by') }}</th>
                        <th class="col-sm-2" data-visible="true" data-field="action_type">{{ trans('general.action') }}</th>
                        <th class="col-sm-3" data-visible="true" data-field="item" data-formatter="polymorphicItemFormatter">{{ trans('general.item') }}</th>
                        <th class="col-sm-2" data-visible="true" data-field="target" data-formatter="polymorphicItemFormatter">{{ trans('general.target') }}</th>
                    </tr>
                    </thead>
                </table>



            </div><!-- /.responsive -->
          </div><!-- /.col -->
          <div class="text-center col-md-12" style="padding-top: 10px;">
            <a href="{{ route('reports.activity') }}" class="btn btn-primary btn-sm" style="width: 100%">{{ trans('general.viewall') }}</a>
          </div>
        </div><!-- /.row -->
      </div><!-- ./box-body -->
    </div><!-- /.box -->
  </div>
  <div class="col-md-4">
        <div class="box box-default">
            <div class="box-header with-border">
                <h2 class="box-title">
                    {{ (\App\Models\Setting::getSettings()->dash_chart_type == 'name') ? trans('general.assets_by_status') : trans('general.assets_by_status_type') }}
                </h2>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse" aria-hidden="true">
                        <x-icon type="minus" />
                        <span class="sr-only">{{ trans('general.collapse') }}</span>
                    </button>
                </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="chart-responsive">
                            <canvas id="statusPieChart" height="260"></canvas>
                        </div> <!-- ./chart-responsive -->
                    </div> <!-- /.col -->
                </div> <!-- /.row -->
            </div><!-- /.box-body -->
        </div> <!-- /.box -->
  </div>

</div> <!--/row-->
<div class="row">
    <div class="col-md-6">

		@if ((($snipeSettings->scope_locations_fmcs!='1') && ($snipeSettings->full_multiple_companies_support=='1')))
			 <!-- Companies -->	
			<div class="box box-default">
				<div class="box-header with-border">
					<h2 class="box-title">{{ trans('general.companies') }}</h2>
					<div class="box-tools pull-right">
						<button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <x-icon type="minus" />
							<span class="sr-only">{{ trans('general.collapse') }}</span>
						</button>
					</div>
				</div>
				<!-- /.box-header -->
				<div class="box-body">
					<div class="row">
						<div class="col-md-12">
							<div class="table-responsive">
							<table
									data-cookie-id-table="dashCompanySummary"
									data-height="400"
                                    data-pagination="false"
									data-side-pagination="server"
									data-sort-order="desc"
									data-sort-field="assets_count"
									id="dashCompanySummary"
									class="table table-striped snipe-table"
									data-url="{{ route('api.companies.index', ['sort' => 'assets_count', 'order' => 'asc']) }}">

								<thead>
								<tr>
									<th class="col-sm-3" data-visible="true" data-field="name" data-formatter="companiesLinkFormatter" data-sortable="true">{{ trans('general.name') }}</th>
									<th class="col-sm-1" data-visible="true" data-field="users_count" data-sortable="true">
                                        <x-icon type="users" />
										<span class="sr-only">{{ trans('general.people') }}</span>
									</th>
									<th class="col-sm-1" data-visible="true" data-field="assets_count" data-sortable="true">
                                        <x-icon type="assets" />
										<span class="sr-only">{{ trans('general.asset_count') }}</span>
									</th>
									<th class="col-sm-1" data-visible="true" data-field="accessories_count" data-sortable="true">
                                        <x-icon type="accessories" />
										<span class="sr-only">{{ trans('general.accessories_count') }}</span>
									</th>
									<th class="col-sm-1" data-visible="true" data-field="consumables_count" data-sortable="true">
                                        <x-icon type="consumables" />
										<span class="sr-only">{{ trans('general.consumables_count') }}</span>
									</th>
									<th class="col-sm-1" data-visible="true" data-field="components_count" data-sortable="true">
                                        <x-icon type="components" />
										<span class="sr-only">{{ trans('general.components_count') }}</span>
									</th>
									<th class="col-sm-1" data-visible="true" data-field="licenses_count" data-sortable="true">
                                        <x-icon type="licenses" />
										<span class="sr-only">{{ trans('general.licenses_count') }}</span>
									</th>
								</tr>
								</thead>
							</table>
							</div>
						</div> <!-- /.col -->
						<div class="text-center col-md-12" style="padding-top: 10px;">
							<a href="{{ route('companies.index') }}" class="btn btn-primary btn-sm" style="width: 100%">{{ trans('general.viewall') }}</a>
						</div>
					</div> <!-- /.row -->

				</div><!-- /.box-body -->
			</div> <!-- /.box -->
		
		@else
			 <!-- Locations -->
			 <div class="box box-default">
				<div class="box-header with-border">
					<h2 class="box-title">{{ trans('general.locations') }}</h2>
					<div class="box-tools pull-right">
						<button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <x-icon type="minus" />
							<span class="sr-only">{{ trans('general.collapse') }}</span>
						</button>
					</div>
				</div>
				<!-- /.box-header -->
				<div class="box-body">
					<div class="row">
						<div class="col-md-12">
							<div class="table-responsive">
							<table
									data-cookie-id-table="dashLocationSummary"
									data-height="400"
									data-side-pagination="server"
                                    data-pagination="false"
									data-sort-order="desc"
									data-sort-field="assets_count"
									id="dashLocationSummary"
									class="table table-striped snipe-table"
									data-url="{{ route('api.locations.index', ['sort' => 'assets_count', 'order' => 'asc']) }}">
								<thead>
								<tr>
									<th class="col-sm-3" data-visible="true" data-field="name" data-formatter="locationsLinkFormatter" data-sortable="true">{{ trans('general.name') }}</th>
									
									<th class="col-sm-1" data-visible="true" data-field="assets_count" data-sortable="true">
                                        <x-icon type="assets" />
										<span class="sr-only">{{ trans('general.asset_count') }}</span>
									</th>
									<th class="col-sm-1" data-visible="true" data-field="assigned_assets_count" data-sortable="true">
										
										{{ trans('general.assigned') }}
									</th>
									<th class="col-sm-1" data-visible="true" data-field="users_count" data-sortable="true">
                                        <x-icon type="users" />
										<span class="sr-only">{{ trans('general.people') }}</span>
										
									</th>
									
								</tr>
								</thead>
							</table>
							</div>
						</div> <!-- /.col -->
						<div class="text-center col-md-12" style="padding-top: 10px;">
							<a href="{{ route('locations.index') }}" class="btn btn-primary btn-sm" style="width: 100%">{{ trans('general.viewall') }}</a>
						</div>
					</div> <!-- /.row -->

				</div><!-- /.box-body -->
			</div> <!-- /.box -->

		@endif
			
    </div>
    <div class="col-md-6">

        <!-- Categories -->
        <div class="box box-default">
            <div class="box-header with-border">
                <h2 class="box-title">{{ trans('general.asset') }} {{ trans('general.categories') }}</h2>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <x-icon type="minus" />
                        <span class="sr-only">{{ trans('general.collapse') }}</span>
                    </button>
                </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                        <table
                                data-cookie-id-table="dashCategorySummary"
                                data-height="400"
                                data-pagination="false"
                                data-side-pagination="server"
                                data-sort-order="desc"
                                data-sort-field="assets_count"
                                id="dashCategorySummary"
                                class="table table-striped snipe-table"
                                data-url="{{ route('api.categories.index', ['sort' => 'assets_count', 'order' => 'asc']) }}">
                            <thead>
                            <tr>
                                <th class="col-sm-3" data-visible="true" data-field="name" data-formatter="categoriesLinkFormatter" data-sortable="true">{{ trans('general.name') }}</th>
                                <th class="col-sm-3" data-visible="true" data-field="category_type" data-sortable="true">
                                    {{ trans('general.type') }}
                                </th>
                                <th class="col-sm-1" data-visible="true" data-field="assets_count" data-sortable="true">
                                    <x-icon type="assets" />
                                    <span class="sr-only">{{ trans('general.asset_count') }}</span>
                                </th>
                                <th class="col-sm-1" data-visible="true" data-field="accessories_count" data-sortable="true">
                                    <x-icon type="licenses" />
                                    <span class="sr-only">{{ trans('general.accessories_count') }}</span>
                                </th>
                                <th class="col-sm-1" data-visible="true" data-field="consumables_count" data-sortable="true">
                                    <x-icon type="consumables" />
                                    <span class="sr-only">{{ trans('general.consumables_count') }}</span>
                                </th>
                                <th class="col-sm-1" data-visible="true" data-field="components_count" data-sortable="true">
                                    <x-icon type="components" />
                                    <span class="sr-only">{{ trans('general.components_count') }}</span>
                                </th>
                                <th class="col-sm-1" data-visible="true" data-field="licenses_count" data-sortable="true">
                                    <x-icon type="licenses" />
                                    <span class="sr-only">{{ trans('general.licenses_count') }}</span>
                                </th>
                            </tr>
                            </thead>
                        </table>
                        </div>
                    </div> <!-- /.col -->
                    <div class="text-center col-md-12" style="padding-top: 10px;">
                        <a href="{{ route('categories.index') }}" class="btn btn-primary btn-sm" style="width: 100%">{{ trans('general.viewall') }}</a>
                    </div>
                </div> <!-- /.row -->

            </div><!-- /.box-body -->
        </div> <!-- /.box -->
    </div>


@endif


@stop

@section('moar_scripts')
@include ('partials.bootstrap-table', ['simple_view' => true, 'nopages' => true])
@stop

@push('js')


        <script src="{{ url(mix('js/dist/Chart.min.js')) }}"></script>
<script nonce="{{ csrf_token() }}">
    // ---------------------------
    // - ASSET STATUS CHART -
    // ---------------------------
      var pieChartCanvas = $("#statusPieChart").get(0).getContext("2d");
      var pieChart = new Chart(pieChartCanvas);
      var ctx = document.getElementById("statusPieChart");
      var pieOptions = {
              legend: {
                  position: 'top',
                  responsive: true,
                  maintainAspectRatio: true,
              },
              tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        counts = data.datasets[0].data;
                        total = 0;
                        for(var i in counts) {
                            total += counts[i];
                        }
                        prefix = data.labels[tooltipItem.index] || '';
                        return prefix+" "+Math.round(counts[tooltipItem.index]/total*100)+"%";
                    }
                }
              }
          };

      $.ajax({
          type: 'GET',
          url: '{{ (\App\Models\Setting::getSettings()->dash_chart_type == 'name') ? route('api.statuslabels.assets.byname') : route('api.statuslabels.assets.bytype') }}',
          headers: {
              "X-Requested-With": 'XMLHttpRequest',
              "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
          },
          dataType: 'json',
          success: function (data) {
              var myPieChart = new Chart(ctx,{
                  type   : 'pie',
                  data   : data,
                  options: pieOptions
              });
          },
          error: function (data) {
              // window.location.reload(true);
          },
      });
        var last = document.getElementById('statusPieChart').clientWidth;
        addEventListener('resize', function() {
        var current = document.getElementById('statusPieChart').clientWidth;
        if (current != last) location.reload();
        last = current;
    });
</script>
@endpush
