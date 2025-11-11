<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class AttendanceController extends Controller
{
    public function index()
    {
        $date = request()->get('date', now()->toDateString());
        $q = request()->get('q');
        $departmentId = request()->get('department_id');
        $perPage = (int) request()->get('per_page', 50);

        // recent attendance records (history)
        $attendances = Attendance::with('employee')
            ->when($date, function($qry) use ($date){ $qry->whereDate('date', $date); })
            ->latest()->paginate(20)->appends(request()->query());

        // For switches UI we need a paginated employees list (scales better) and present ids for the selected date
        $employeesQuery = Employee::orderBy('first_name')
            ->when($departmentId, function($q2) use ($departmentId){ $q2->where('department_id', $departmentId); })
            ->when($q, function($q2) use ($q){ $q2->whereRaw("concat(first_name,' ',last_name) like ?", ["%{$q}%"]); });

        $employees = $employeesQuery->paginate($perPage)->appends(request()->query());

    $todayPresentIds = Attendance::whereDate('date', $date)->where('status', 'present')->pluck('employee_id')->toArray();

    // also load full attendance rows for the date to prefill check-in/check-out in the UI
    $todayAttendances = Attendance::whereDate('date', $date)->get()->keyBy('employee_id');

        if (request()->ajax()) {
            return view('hr.partials.attendances_table', compact('attendances','employees','todayPresentIds','todayAttendances','date'));
        }
        return view('hr.attendances.index', compact('attendances','employees','todayPresentIds','todayAttendances','date'));
    }

    public function create()
    {
        $employees = Employee::orderBy('first_name')->get();
        return view('hr.attendances.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'check_in' => 'nullable',
            'check_out' => 'nullable',
            'status' => 'required',
        ]);
        Attendance::create($data);
        return redirect()->route('hr.attendances.index');
    }

    public function edit(Attendance $attendance)
    {
        $employees = Employee::orderBy('first_name')->get();
        return view('hr.attendances.edit', compact('attendance','employees'));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'check_in' => 'nullable',
            'check_out' => 'nullable',
            'status' => 'required',
        ]);
        $attendance->update($data);
        return redirect()->route('hr.attendances.index');
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();
        return redirect()->route('hr.attendances.index');
    }

    /**
     * Show attendance entry - kept for route compatibility (redirects to edit).
     */
    public function show(Attendance $attendance)
    {
        return redirect()->route('hr.attendances.edit', $attendance);
    }

    /**
     * Mark attendance for an employee for a given date (defaults to today).
     * Accepts employee_id and status (present|absent|leave|late).
     * Creates or updates the attendance record.
     */
    public function mark(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'status' => 'required|in:present,absent,leave,late',
            'date' => 'nullable|date',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
        ]);

        $date = $data['date'] ?? now()->toDateString();

        // If marking present => create or update today's attendance; if marking absent => set status to absent (do not delete)
        if ($data['status'] === 'present') {
            $attendanceData = [
                'status' => 'present',
                'check_in' => isset($data['check_in']) ? $data['check_in'] . ':00' : now()->format('H:i:s'),
                'check_out' => isset($data['check_out']) ? $data['check_out'] . ':00' : null,
                'marked_by' => auth()->id(),
            ];
            $attendance = Attendance::updateOrCreate(
                ['employee_id' => $data['employee_id'], 'date' => $date],
                $attendanceData
            );
            $result = ['success' => true, 'action' => 'marked_present', 'attendance' => $attendance];
        } else {
            $attendanceData = [
                'status' => $data['status'],
                'check_in' => isset($data['check_in']) ? $data['check_in'] . ':00' : null,
                'check_out' => isset($data['check_out']) ? $data['check_out'] . ':00' : null,
                'marked_by' => auth()->id(),
            ];
            $attendance = Attendance::updateOrCreate(
                ['employee_id' => $data['employee_id'], 'date' => $date],
                $attendanceData
            );
            $result = ['success' => true, 'action' => 'marked_absent', 'attendance' => $attendance];
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->back();
    }

    /**
     * Bulk mark attendance for multiple employees.
     * Accepts employee_ids[] (array), status and date (optional).
     */
    public function bulkMark(Request $request)
    {
        $data = $request->validate([
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'required|exists:employees,id',
            'status' => 'required|in:present,absent,leave,late',
            'date' => 'nullable|date',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
        ]);

        $date = $data['date'] ?? now()->toDateString();
        $results = [];
        foreach ($data['employee_ids'] as $empId) {
            if ($data['status'] === 'present') {
                $attendance = Attendance::updateOrCreate(
                    ['employee_id' => $empId, 'date' => $date],
                    [
                        'status' => 'present',
                        'check_in' => isset($data['check_in']) ? $data['check_in'] . ':00' : now()->format('H:i:s'),
                        'check_out' => isset($data['check_out']) ? $data['check_out'] . ':00' : null,
                        'marked_by' => auth()->id(),
                    ]
                );
            } else {
                $attendance = Attendance::updateOrCreate(
                    ['employee_id' => $empId, 'date' => $date],
                    [
                        'status' => $data['status'],
                        'check_in' => isset($data['check_in']) ? $data['check_in'] . ':00' : null,
                        'check_out' => isset($data['check_out']) ? $data['check_out'] . ':00' : null,
                        'marked_by' => auth()->id(),
                    ]
                );
            }
            $results[] = $attendance->id;
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'marked' => $results]);
        }
        return redirect()->back()->with('success', 'Marked attendance for ' . count($results) . ' employees');
    }

    /**
     * Export attendance for a date (or range) as PDF.
     */
    public function exportPdf(Request $request)
    {
        $date = $request->query('date', now()->toDateString());
        $departmentId = $request->query('department_id');
        $q = $request->query('q');

        $query = Attendance::with(['employee','employee.department'])->when($date, function($q2) use ($date){
            $q2->whereDate('date', $date);
        });
        if ($departmentId) {
            $query->whereHas('employee', function($q3) use ($departmentId){ $q3->where('department_id', $departmentId); });
        }
        if ($q) {
            $query->whereHas('employee', function($q4) use ($q){ $q4->whereRaw("concat(first_name,' ',last_name) like ?", ["%{$q}%"]); });
        }

        $items = $query->orderBy('date','desc')->get();

        $filters = ['date' => $date, 'department_id' => $departmentId, 'q' => $q];

        $pdf = PDF::loadView('hr.reports.attendance_pdf', compact('items','filters'))
            ->setPaper('a4','landscape');
        return $pdf->stream('attendance_report.pdf');
    }
}
