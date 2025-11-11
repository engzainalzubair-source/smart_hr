<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'department_id','first_name','last_name','email','phone','address','job_title','hire_date','salary','status'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function performances()
    {
        return $this->hasMany(Performance::class);
    }

    public function rewardsPenalties()
    {
        return $this->hasMany(RewardPenalty::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(\App\Models\LeaveRequest::class);
    }
}
