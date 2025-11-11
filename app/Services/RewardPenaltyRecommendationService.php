<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\RewardPenalty;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RewardPenaltyRecommendationService
{
    protected $config;

    public function __construct()
    {
        $this->config = config('rewards', []);
    }

    /**
     * Generate simple rule-based recommendations for a given period.
     * Returns array of suggestions: [ ['employee_id'=>, 'score'=>, 'type'=>, 'suggested_amount'=>, 'metadata'=>[], 'reason'=>], ... ]
     */
    public function generateRecommendations($from = null, $to = null)
    {
        $from = $from ? Carbon::parse($from)->startOfDay() : Carbon::now()->subMonth()->startOfDay();
        $to = $to ? Carbon::parse($to)->endOfDay() : Carbon::now()->endOfDay();

        $employees = Employee::with(['department'])->get();
        $suggestions = [];

        foreach ($employees as $e) {
            // compute simple features
            $attendancePercent = $this->attendancePercent($e->id, $from, $to);
            $perfAvg = $this->performanceAvg($e->id, $from, $to); // 0-100
            $warnings = $this->warningsCount($e->id, $from, $to);
            $recentPenaltyTotal = $this->recentPenaltyTotal($e->id, $from, $to);

            $components = [];
            $dataGaps = 0;

            // attendance component (weighted)
            if ($attendancePercent === null) {
                $dataGaps++;
                $components['attendance'] = null;
            } else {
                // normalize to -50..+50 around 0.9
                $attScore = ($attendancePercent - 0.9) * 100; // e.g. 0.95 -> +5
                $components['attendance'] = round($attendancePercent, 2);
            }

            // performance component
            if ($perfAvg === null) {
                $dataGaps++;
                $components['performance'] = null;
            } else {
                $components['performance'] = round($perfAvg, 1);
            }

            $components['warnings'] = $warnings;
            $components['recent_penalty_total'] = round($recentPenaltyTotal, 2);

            // score composition
            $score = 0;
            if ($attendancePercent !== null) {
                // reward reasonable attendance > 0.92
                if ($attendancePercent >= 0.95) $score += 25;
                elseif ($attendancePercent >= 0.92) $score += 12;
                elseif ($attendancePercent >= 0.88) $score += 4;
                else $score -= 18; // low attendance penalized
            }

            if ($perfAvg !== null) {
                if ($perfAvg >= 95) $score += 45;
                elseif ($perfAvg >= 90) $score += 30;
                elseif ($perfAvg >= 80) $score += 12;
                elseif ($perfAvg >= 70) $score += 0;
                else $score -= 12;
            }

            if ($warnings > 0) {
                $score -= min(40, $warnings * 12);
            }

            if ($recentPenaltyTotal > 0) {
                // scale down score by a fraction of penalty relative to salary
                $salary = $e->salary ?? 1000;
                $ratio = $recentPenaltyTotal / max(1, $salary);
                $score -= min(30, $ratio * 100);
            }

            // compute confidence: depends on data availability and magnitude of score
            $availabilityFactor = max(0.2, 1 - ($dataGaps * 0.25));
            $rawConfidence = min(0.95, 0.4 + (abs($score) / 120));
            $confidence = round($rawConfidence * $availabilityFactor, 2);

            // decide suggestion
            $suggestion = null;
            if ($score >= 30) {
                $amt = $this->suggestAmountForReward($e, $score);
                $suggestion = [
                    'employee_id' => $e->id,
                    'type' => RewardPenalty::TYPE_REWARD,
                    'score' => round($score,1),
                    'confidence' => $confidence,
                    'suggested_amount' => $amt,
                    'suggested_action' => 'Bonus/Recognition',
                    'rationale' => $this->buildRationale($components),
                    'metadata' => $components,
                    'reason' => 'Auto: positive performance and attendance',
                ];
            } elseif ($score <= -25) {
                $amt = $this->suggestAmountForPenalty($e, $score);
                $suggestion = [
                    'employee_id' => $e->id,
                    'type' => RewardPenalty::TYPE_PENALTY,
                    'score' => round($score,1),
                    'confidence' => $confidence,
                    'suggested_amount' => $amt,
                    'suggested_action' => 'Investigation / Deduction',
                    'rationale' => $this->buildRationale($components),
                    'metadata' => $components,
                    'reason' => 'Auto: concerning attendance/performance indicators',
                ];
            }

            if ($suggestion) {
                $suggestions[] = $suggestion;
            }
        }

        // sort suggestions by confidence desc then absolute score desc
        usort($suggestions, function ($a, $b) {
            $ca = ($a['confidence'] ?? 0) * abs($a['score'] ?? 0);
            $cb = ($b['confidence'] ?? 0) * abs($b['score'] ?? 0);
            return $cb <=> $ca;
        });

        return $suggestions;
    }

    protected function recentPenaltyTotal($employeeId, $from, $to)
    {
        return DB::table('rewards_penalties')->where('employee_id', $employeeId)
            ->where('type', 'penalty')
            ->whereBetween('issued_at', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');
    }

    protected function buildRationale(array $components)
    {
        $parts = [];
        if (isset($components['attendance'])) {
            $parts[] = $components['attendance'] === null ? 'Attendance data: missing' : ('Attendance: ' . ($components['attendance'] * 100) . '%');
        }
        if (isset($components['performance'])) {
            $parts[] = $components['performance'] === null ? 'Performance data: missing' : ('Performance avg: ' . $components['performance']);
        }
        if (!empty($components['warnings'])) {
            $parts[] = 'Warnings: ' . $components['warnings'];
        }
        if (!empty($components['recent_penalty_total'])) {
            $parts[] = 'Recent penalties total: ' . number_format($components['recent_penalty_total'],2);
        }
        return $parts;
    }

    protected function attendancePercent($employeeId, $from, $to)
    {
        // attempt to compute attendance percent as present / expected
        $present = DB::table('attendances')->where('employee_id', $employeeId)
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->where('status', 'present')->count();
        $expected = DB::table('attendances')->where('employee_id', $employeeId)
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])->count();

        if ($expected == 0) return null;
        return round($present / $expected, 2);
    }

    protected function performanceAvg($employeeId, $from, $to)
    {
        $row = DB::table('performances')->where('employee_id', $employeeId)
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->avg('score');
        return $row ? round($row, 1) : null;
    }

    protected function warningsCount($employeeId, $from, $to)
    {
        // If you have a warnings table, use it; otherwise count penalties as warnings proxy
        $count = DB::table('rewards_penalties')->where('employee_id', $employeeId)
            ->where('type', 'penalty')
            ->whereBetween('issued_at', [$from->toDateString(), $to->toDateString()])
            ->count();
        return $count;
    }

    protected function suggestAmountForReward(Employee $e, $score)
    {
        // base amount heuristics: based on salary if available
        $base = $e->salary ?? 1000;
        $mult = min(0.2, $score / 200); // cap at 20%
        return round($base * $mult, 2);
    }

    protected function suggestAmountForPenalty(Employee $e, $score)
    {
        $base = $e->salary ?? 1000;
        $mult = min(0.15, abs($score) / 200); // cap at 15%
        return round($base * $mult, 2);
    }
}
