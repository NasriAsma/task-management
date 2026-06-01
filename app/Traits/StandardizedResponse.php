<?php

namespace App\Traits;

use Carbon\Carbon;

trait StandardizedResponse
{
    /**
     * Format une réponse de statistiques standardisée
     */
    protected function statsResponse(array $data, string $unit = 'items', int $statusCode = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'timestamp' => Carbon::now()->toIso8601String(),
                'unit' => $unit,
            ],
        ], $statusCode);
    }

    /**
     * Format une réponse avec taux de complétion
     */
    protected function statsWithPercentages(array $counts, array $labels = []): array
    {
        $total = array_sum($counts);
        
        if ($total === 0) {
            return array_fill_keys(array_keys($counts), 0);
        }

        $percentages = [];
        foreach ($counts as $key => $count) {
            $percentages[$key] = round(($count / $total) * 100, 2);
        }

        return $percentages;
    }

    /**
     * Format les données avec comparaison temporelle
     */
    protected function statsWithTimeline(array $data, $comparisonPeriod = 'monthly'): array
    {
        return [
            'current' => $data,
            'comparison' => [
                'period' => $comparisonPeriod,
                'note' => 'Comparison data will be calculated based on selection',
            ],
        ];
    }

    /**
     * Group statistics by period
     */
    protected function groupByPeriod($query, string $dateColumn, string $period = 'monthly')
    {
        $groupBy = match ($period) {
            'daily' => "DATE($dateColumn)",
            'weekly' => "YEAR($dateColumn), WEEK($dateColumn)",
            'monthly' => "YEAR($dateColumn), MONTH($dateColumn)",
            default => "DATE($dateColumn)",
        };

        return $query->groupByRaw($groupBy);
    }
}
