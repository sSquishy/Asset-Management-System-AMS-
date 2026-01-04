@php
    $vendorRows = \App\Models\Maintenance::select(
        'supplier_id',
        \DB::raw('count(*) as jobs'),
        \DB::raw('coalesce(sum(cost),0) as total_cost'),
        \DB::raw(
            'avg(case when completion_date is not null and start_date is not null then datediff(completion_date,start_date) end) as avg_duration',
        ),
    )
        ->whereNotNull('supplier_id')
        ->groupBy('supplier_id')
        ->get();

    $vendorIds = $vendorRows->pluck('supplier_id')->unique()->filter()->values()->all();
    $vendorsMap = \App\Models\Supplier::whereIn('id', $vendorIds)->get()->keyBy('id');

    $vendorItems = collect();
    foreach ($vendorRows as $vr) {
        $s = $vendorsMap->get($vr->supplier_id);
        if (!$s) {
            continue;
        }
        $vendorItems->push([
            'id' => $vr->supplier_id,
            'name' => $s->name ?: 'Supplier #' . $vr->supplier_id,
            'jobs' => (int) $vr->jobs,
            'total_cost' => (float) $vr->total_cost,
            'total_cost_formatted' => \App\Helpers\Helper::formatCurrencyOutput($vr->total_cost),
            'avg_duration' => $vr->avg_duration !== null ? round($vr->avg_duration, 1) : null,
        ]);
    }
    $vendorItems = $vendorItems->sortByDesc('total_cost')->values()->take(8);
@endphp
<div class="box box-default">
    <div class="box-header with-border">
        <h2 class="box-title" style="font-weight:700">Vendors with Most Maintenance or Costs</h2>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse" aria-hidden="true">
                <x-icon type="minus" />
                <span class="sr-only">{{ trans('general.collapse') }}</span>
            </button>
        </div>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-striped snipe-table">
                <thead>
                    <tr>
                        <th>Vendor</th>
                        <th>Jobs</th>
                        <th>Total Cost</th>
                        <th>Avg Duration (days)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vendorItems as $v)
                        <tr>
                            <td>{{ $v['name'] }}</td>
                            <td>{{ $v['jobs'] }}</td>
                            <td>{{ $v['total_cost_formatted'] }}</td>
                            <td>{{ $v['avg_duration'] !== null ? $v['avg_duration'] : '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-muted">No vendor maintenance data available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
