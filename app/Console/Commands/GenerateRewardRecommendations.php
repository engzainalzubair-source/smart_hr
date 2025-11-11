<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RewardPenaltyRecommendationService;
use App\Models\RewardPenalty;
use Carbon\Carbon;

class GenerateRewardRecommendations extends Command
{
    protected $signature = 'rewards:recommend {--from=} {--to=} {--apply=false}';
    protected $description = 'Generate reward/penalty recommendations using rule-based engine';

    protected $service;

    public function __construct(RewardPenaltyRecommendationService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function handle()
    {
        $from = $this->option('from');
        $to = $this->option('to');
        $apply = $this->option('apply');

        $this->info('Generating recommendations...');
        $list = $this->service->generateRecommendations($from, $to);
        $this->info('Found ' . count($list) . ' suggestions');

        foreach ($list as $s) {
            $now = Carbon::now();
            $item = RewardPenalty::create([
                'employee_id' => $s['employee_id'],
                'type' => $s['type'],
                'amount' => $s['suggested_amount'],
                'value_type' => 'amount',
                'reason' => $s['reason'],
                'issued_by' => null,
                'issued_at' => $now->toDateString(),
                'status' => RewardPenalty::STATUS_PENDING,
                'policy_rule' => 'auto_score_v1',
                'metadata' => $s['metadata'] ?? [],
            ]);
            $this->line('Created pending suggestion id=' . $item->id . ' for employee=' . $item->employee_id);
            if ($apply) {
                $item->status = RewardPenalty::STATUS_APPROVED;
                $item->save();
                $this->line('Auto-approved id=' . $item->id);
            }
        }

        $this->info('Done.');
        return 0;
    }
}
