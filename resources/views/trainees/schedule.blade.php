@extends('layouts.app')

@section('title', 'Schedule - ' . ($trainee->trainee_first_name ?? 'Trainee') . ' ' . ($trainee->trainee_last_name ?? '') . ' - CREAMS')

@section('styles')
<style>
    :root {
        --primary-color: #c850c0;
        --secondary-color: #32bdea;
        --success-color: #28a745;
        --warning-color: #ffc107;
        --danger-color: #dc3545;
        --info-color: #17a2b8;
        --dark-color: #2c3e50;
        --light-bg: #f8f9fc;
        --border-color: #e3e6f0;
    }

    body {
        background-color: var(--light-bg);
    }

    .schedule-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 2rem;
        margin-bottom: 2rem;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(200, 80, 192, 0.3);
        position: relative;
        overflow: hidden;
    }

    .schedule-header::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 200px;
        height: 200px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
        transform: translate(50px, -50px);
    }

    .schedule-header h1 {
        margin: 0;
        font-size: 2.5rem;
        font-weight: 700;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        position: relative;
        z-index: 1;
    }

    .schedule-header p {
        margin: 10px 0 0 0;
        opacity: 0.9;
        font-size: 1.1rem;
        position: relative;
        z-index: 1;
    }

    .schedule-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        margin-bottom: 25px;
        border: 1px solid #f1f3f4;
        transition: all 0.3s ease;
    }

    .schedule-card:hover {
        box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        transform: translateY(-3px);
    }

    .schedule-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f1f3f4;
    }

    .schedule-card-header h5 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--dark-color);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .card-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        font-size: 16px;
    }

    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 1px;
        background: #e9ecef;
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 30px;
    }

    .calendar-header {
        background: var(--dark-color);
        color: white;
        padding: 15px 10px;
        text-align: center;
        font-weight: 600;
        font-size: 14px;
    }

    .calendar-day {
        background: white;
        padding: 15px 10px;
        min-height: 100px;
        border: 1px solid #f1f3f4;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .calendar-day:hover {
        background: #f8f9fc;
    }

    .calendar-day.today {
        background: rgba(200, 80, 192, 0.1);
        border-color: var(--primary-color);
    }

    .calendar-day.has-schedule {
        background: rgba(50, 189, 234, 0.1);
        border-left: 4px solid var(--secondary-color);
    }

    .day-number {
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 5px;
    }

    .day-activity {
        background: var(--primary-color);
        color: white;
        padding: 3px 6px;
        border-radius: 4px;
        font-size: 11px;
        margin-bottom: 2px;
        display: block;
        text-align: center;
    }

    .weekly-schedule {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .day-schedule {
        background: white;
        border-radius: 10px;
        padding: 20px;
        border-left: 4px solid var(--primary-color);
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    .day-schedule.today {
        border-left-color: var(--warning-color);
        background: rgba(255, 193, 7, 0.05);
    }

    .day-name {
        font-weight: 700;
        color: var(--dark-color);
        font-size: 1.1rem;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .schedule-time-slot {
        background: #f8f9fc;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 10px;
        border-left: 3px solid var(--secondary-color);
    }

    .schedule-time {
        font-weight: 600;
        color: var(--primary-color);
        font-size: 0.9rem;
    }

    .schedule-activity {
        color: var(--dark-color);
        font-weight: 500;
        margin-top: 3px;
    }

    .schedule-location {
        color: #6c757d;
        font-size: 0.85rem;
        margin-top: 2px;
    }

    .no-schedule {
        text-align: center;
        color: #6c757d;
        font-style: italic;
        padding: 20px;
    }

    .breadcrumb {
        background: transparent;
        padding: 0;
        margin-bottom: 0;
    }

    .breadcrumb-item + .breadcrumb-item::before {
        content: "›";
        color: rgba(255,255,255,0.7);
    }

    .breadcrumb-item a {
        color: rgba(255,255,255,0.8);
        text-decoration: none;
    }

    .breadcrumb-item.active {
        color: white;
    }

    .btn {
        border-radius: 10px;
        padding: 12px 25px;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
        font-size: 14px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        box-shadow: 0 4px 15px rgba(200, 80, 192, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(200, 80, 192, 0.4);
        color: white;
        text-decoration: none;
    }

    .btn-secondary {
        background: #6c757d;
        color: white;
        box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
    }

    .btn-secondary:hover {
        background: #5a6268;
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(108, 117, 125, 0.4);
        color: white;
        text-decoration: none;
    }

    .action-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        justify-content: center;
        margin-top: 30px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Schedule Header -->
    <div class="schedule-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-calendar-alt mr-3"></i>Trainee Schedule</h1>
                    <p>{{ $trainee->trainee_first_name ?? 'Trainee' }} {{ $trainee->trainee_last_name ?? '' }} - Weekly Schedule & Activities</p>
                </div>
                <div class="col-md-4 text-right">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('trainees.index') }}">Trainee</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('trainees.show', $trainee->id) }}">{{ $trainee->trainee_first_name ?? 'Profile' }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Schedule</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Current Week Schedule -->
        <div class="schedule-card">
            <div class="schedule-card-header">
                <h5>
                    <div class="card-icon">
                        <i class="fas fa-calendar-week"></i>
                    </div>
                    Current Week Schedule
                </h5>
                <span class="text-muted">{{ date('M j') }} - {{ date('M j', strtotime('+6 days')) }}, {{ date('Y') }}</span>
            </div>

            <div class="weekly-schedule">
                @php
                    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                    $today = date('l');
                @endphp

                @foreach($days as $day)
                <div class="day-schedule {{ $day === $today ? 'today' : '' }}">
                    <div class="day-name">
                        <i class="fas fa-{{ $day === $today ? 'star' : 'circle' }}"></i>
                        {{ $day }}
                        @if($day === $today)
                            <span class="badge badge-warning">Today</span>
                        @endif
                    </div>

                    @if(isset($weeklySchedule[$day]) && count($weeklySchedule[$day]) > 0)
                        @foreach($weeklySchedule[$day] as $activity)
                        <div class="schedule-time-slot">
                            <div class="schedule-time">{{ $activity['time'] ?? '09:00 AM' }}</div>
                            <div class="schedule-activity">{{ $activity['activity'] ?? 'Therapy Session' }}</div>
                            <div class="schedule-location">{{ $activity['location'] ?? 'Main Hall' }}</div>
                        </div>
                        @endforeach
                    @else
                        <div class="no-schedule">
                            <i class="fas fa-calendar-times"></i>
                            <p>No activities scheduled</p>
                        </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        <!-- Monthly Calendar View -->
        <div class="schedule-card">
            <div class="schedule-card-header">
                <h5>
                    <div class="card-icon">
                        <i class="fas fa-calendar"></i>
                    </div>
                    Monthly Calendar View
                </h5>
                <span class="text-muted">{{ date('F Y') }}</span>
            </div>

            <div class="calendar-grid">
                <!-- Calendar Headers -->
                <div class="calendar-header">Mon</div>
                <div class="calendar-header">Tue</div>
                <div class="calendar-header">Wed</div>
                <div class="calendar-header">Thu</div>
                <div class="calendar-header">Fri</div>
                <div class="calendar-header">Sat</div>
                <div class="calendar-header">Sun</div>

                <!-- Calendar Days -->
                @php
                    $startOfMonth = \Carbon\Carbon::now()->startOfMonth();
                    $endOfMonth = \Carbon\Carbon::now()->endOfMonth();
                    $startDate = $startOfMonth->copy()->startOfWeek();
                    $endDate = $endOfMonth->copy()->endOfWeek();
                    $currentDate = $startDate->copy();
                    $today = \Carbon\Carbon::today();
                @endphp

                @while($currentDate->lte($endDate))
                <div class="calendar-day {{ $currentDate->isSameDay($today) ? 'today' : '' }} {{ rand(0, 4) === 0 ? 'has-schedule' : '' }}">
                    <div class="day-number">{{ $currentDate->format('j') }}</div>
                    @if(rand(0, 4) === 0)
                        <span class="day-activity">Therapy</span>
                    @endif
                    @if(rand(0, 6) === 0)
                        <span class="day-activity" style="background: var(--success-color);">Assessment</span>
                    @endif
                </div>
                @php $currentDate->addDay(); @endphp
                @endwhile
            </div>
        </div>

        <!-- Upcoming Activities -->
        <div class="schedule-card">
            <div class="schedule-card-header">
                <h5>
                    <div class="card-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    Upcoming Activities
                </h5>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="schedule-time-slot">
                        <div class="schedule-time">Tomorrow - 10:00 AM</div>
                        <div class="schedule-activity">Physical Therapy Session</div>
                        <div class="schedule-location">Therapy Room A</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="schedule-time-slot">
                        <div class="schedule-time">Friday - 2:00 PM</div>
                        <div class="schedule-activity">Progress Assessment</div>
                        <div class="schedule-location">Assessment Center</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="schedule-time-slot">
                        <div class="schedule-time">Next Monday - 9:30 AM</div>
                        <div class="schedule-activity">Group Activity</div>
                        <div class="schedule-location">Main Hall</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="schedule-time-slot">
                        <div class="schedule-time">Next Wednesday - 11:00 AM</div>
                        <div class="schedule-activity">Individual Counseling</div>
                        <div class="schedule-location">Counseling Room</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="{{ route('trainees.show', $trainee->id) }}" class="btn btn-primary">
                <i class="fas fa-user"></i>Back to Profile
            </a>
            
            <a href="{{ route('trainees.attendance', $trainee->id) }}" class="btn btn-secondary">
                <i class="fas fa-clipboard-check"></i>View Attendance
            </a>
            
            @if(in_array(session('role'), ['admin', 'supervisor']))
            <a href="{{ route('trainees.edit', $trainee->id) }}" class="btn btn-secondary">
                <i class="fas fa-edit"></i>Edit Schedule
            </a>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add click functionality to calendar days
    const calendarDays = document.querySelectorAll('.calendar-day');
    calendarDays.forEach(day => {
        day.addEventListener('click', function() {
            // Remove previous selections
            document.querySelectorAll('.calendar-day.selected').forEach(d => d.classList.remove('selected'));
            // Add selection to clicked day
            this.classList.add('selected');
            // Here you could load specific day's schedule
        });
    });

    // Highlight today
    const today = new Date();
    const todayElement = document.querySelector('.calendar-day.today');
    if (todayElement) {
        todayElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});
</script>

<style>
.calendar-day.selected {
    background: rgba(200, 80, 192, 0.2) !important;
    border-color: var(--primary-color) !important;
}

.badge {
    padding: 0.25em 0.4em;
    font-size: 75%;
    font-weight: 700;
    border-radius: 0.375rem;
}

.badge-warning {
    color: #212529;
    background-color: #ffc107;
}
</style>
@endsection