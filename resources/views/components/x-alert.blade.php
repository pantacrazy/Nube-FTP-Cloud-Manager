@props([
    'type' => 'info',
    'dismissible' => false,
])

@php
    $typeClasses = match($type) {
        'success' => 'bg-green-100 text-green-700 border-green-200',
        'error' => 'bg-red-100 text-red-700 border-red-200',
        'warning' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
        'info' => 'bg-blue-100 text-blue-700 border-blue-200',
        default => 'bg-gray-100 text-gray-700 border-gray-200',
    };
@endphp

<div class="text-sm px-4 py-3 rounded-lg border {{ $typeClasses }}" role="alert">
    <div class="flex items-start justify-between">
        <p>{{ $slot }}</p>
        @if($dismissible)
            <button type="button" class="ml-4 text-current opacity-70 hover:opacity-100" onclick="this.parentElement.parentElement.remove()">
                ✕
            </button>
        @endif
    </div>
</div>