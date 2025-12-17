<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SupplierReliabilityService
{
    /**
     * Compute a reliability score for a supplier (1-100) and component metrics.
     * Returns array: [score:int, rating:string, components:array]
     */
    public static function computeSupplierScore(int $supplierId): array
    {
        // Basic counts
        $totalAssets = DB::table('assets')->where('supplier_id', $supplierId)->count();

        // Repairs only
        $repairBase = DB::table('maintenances')
            ->where('supplier_id', $supplierId)
            ->where('asset_maintenance_type', 'repair');

        $totalRepairs = (int) $repairBase->count();

        // Average turnaround (days) for completed repairs
        $avgTurnaround = (float) DB::table('maintenances')
            ->where('supplier_id', $supplierId)
            ->where('asset_maintenance_type', 'repair')
            ->whereNotNull('completion_date')
            ->avg(DB::raw('DATEDIFF(completion_date, start_date)'));

        $avgTurnaround = $avgTurnaround ?: null;

        // Warranty claim success: among repairs marked is_warranty=1, % with cost null or 0 (assumed successful)
        $warrantyTotal = (int) DB::table('maintenances')
            ->where('supplier_id', $supplierId)
            ->where('asset_maintenance_type', 'repair')
            ->where('is_warranty', 1)
            ->count();

        $warrantySuccess = (int) DB::table('maintenances')
            ->where('supplier_id', $supplierId)
            ->where('asset_maintenance_type', 'repair')
            ->where('is_warranty', 1)
            ->where(function ($q) {
                $q->whereNull('cost')->orWhere('cost', 0);
            })->count();

        $warrantySuccessRate = $warrantyTotal > 0 ? ($warrantySuccess / $warrantyTotal) * 100 : null;

        // Repeat repair rate: assets (from this supplier) with more than 1 repair / total assets
        $assetsWithMultipleRepairs = DB::table('maintenances')
            ->where('supplier_id', $supplierId)
            ->where('asset_maintenance_type', 'repair')
            ->select('asset_id', DB::raw('COUNT(*) as c'))
            ->groupBy('asset_id')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();

        $repeatRepairRate = $totalAssets > 0 ? ($assetsWithMultipleRepairs / $totalAssets) * 100 : null;

        // Failure rate of assets purchased from supplier: % of supplier assets that have at least one repair
        $assetsWithAnyRepair = DB::table('maintenances')
            ->where('supplier_id', $supplierId)
            ->where('asset_maintenance_type', 'repair')
            ->select('asset_id')
            ->groupBy('asset_id')
            ->get()
            ->count();

        $failureRate = $totalAssets > 0 ? ($assetsWithAnyRepair / $totalAssets) * 100 : null;

        // Normalization helpers
        $clamp = function ($v) {
            if ($v === null) return null;
            return max(0, min(100, $v));
        };

        // 1) Turnaround: lower is better. map 1 day => 100, 30+ days => 0
        if ($avgTurnaround === null) {
            $turnNorm = null;
        } else {
            $minGood = 1.0; $maxBad = 30.0;
            $turnNorm = ($maxBad - $avgTurnaround) / ($maxBad - $minGood) * 100.0;
            $turnNorm = $clamp($turnNorm);
        }

        // 2) Repeat repair rate: lower is better -> invert percentage
        $repeatNorm = $repeatRepairRate === null ? null : $clamp(100 - $repeatRepairRate);

        // 3) Warranty success: higher is better (already 0-100)
        $warrantyNorm = $warrantySuccessRate === null ? null : $clamp($warrantySuccessRate);

        // 4) Failure rate: lower is better -> invert
        $failureNorm = $failureRate === null ? null : $clamp(100 - $failureRate);

        // Weights (example provided)
        $w_turn = 0.30; $w_repeat = 0.25; $w_warranty = 0.25; $w_failure = 0.20;

        // Replace nulls with neutral 50 for aggregation
        $replaceNeutral = function ($v) { return $v === null ? 50.0 : $v; };

        $t = $replaceNeutral($turnNorm);
        $r = $replaceNeutral($repeatNorm);
        $w = $replaceNeutral($warrantyNorm);
        $f = $replaceNeutral($failureNorm);

        $rawScore = ($t * $w_turn) + ($r * $w_repeat) + ($w * $w_warranty) + ($f * $w_failure);
        $score = (int) round(max(1, min(100, $rawScore)));

        // Rating mapping
        if ($score >= 85) {
            $rating = 'Excellent'; $color = 'success';
        } elseif ($score >= 70) {
            $rating = 'Good'; $color = 'info';
        } elseif ($score >= 50) {
            $rating = 'Fair'; $color = 'warning';
        } else {
            $rating = 'Poor'; $color = 'danger';
        }

        return [
            'score' => $score,
            'rating' => $rating,
            'color' => $color,
            'components' => [
                'avg_turnaround_days' => $avgTurnaround,
                'repeat_repair_rate_pct' => $repeatRepairRate,
                'warranty_success_pct' => $warrantySuccessRate,
                'failure_rate_pct' => $failureRate,
                'normalized' => [
                    'turnaround' => $turnNorm,
                    'repeat' => $repeatNorm,
                    'warranty' => $warrantyNorm,
                    'failure' => $failureNorm,
                ],
            ],
        ];
    }
}
