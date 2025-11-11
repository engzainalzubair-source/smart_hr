<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Aggregate data across modules - placeholder
        return view('management.dashboard', [
            'total_employees' => \App\Models\Employee::count(),
            'new_employees' => \App\Models\Employee::where('hire_date', '>=', now()->subMonth())->count(),
        ]);
    }
}
