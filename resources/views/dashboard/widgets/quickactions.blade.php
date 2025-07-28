{{-- resources/views/dashboard/widgets/quick-actions.blade.php --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-bolt"></i> Quick Actions
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="quick-actions-list">
            @forelse($quickActions ?? [] as $action)
                <a href="{{ isset($action['route']) ? (isset($action['params']) ? route($action['route'], $action['params']) : route($action['route'])) : '#' }}" 
                   class="quick-action-item {{ isset($action['color']) ? 'quick-action-' . $action['color'] : '' }}">
                    <div class="quick-action-icon">
                        <i class="fas {{ $action['icon'] ?? 'fa-link' }}"></i>
                    </div>
                    <div class="quick-action-text">
                        {{ $action['title'] ?? 'Action' }}
                    </div>
                </a>
            @empty
                <div class="text-muted text-center p-3">
                    No quick actions available
                </div>
            @endforelse
        </div>
    </div>
</div>