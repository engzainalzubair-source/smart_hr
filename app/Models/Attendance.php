<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = ['employee_id','date','check_in','check_out','status','notes','marked_by'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function markedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'marked_by');
    }
}
