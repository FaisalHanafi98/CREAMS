{{-- 
    Custom Pagination Component
    Usage: @include('components.custom-pagination', ['items' => $paginatedData])
--}}

@if($items->lastPage() > 1)
<!-- Custom Pagination -->
<div class="text-center mt-4">
    <div class="mb-2">
        <small class="text-muted">
            Page {{ $items->currentPage() }} of {{ $items->lastPage() }} • {{ $items->total() }} total {{ $items->total() == 1 ? 'item' : 'items' }}
        </small>
    </div>
    
    <div class="d-inline-flex">
        @php
            $current = $items->currentPage();
            $last = $items->lastPage();
            $start = max(1, $current - 2);
            $end = min($last, $current + 2);
        @endphp
        
        {{-- Previous --}}
        @if(!$items->onFirstPage())
            <a href="{{ $items->appends(request()->query())->previousPageUrl() }}" class="text-decoration-none mx-1" style="color: #667eea;">‹ Prev</a>
        @endif
        
        {{-- First page --}}
        @if($start > 1)
            <a href="{{ $items->appends(request()->query())->url(1) }}" class="text-decoration-none mx-1 px-2 py-1 rounded {{ $current == 1 ? 'bg-primary text-white' : 'text-secondary' }}">1</a>
            @if($start > 2)
                <span class="mx-1 text-muted">…</span>
            @endif
        @endif
        
        {{-- Page range --}}
        @for($page = $start; $page <= $end; $page++)
            @if($page == $current)
                <span class="mx-1 px-2 py-1 rounded bg-primary text-white">{{ $page }}</span>
            @else
                <a href="{{ $items->appends(request()->query())->url($page) }}" class="text-decoration-none mx-1 px-2 py-1 rounded text-secondary hover-bg-light">{{ $page }}</a>
            @endif
        @endfor
        
        {{-- Last page --}}
        @if($end < $last)
            @if($end < $last - 1)
                <span class="mx-1 text-muted">…</span>
            @endif
            <a href="{{ $items->appends(request()->query())->url($last) }}" class="text-decoration-none mx-1 px-2 py-1 rounded text-secondary">{{ $last }}</a>
        @endif
        
        {{-- Next --}}
        @if($items->hasMorePages())
            <a href="{{ $items->appends(request()->query())->nextPageUrl() }}" class="text-decoration-none mx-1" style="color: #667eea;">Next ›</a>
        @endif
    </div>
</div>
@endif

<style>
.hover-bg-light:hover {
    background-color: #f8f9fa !important;
}
</style>