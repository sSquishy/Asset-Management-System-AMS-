@php
    // Query maintenance records aggregated by asset CATEGORY instead of per-asset.
    // We join maintenances -> assets -> models -> categories to get counts per category.
    try {
        $failureRows = \App\Models\Maintenance::select(
            'categories.id as category_id',
            'categories.name as category_name',
            \DB::raw('count(*) as failures'),
            \DB::raw('count(DISTINCT assets.id) as assets_count'),
            \DB::raw('max(start_date) as last_failure'),
        )
            ->join('assets', 'maintenances.asset_id', '=', 'assets.id')
            ->join('models', 'assets.model_id', '=', 'models.id')
            ->join('categories', 'models.category_id', '=', 'categories.id')
            ->where('categories.category_type', 'asset')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('failures')
            ->take(6)
            ->get();
    } catch (\Exception $e) {
        // If DB is not reachable or query fails, return an empty collection so the card shows a friendly message.
        $failureRows = collect();
    }
    // Build array of category details for display (keeps the same keys expected by the card)
    $failureItems = [];
    foreach ($failureRows as $fr) {
        $label = $fr->category_name ?: 'Category #' . $fr->category_id;
        $assetsCount = isset($fr->assets_count) ? (int) $fr->assets_count : 0;
        $last = $fr->last_failure ? \Carbon\Carbon::parse($fr->last_failure)->toDateString() : null;

        $failureItems[] = [
            // Card expects keys: label, model, count, last
            'label' => $label,
            // Use the `model` slot to show how many assets are in the category
            'model' => $assetsCount . ' assets',
            'count' => (int) $fr->failures,
            'last' => $last,
        ];
    }
@endphp

@include('components.assets-most-failures-card', [
    'items' => $failureItems,
    'title' => 'Categories with Most Failures',
])
