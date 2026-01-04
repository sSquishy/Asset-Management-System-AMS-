@php
    $assets = \App\Models\Asset::AssetsForShow()->get();
    $warrantySoon = collect();
    $thresholdDays = 60; // consider "nearing" as within next 60 days
    foreach ($assets as $a) {
        if (!$a->purchase_date || empty($a->warranty_months)) {
            continue;
        }
        try {
            $expiry = \Carbon\Carbon::parse($a->purchase_date)->addMonths($a->warranty_months);
        } catch (\Exception $e) {
            continue;
        }
        $now = \Carbon\Carbon::now();
        if ($expiry->lt($now)) {
            continue;
        } // already expired
        $daysRemaining = $now->diffInDays($expiry);
        if ($daysRemaining <= $thresholdDays) {
            $assigned =
                optional($a->assigned_user)->name ?:
                optional($a->assigned_to)->name ?:
                optional($a->user)->name ?:
                '';
            $department = optional($a->location)->name ?: optional($a->department)->name ?: '';
            $supplier = optional($a->supplier)->name ?: '';
            $warrantySoon->push([
                'asset_tag' => $a->asset_tag ?: '',
                'name' => $a->name ?: 'Asset #' . $a->id,
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
            <table id="dashWarrantySoon" class="table table-striped snipe-table" data-toggle="table"
                data-fixed-table-toolbar="true" data-cookie-id-table="dashWarrantySoon" data-height="500"
                data-search="true" data-pagination="false">
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
                    @if ($warrantySoon->isEmpty())
                        <tr>
                            <td colspan="7" class="text-muted">No assets nearing warranty expiration.</td>
                        </tr>
                    @else
                        @foreach ($warrantySoon as $w)
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
            <a href="{{ route('hardware.index') }}" class="btn btn-primary btn-sm"
                style="width: 100%">{{ trans('general.viewall') }}</a>
        </div>
    </div>
</div>
