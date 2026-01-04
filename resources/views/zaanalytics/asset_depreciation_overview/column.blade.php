@php
    $assets = \App\Models\Asset::AssetsForShow()->get();
    $totalPurchase = $assets->sum('purchase_cost');
    $currentTotal = 0;
    foreach ($assets as $a) {
        $currentTotal += (float) ($a->getDepreciatedValue() ?? 0);
    }
    $depreciationAmount = $totalPurchase - $currentTotal;
    $trend = [];
    for ($i = 5; $i >= 0; $i--) {
        $start = \Carbon\Carbon::now()->subMonths($i)->startOfMonth();
        $end = \Carbon\Carbon::now()->subMonths($i)->endOfMonth();
        $sum = \App\Models\Asset::AssetsForShow()
            ->whereNotNull('purchase_date')
            ->whereBetween('purchase_date', [$start->toDateString(), $end->toDateString()])
            ->sum('purchase_cost');
        $trend[] = (float) $sum;
    }
    $metrics = [
        [
            'value' => \App\Helpers\Helper::formatCurrencyOutput($totalPurchase),
            'subtitle' => 'Total Purchase Cost',
            'trend' => $trend,
            'trendLabel' => '6-month purchases',
        ],
        [
            'value' => \App\Helpers\Helper::formatCurrencyOutput($currentTotal),
            'subtitle' => 'Current Total Value (after depreciation)',
        ],
        [
            'value' => \App\Helpers\Helper::formatCurrencyOutput($depreciationAmount),
            'subtitle' => 'Total Depreciation Amount',
        ],
    ];
@endphp
@include('components.asset-depreciation-card', ['metrics' => $metrics])