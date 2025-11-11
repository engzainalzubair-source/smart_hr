<?php

namespace App\Services;

use App\Models\Performance;
use App\Models\Attendance;
use Carbon\Carbon;

class PerformanceRecommendationService
{
    /**
     * Generate actionable recommendations based on composites array.
     * Expected $composites: array of ['employee_id','name','perfAvg','attendanceRate','composite']
     * Returns array of suggestions with keys: employee_id, name, issue, suggestion, confidence
     */
    public function generate(array $composites, array $options = []) : array
    {
        $days = $options['days'] ?? 90;
        $suggestions = [];
        foreach ($composites as $c) {
            $empId = $c['employee_id'] ?? null;
            $composite = $c['composite'] ?? 0;
            $perf = $c['perfAvg'] ?? 0;
            $att = $c['attendanceRate'] ?? 0;

            $issue = null;
            $suggestion = null;
            $confidence = 0.5;

            // Compute a simple trend: compare recent average to previous window
            $trend = null;
            try {
                if ($empId) {
                    $startRecent = Carbon::now()->subDays($days - 1)->startOfDay();
                    $startPrev = Carbon::now()->subDays(2 * $days - 1)->startOfDay();
                    $endPrev = Carbon::now()->subDays($days)->endOfDay();

                    $recentAvg = Performance::where('employee_id', $empId)
                        ->where('period_start', '>=', $startRecent)
                        ->avg('score') ?? 0;
                    $prevAvg = Performance::where('employee_id', $empId)
                        ->whereBetween('period_start', [$startPrev, $endPrev])
                        ->avg('score') ?? 0;
                    $trend = round($recentAvg - $prevAvg, 1); // positive = improving
                }
            } catch (\Throwable $e) {
                $trend = null;
            }

            // Decision rules (more granular):
            if ($composite >= 88) {
                $issue = 'High performer';
                $suggestion = "{$c['name']} is a high performer ({$composite}%). Recommend stretch assignments, leadership coaching, or promotion track.";
                $confidence = 0.9 + (($composite - 88) / 40);
            } elseif (!is_null($trend) && $trend <= -5) {
                $issue = 'Declining performance';
                $suggestion = "Recent performance has dropped by {$trend} points. Schedule a performance review and personalised coaching.";
                $confidence = 0.9;
            } elseif ($perf < 60 && $att >= 75) {
                $issue = 'Skill gap';
                $suggestion = "Avg score {$perf} with good attendance ({$att}%). Recommend targeted technical training and mentoring.";
                $confidence = 0.8;
            } elseif ($att < 65) {
                $issue = 'Attendance concern';
                $suggestion = "Attendance rate is low ({$att}%). HR should investigate causes and consider attendance improvement plan.";
                $confidence = 0.82;
            } elseif ($composite >= 72) {
                $issue = 'Solid performer';
                $suggestion = "Composite {$composite}%. Recommend advanced upskilling (leadership/time management) to prepare for next role.";
                $confidence = 0.65;
            } else {
                $issue = 'Needs improvement';
                $suggestion = "Composite {$composite}%. Recommend foundational training, coaching and monthly check-ins to monitor progress.";
                $confidence = 0.6;
            }

            // Slightly boost confidence if trend positive and recommendation is promotion/upskill
            if (!is_null($trend) && $trend >= 4 && in_array($issue, ['High performer','Solid performer'])) {
                $confidence = min(0.98, $confidence + 0.08);
            }

            $suggestions[] = [
                'employee_id' => $empId,
                'name' => $c['name'] ?? 'Unknown',
                'issue' => $issue,
                'suggestion' => $suggestion,
                'confidence' => round(min(0.99, max(0.4, $confidence)) * 100), // percentage
                'perfAvg' => $perf,
                'attendanceRate' => $att,
                'composite' => $composite,
                'trend' => $trend,
            ];
        }

        // sort suggestions by composite ascending (low performers first)
        usort($suggestions, function($a,$b){ return $a['composite'] <=> $b['composite']; });

        return $suggestions;
    }
}
