@extends('layouts.app')

@section('title', 'Recorded attendance')

@section('content')
<div class="container-fluid">
    <h1 class="h3">Recorded attendance</h1>
    <p>Review marks saved for activity sessions. Unmarked opportunities do not appear in this report.</p>
    <a href="{{ route('centre.attendance.index', ['centre_id' => $centre?->centre_id]) }}" class="btn btn-outline-secondary mb-3">Back to attendance</a>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="GET" action="{{ route('centre.attendance.analytics') }}" class="row g-3 align-items-end mb-4">
        @if (session('role') === 'admin')
            <div class="col-md-4">
                <label for="report-centre" class="form-label">Centre</label>
                <select id="report-centre" name="centre_id" class="form-control" required>
                    <option value="">Choose a centre</option>
                    @foreach ($centres as $option)
                        <option value="{{ $option->centre_id }}" @selected($centre?->centre_id === $option->centre_id)>{{ $option->centre_name }}</option>
                    @endforeach
                </select>
            </div>
        @else
            <div class="col-md-4"><strong>{{ $centre?->centre_name }}</strong></div>
        @endif
        <div class="col-md-3">
            <label for="report-start" class="form-label">Start date</label>
            <input id="report-start" name="start_date" type="date" value="{{ old('start_date', $startDate) }}" class="form-control" required>
        </div>
        <div class="col-md-3">
            <label for="report-end" class="form-label">End date</label>
            <input id="report-end" name="end_date" type="date" value="{{ old('end_date', $endDate) }}" class="form-control" required>
        </div>
        <div class="col-md-2"><button type="submit" class="btn btn-primary">Apply filters</button></div>
    </form>

    @if ($marks !== null)
        <p>{{ $marks->total() }} recorded {{ $marks->total() === 1 ? 'mark' : 'marks' }}.</p>
        <div class="mb-3">
            <a href="{{ route('centre.attendance.export', ['centre_id' => $centre->centre_id, 'start_date' => $startDate, 'end_date' => $endDate, 'format' => 'csv']) }}" class="btn btn-outline-success">Download CSV</a>
            <a href="{{ route('centre.attendance.export', ['centre_id' => $centre->centre_id, 'start_date' => $startDate, 'end_date' => $endDate, 'format' => 'pdf']) }}" class="btn btn-outline-secondary">Download PDF</a>
        </div>
        <div class="table-responsive" tabindex="0" aria-label="Attendance report table">
            @include('centre.attendance.report-table')
        </div>
        {{ $marks->links() }}
    @else
        <p>Select a centre to view recorded attendance.</p>
    @endif
</div>
@endsection
