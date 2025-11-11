<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Performance extends Model
{
    use HasFactory;

    protected $fillable = ['employee_id','period_start','period_end','score','notes'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
