<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\RewardPenalty;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Services\RewardPenaltyRecommendationService;
use App\Notifications\NewRewardPenaltyNotification;
use Illuminate\Support\Facades\Notification;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class RewardPenaltyController extends Controller
{
    public function index()
    {
        $query = RewardPenalty::with(['employee','employee.department']);

        // filters
        if ($q = request()->get('q')) {
            $query->whereHas('employee', function ($q2) use ($q) {
                $q2->whereRaw("CONCAT(first_name,' ',last_name) LIKE ?", ["%{$q}%"])->orWhere('email','like',"%{$q}%");
            });
        }
        if ($type = request()->get('type')) {
            $query->where('type', $type);
        }
        if ($from = request()->get('from')) {
            $query->whereDate('issued_at', '>=', $from);
        }
        if ($to = request()->get('to')) {
            $query->whereDate('issued_at', '<=', $to);
        }
        if ($dept = request()->get('department')) {
            $query->whereHas('employee.department', function ($q3) use ($dept) {
                $q3->where('id', $dept);
            });
        }

        // compute aggregates for inline analytics (respect current filters)
        $totals = (clone $query)->selectRaw("type, SUM(COALESCE(amount,0)) as total, COUNT(*) as count")->groupBy('type')->get()->keyBy('type');

        $byDept = (clone $query)
            ->join('employees','rewards_penalties.employee_id','=','employees.id')
            ->join('departments','employees.department_id','=','departments.id')
            ->selectRaw('departments.name as department, rewards_penalties.type, SUM(COALESCE(rewards_penalties.amount,0)) as total')
            ->groupBy('departments.name','rewards_penalties.type')
            ->get();

        $items = $query->latest()->paginate(20)->withQueryString();

        $departments = \App\Models\Department::orderBy('name')->get();


        if (request()->ajax()) {
            return view('hr.partials.rewards_table', compact('items','departments','totals','byDept'));
        }
        return view('hr.rewards.index', compact('items','departments','totals','byDept'));
    }

    public function create()
    {
        $employees = Employee::orderBy('first_name')->get();
        return view('hr.rewards.create', compact('employees'));
    }

    /**
     * Display the specified resource.
     * For now redirect to the edit form to avoid creating a separate show view.
     */
    public function show(RewardPenalty $reward)
    {
        return redirect()->route('hr.rewards.edit', $reward->id);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'type' => 'required|in:reward,penalty',
            'amount' => 'nullable|numeric',
            'reason' => 'nullable|string',
            'issued_at' => 'nullable|date',
        ]);
        RewardPenalty::create($data);
        return redirect()->route('hr.rewards.index');
    }

    public function edit(RewardPenalty $reward)
    {
        $employees = Employee::orderBy('first_name')->get();
        return view('hr.rewards.edit', ['item' => $reward, 'employees' => $employees]);
    }

    public function update(Request $request, RewardPenalty $reward)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'type' => 'required|in:reward,penalty',
            'amount' => 'nullable|numeric',
            'reason' => 'nullable|string',
            'issued_at' => 'nullable|date',
        ]);
        $reward->update($data);
        return redirect()->route('hr.rewards.index');
    }

    public function destroy(RewardPenalty $reward)
    {
        $reward->delete();
        return redirect()->route('hr.rewards.index');
    }

    // Analytics dashboard
    public function analytics(RewardPenaltyRecommendationService $svc)
    {
        // simple aggregates for the view
        $totals = RewardPenalty::selectRaw("type, SUM(COALESCE(amount,0)) as total, COUNT(*) as count")
            ->groupBy('type')->get()->keyBy('type');

        $byDept = RewardPenalty::join('employees','rewards_penalties.employee_id','=','employees.id')
            ->join('departments','employees.department_id','=','departments.id')
            ->selectRaw('departments.name as department, rewards_penalties.type, SUM(COALESCE(rewards_penalties.amount,0)) as total')
            ->groupBy('departments.name','rewards_penalties.type')
            ->get();

        return view('hr.rewards.analytics', compact('totals','byDept'));
    }

    /**
     * Export the current filtered rewards/penalties list as PDF.
     */
    public function exportPdf(Request $request)
    {
        $q = $request->query('q');
        $type = $request->query('type');
        $from = $request->query('from');
        $to = $request->query('to');
        $dept = $request->query('department');

        $query = RewardPenalty::with(['employee','employee.department']);
        if ($q) {
            $query->whereHas('employee', function ($q2) use ($q) {
                $q2->whereRaw("CONCAT(first_name,' ',last_name) LIKE ?", ["%{$q}%"])->orWhere('email','like',"%{$q}%");
            });
        }
        if ($type) {
            $query->where('type', $type);
        }
        if ($from) {
            $query->whereDate('issued_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('issued_at', '<=', $to);
        }
        if ($dept) {
            $query->whereHas('employee.department', function ($q3) use ($dept) {
                $q3->where('id', $dept);
            });
        }

    // qualify the created_at column to avoid ambiguous column errors when joins are used later
    $items = $query->latest('rewards_penalties.created_at')->get();

        $totals = (clone $query)->selectRaw("type, SUM(COALESCE(amount,0)) as total, COUNT(*) as count")->groupBy('type')->get()->keyBy('type');

        $byDept = (clone $query)
            ->join('employees','rewards_penalties.employee_id','=','employees.id')
            ->join('departments','employees.department_id','=','departments.id')
            ->selectRaw('departments.name as department, rewards_penalties.type, SUM(COALESCE(rewards_penalties.amount,0)) as total')
            ->groupBy('departments.name','rewards_penalties.type')
            ->get();

        $filters = ['q'=>$q,'type'=>$type,'from'=>$from,'to'=>$to,'department'=>$dept];

        $pdf = PDF::loadView('hr.reports.rewards_pdf', compact('items','totals','byDept','filters'))
            ->setPaper('a4','landscape');

        return $pdf->stream('rewards_report.pdf');
    }

    // Show pending auto recommendations (or run on demand)
    public function recommendations(RewardPenaltyRecommendationService $svc)
    {
        try {
            $suggestions = $svc->generateRecommendations();
        } catch (\Exception $e) {
            if (request()->ajax()) {
                $msg = app()->environment('production') ? 'حدث خطأ أثناء توليد التوصيات' : $e->getMessage();
                return response()->make('<div class="text-sm text-red-600">' . e($msg) . '</div>', 500);
            }
            return back()->with('error', app()->environment('production') ? 'Failed to generate recommendations' : $e->getMessage());
        }

        if (request()->ajax()) {
            try {
                return view('hr.partials.recommendations_list', compact('suggestions'));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Recommendations render error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
                $msg = app()->environment('production') ? 'خطأ أثناء عرض التوصيات' : $e->getMessage();
                return response()->make('<div class="text-sm text-red-600">' . e($msg) . '</div>', 500);
            }
        }

        try {
            return view('hr.rewards.recommendations', compact('suggestions'));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Recommendations page render error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', app()->environment('production') ? 'خطأ أثناء عرض صفحة التوصيات' : $e->getMessage());
        }
    }

    // Approve a pending suggestion or item
    public function approve(Request $request, RewardPenalty $reward)
    {
        // only allow transition if pending
        if ($reward->status !== RewardPenalty::STATUS_PENDING) {
            return back()->with('error','Item is not pending');
        }
        $reward->status = RewardPenalty::STATUS_APPROVED;
        $reward->issued_by = $request->user() ? $request->user()->id : null;
        $reward->save();

        // notify employee
        try {
            $reward->employee->notify(new NewRewardPenaltyNotification($reward));
        } catch (\Exception $e) {
            // swallow notification errors
        }

        return back()->with('success','Approved');
    }
}
