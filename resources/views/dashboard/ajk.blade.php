<div class="ajk-dashboard-content">
  <div class="row">
    <div class="col-md-12">
      <div class="chart-container" style="position: relative; height: 300px;">
        <canvas id="eventParticipationChart"></canvas>
      </div>
      <h6 class="text-centre mt-3">Event Participation Analytics</h6>
    </div>
  </div>
  
  <!-- Upcoming Events -->
  <div class="card mt-4">
    <div class="card-header">
      <h5 class="card-title">Upcoming Events</h5>
      <div class="card-options">
        <a href="{{ route('ajk.events') }}" class="btn btn-sm btn-primary">
          <i class="fas fa-plus"></i> Create New Event
        </a>
      </div>
    </div>
    <div class="card-body">
      @if(isset($data['events']) && $data['events']->count() > 0)
      <div class="events-list">
        @foreach($data['events'] as $event)
        <div class="event-item">
          <div class="event-date">
            <div class="date">{{ \Carbon\Carbon::parse($event->date)->format('d') }}</div>
            <div class="month">{{ \Carbon\Carbon::parse($event->date)->format('M') }}</div>
          </div>
          <div class="event-details">
            <h5>{{ $event->title }}</h5>
            <div class="event-info">
              <span><i class="fas fa-map-marker-alt"></i> {{ $event->location }}</span>
              <span><i class="fas fa-clock"></i> {{ $event->start_time }} - {{ $event->end_time }}</span>
            </div>
            <div class="event-participants">
              <div class="attendance">
                <span class="confirmed">{{ $event->participants()->where('status', 'confirmed')->count() }} Confirmed</span> / 
                <span class="invited">{{ $event->max_participants }} Capacity</span>
              </div>
            </div>
          </div>
          <div class="event-actions">
            <a href="{{ route('ajk.event.view', $event->id) }}" class="btn btn-sm btn-info">Manage</a>
          </div>
        </div>
        @endforeach
      </div>
      @else
      <div class="empty-state">
        <div class="empty-state-icon">
          <i class="fas fa-calendar-plus"></i>
        </div>
        <p>No upcoming events scheduled</p>
        <a href="{{ route('ajk.event.create') }}" class="btn btn-primary mt-3">Create New Event</a>
      </div>
      @endif
    </div>
  </div>
  
  <!-- Volunteer Management -->
  <div class="card mt-4">
    <div class="card-header">
      <h5 class="card-title">Volunteer Applications</h5>
    </div>
    <div class="card-body">
      @if(isset($data['volunteers']) && $data['volunteers']->count() > 0)
      <div class="volunteers-list">
        @foreach($data['volunteers'] as $volunteer)
        <div class="volunteer-item">
          <div class="volunteer-info">
            <div class="volunteer-avatar">
              <img src="{{ asset('images/volunteer-avatar.jpg') }}" alt="Volunteer">
            </div>
            <div class="volunteer-details">
              <h5>{{ $volunteer->name }}</h5>
              <div class="volunteer-email">{{ $volunteer->email }}</div>
              <div class="volunteer-phone">{{ $volunteer->phone }}</div>
              <div class="volunteer-interests">
                <span class="interest-badge">{{ $volunteer->interest }}</span>
              </div>
            </div>
          </div>
          <div class="volunteer-actions">
            <form action="{{ route('ajk.volunteer.change-status', $volunteer->id) }}" method="POST" class="d-inline">
              @csrf
              <input type="hidden" name="status" value="approved">
              <button type="submit" class="btn btn-sm btn-success">Approve</button>
            </form>
            <form action="{{ route('ajk.volunteer.change-status', $volunteer->id) }}" method="POST" class="d-inline">
              @csrf
              <input type="hidden" name="status" value="rejected">
              <button type="submit" class="btn btn-sm btn-danger">Reject</button>
            </form>
          </div>
        </div>
        @endforeach
      </div>
      @else
      <div class="empty-state">
        <div class="empty-state-icon">
          <i class="fas fa-hands-helping"></i>
        </div>
        <p>No pending volunteer applications</p>
      </div>
      @endif
    </div>
  </div>
</div>