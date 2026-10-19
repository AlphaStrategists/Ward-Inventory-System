@props([
    'title' => 'Log Records',
    'actionModalId' => null,
    'actionLabel' => '+ Add Record',
    'actionUrl' => null
])

<div class="data-log-card">
    <div class="data-log-header">
        <h2 class="data-log-title">{{ $title }}</h2>
        
        @if($actionModalId)
            <button type="button" class="btn-table-action" data-bs-toggle="modal" data-bs-target="#{{ $actionModalId }}">
                <i class="bi bi-plus-lg"></i>
                <span>{{ $actionLabel }}</span>
            </button>
        @elseif($actionUrl)
            <a href="{{ $actionUrl }}" class="btn-table-action">
                <i class="bi bi-plus-lg"></i>
                <span>{{ $actionLabel }}</span>
            </a>
        @endif
    </div>

    <div class="table-responsive">
        <table class="modern-table">
            <thead>
                <tr>
                    {{ $headers }}
                </tr>
            </thead>
            <tbody>
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>
