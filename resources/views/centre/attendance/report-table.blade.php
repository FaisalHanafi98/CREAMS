<table class="table table-striped align-middle">
    <caption>Recorded attendance marks</caption>
    <thead>
        <tr><th scope="col">Date</th><th scope="col">Trainee</th><th scope="col">Activity</th><th scope="col">Session</th><th scope="col">Status</th></tr>
    </thead>
    <tbody>
        @forelse ($marks as $mark)
            <tr>
                <td>{{ $mark->attendance_date->format('d/m/Y') }}</td>
                <td>{{ $mark->trainee?->name ?? 'Unavailable' }} <small>{{ $mark->trainee?->trainee_id }}</small></td>
                <td>{{ $mark->session?->activity?->activity_name ?? 'Unavailable' }}</td>
                <td>{{ $mark->session?->session_name ?? 'Unavailable' }}</td>
                <td>{{ ucfirst($mark->status) }}</td>
            </tr>
        @empty
            <tr><td colspan="5">No recorded attendance in this period.</td></tr>
        @endforelse
    </tbody>
</table>
