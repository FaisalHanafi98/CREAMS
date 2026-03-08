@extends('layouts.app')

@section('title', 'Schedule - CREAMS')

@section('content')
<div class="dashboard-header">
    <h2 class="dashboard-title">Schedule</h2>
</div>
<div class="card mt-3">
    <div class="card-body">
        @if(isset($courses) && $courses->count() > 0)
            <table class="table">
                <thead>
                    <tr>
                        <th>Activity</th>
                        <th>Schedule</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($courses as $course)
                        <tr>
                            <td>{{ $course->activity_name ?? 'N/A' }}</td>
                            <td>{{ $course->schedule ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-muted">No scheduled activities found. Visit the <a href="{{ route('activities.schedule') }}">Activity Schedule</a> page for the full schedule view.</p>
        @endif
    </div>
</div>
@endsection
