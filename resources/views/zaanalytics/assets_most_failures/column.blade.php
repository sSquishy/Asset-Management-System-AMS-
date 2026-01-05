@php
    // Query maintenance records to get failure counts and last failure date per asset
    $failureRows = \App\Models\Maintenance::select(
        'asset_id',
        \DB::raw('count(*) as failures'), // Count number of failures per asset
        \DB::raw('max(start_date) as last_failure'), // Most recent failure date
    )
        ->groupBy('asset_id')
        ->orderByDesc('failures') // Order by most failures
        ->take(6) // Limit to top 6 assets
        ->get();

    // Build array of asset details for display
    $failureItems = [];
    foreach ($failureRows as $fr) {
        // Fetch asset and its model
        $asset = \App\Models\Asset::with('model')->find($fr->asset_id);
        if (!$asset) {
            continue;
        }
        // Prepare display data for each asset
        $failureItems[] = [
            'label' => $asset->name ?: $asset->asset_tag ?: 'Asset #' . $asset->id, // Asset name or tag
            'model' => $asset->model->name ?? ($asset->model_number ?? ''), // Model name or number
            'count' => (int) $fr->failures, // Number of failures
            'last' => $fr->last_failure ? \Carbon\Carbon::parse($fr->last_failure)->toDateString() : null, // Last failure date
        ];
    }
@endphp

@include('components.assets-most-failures-card', ['items' => $failureItems])