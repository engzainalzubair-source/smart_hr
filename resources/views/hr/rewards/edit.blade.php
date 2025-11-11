@if(request()->ajax() || request()->query('inline'))
    <div class="p-4">
        <h2 class="text-lg font-semibold mb-4">Edit Reward / Penalty</h2>
        <form id="ajaxForm" method="POST" action="{{ route('hr.rewards.update', $item) }}">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="_method" value="PUT">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label>Employee</label>
                    <select name="employee_id" class="w-full p-2 border rounded">
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ $item->employee_id == $emp->id ? 'selected' : '' }}>{{ $emp->first_name }} {{ $emp->last_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Type</label>
                    <select name="type" class="w-full p-2 border rounded">
                        <option value="reward" {{ $item->type=='reward' ? 'selected' : '' }}>Reward</option>
                        <option value="penalty" {{ $item->type=='penalty' ? 'selected' : '' }}>Penalty</option>
                    </select>
                </div>
                <div>
                    <label>Amount</label>
                    <input type="number" step="0.01" name="amount" value="{{ $item->amount }}" class="w-full p-2 border rounded">
                </div>
                <div>
                    <label>Reason</label>
                    <input type="text" name="reason" value="{{ $item->reason }}" class="w-full p-2 border rounded">
                </div>
                <div>
                    <label>Issued At</label>
                    <input type="date" name="issued_at" value="{{ $item->issued_at }}" class="w-full p-2 border rounded">
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Save</button>
            </div>
        </form>
    </div>
@else
    @extends('layouts.app')

    @section('content')
        <div class="bg-white p-4 rounded shadow">
            <h2 class="text-lg font-semibold mb-4">Edit Reward / Penalty</h2>
            <form method="POST" action="{{ route('hr.rewards.update', $item) }}">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label>Employee</label>
                        <select name="employee_id" class="w-full p-2 border rounded">
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ $item->employee_id == $emp->id ? 'selected' : '' }}>{{ $emp->first_name }} {{ $emp->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>Type</label>
                        <select name="type" class="w-full p-2 border rounded">
                            <option value="reward" {{ $item->type=='reward' ? 'selected' : '' }}>Reward</option>
                            <option value="penalty" {{ $item->type=='penalty' ? 'selected' : '' }}>Penalty</option>
                        </select>
                    </div>
                    <div>
                        <label>Amount</label>
                        <input type="number" step="0.01" name="amount" value="{{ $item->amount }}" class="w-full p-2 border rounded">
                    </div>
                    <div>
                        <label>Reason</label>
                        <input type="text" name="reason" value="{{ $item->reason }}" class="w-full p-2 border rounded">
                    </div>
                    <div>
                        <label>Issued At</label>
                        <input type="date" name="issued_at" value="{{ $item->issued_at }}" class="w-full p-2 border rounded">
                    </div>
                </div>
                <div class="mt-4">
                    <button class="px-4 py-2 bg-blue-600 text-white rounded">Save</button>
                </div>
            </form>
        </div>
    @endsection
@endif
