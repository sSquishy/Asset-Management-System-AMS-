@php
    $assets = \App\Models\Asset::AssetsForShow()->get();
    $warrantySeries = [];
    $warrantyLabels = [];
    for ($i = 0; $i < 6; $i++) {
        $start = \Carbon\Carbon::now()->addMonths($i)->startOfMonth();
        $end = \Carbon\Carbon::now()->addMonths($i)->endOfMonth();
        $warrantyLabels[] = $start->format('M Y');
        $count = $assets
            ->filter(function ($a) use ($start, $end) {
                if (!$a->purchase_date || !$a->warranty_months) {
                    return false;
                }
                try {
                    $expiry = \Carbon\Carbon::parse($a->purchase_date)->addMonths($a->warranty_months);
                } catch (\Exception $e) {
                    return false;
                }
                return $expiry->between($start, $end);
            })
            ->count();
        $warrantySeries[] = (int) $count;
    }
@endphp
@include('components.warranty-expiration-forecast', [
    'series' => $warrantySeries,
    'labels' => $warrantyLabels,
])