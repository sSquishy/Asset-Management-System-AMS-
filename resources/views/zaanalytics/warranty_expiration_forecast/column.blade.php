@php
    // Retrieve all assets for dashboard analytics
    $assets = \App\Models\Asset::AssetsForShow()->get();

    // Initialize arrays for chart data and labels
    $warrantySeries = [];
    $warrantyLabels = [];

    // For each of the next 6 months, calculate how many assets will expire warranty
    for ($i = 0; $i < 6; $i++) {
        // Get start and end date for the month
        $start = \Carbon\Carbon::now()->addMonths($i)->startOfMonth();
        $end = \Carbon\Carbon::now()->addMonths($i)->endOfMonth();
        // Add month label (e.g. Jan 2026)
        $warrantyLabels[] = $start->format('M Y');
        // Count assets whose warranty expires in this month
        $count = $assets
            ->filter(function ($a) use ($start, $end) {
                // Only consider assets with purchase date and warranty months
                if (!$a->purchase_date || !$a->warranty_months) {
                    return false;
                }
                try {
                    // Calculate warranty expiry date
                    $expiry = \Carbon\Carbon::parse($a->purchase_date)->addMonths($a->warranty_months);
                } catch (\Exception $e) {
                    return false;
                }
                // Check if expiry falls within this month
                return $expiry->between($start, $end);
            })
            ->count();
        // Add count to series for chart
        $warrantySeries[] = (int) $count;
    }
@endphp

@include('components.warranty-expiration-forecast', [
    'series' => $warrantySeries,
    'labels' => $warrantyLabels,
])