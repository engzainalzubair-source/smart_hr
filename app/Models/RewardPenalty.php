<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RewardPenalty extends Model
{
    use HasFactory;

    protected $table = 'rewards_penalties';
    protected $fillable = ['employee_id','type','amount','value_type','reason','issued_by','issued_at','status','policy_rule','metadata','applied_to_payroll'];

    protected $casts = [
        'issued_at' => 'date',
        'metadata' => 'array',
        'amount' => 'decimal:2',
        'applied_to_payroll' => 'boolean',
    ];

    // helper constants
    const TYPE_REWARD = 'reward';
    const TYPE_PENALTY = 'penalty';

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_APPLIED = 'applied';

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function issuer()
    {
        return $this->belongsTo(\App\Models\User::class, 'issued_by');
    }
}
