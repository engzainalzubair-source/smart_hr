<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    use HasFactory;

    protected $table = 'salaries';

    protected $fillable = [
        'employee_id', 'period_start', 'period_end', 'base_salary', 'adjustments', 'bonuses', 'deductions', 'net_pay', 'generated_at',
        'absent_days_without_reason', 'absent_days_with_reason', 'final_salary'
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'generated_at' => 'datetime',
        'base_salary' => 'decimal:2',
        'adjustments' => 'decimal:2',
        'bonuses' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_pay' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
