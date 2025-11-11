@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-gray-100">
    @include('hr.partials.sidebar')

    <div class="flex-1 flex flex-col">
        <!-- MAIN AREA: content provided by HR pages -->
        <main class="flex-1 overflow-y-auto p-8 space-y-8" id="mainContent">
            @yield('hr-content')
        </main>
    </div>
</div>
@endsection
