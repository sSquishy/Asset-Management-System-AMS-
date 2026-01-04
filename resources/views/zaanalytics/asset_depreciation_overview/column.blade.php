@php
    // Fetch all assets to be shown on dashboard
    $assets = \App\Models\Asset::AssetsForShow()->get();

    // Calculate total purchase cost of all assets
    $totalPurchase = $assets->sum('purchase_cost');

    // Calculate current total value after depreciation for all assets
    $currentTotal = 0;
    foreach ($assets as $a) {
        // Add each asset's depreciated value (custom method)
        $currentTotal += (float) ($a->getDepreciatedValue() ?? 0);
    }

    // Calculate total depreciation amount (purchase - current value)
    $depreciationAmount = $totalPurchase - $currentTotal;

    // Build 6-month purchase trend for sparkline chart
    $trend = [];
    for ($i = 5; $i >= 0; $i--) {
        // Get start and end of each month (from 5 months ago to now)
        $start = \Carbon\Carbon::now()->subMonths($i)->startOfMonth();
        $end = \Carbon\Carbon::now()->subMonths($i)->endOfMonth();
        // Sum purchase cost of assets bought in that month
        $sum = \App\Models\Asset::AssetsForShow()
            ->whereNotNull('purchase_date')
            ->whereBetween('purchase_date', [$start->toDateString(), $end->toDateString()])
            ->sum('purchase_cost');
        $trend[] = (float) $sum;
    }

    // Prepare metrics array for the KPI card component
    $metrics = [
        [
            // Metric 1: Total purchase cost, with 6-month trend
            'value' => \App\Helpers\Helper::formatCurrencyOutput($totalPurchase),
            'subtitle' => 'Total Purchase Cost',
            'trend' => $trend,
            'trendLabel' => '6-month purchases',
        ],
        [
            // Metric 2: Current total value after depreciation
            'value' => \App\Helpers\Helper::formatCurrencyOutput($currentTotal),
            'subtitle' => 'Current Total Value (after depreciation)',
        ],
        [
            // Metric 3: Total depreciation amount
            'value' => \App\Helpers\Helper::formatCurrencyOutput($depreciationAmount),
            'subtitle' => 'Total Depreciation Amount',
        ],
    ];
@endphp

// Render the KPI card component with metrics
@include('components.asset-depreciation-card', ['metrics' => $metrics])