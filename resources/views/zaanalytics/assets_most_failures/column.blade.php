@php
    $failureRows = \App\Models\Maintenance::select(
        'asset_id',
        \DB::raw('count(*) as failures'),
        \DB::raw('max(start_date) as last_failure'),
    )
        ->groupBy('asset_id')
        ->orderByDesc('failures')
        ->take(6)
        ->get();
    $failureItems = [];
    foreach ($failureRows as $fr) {
        $asset = \App\Models\Asset::with('model')->find($fr->asset_id);
        if (!$asset) {
            continue;
        }
        $failureItems[] = [
            'label' => $asset->name ?: $asset->asset_tag ?: 'Asset #' . $asset->id,
            'model' => $asset->model->name ?? ($asset->model_number ?? ''),
            'count' => (int) $fr->failures,
            'last' => $fr->last_failure ? \Carbon\Carbon::parse($fr->last_failure)->toDateString() : null,
        ];
    }
@endphp
@include('components.assets-most-failures-card', ['items' => $failureItems])