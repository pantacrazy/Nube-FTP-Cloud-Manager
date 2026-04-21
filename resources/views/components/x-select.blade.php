@props([
    'name' => '',
    'label' => '',
    'required' => false,
    'disabled' => false,
    'error' => null,
    'options' => [],
])

@php
    $baseClasses = 'w-full border rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 transition';
    
    $errorClasses = $error ? 'border-red-400 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500';
    $disabledClasses = $disabled ? 'bg-gray-100 cursor-not-allowed' : '';
    
    $classes = trim("{$baseClasses} {$errorClasses} {$disabledClasses}");
    
    $id = $name ?: 'select_' . uniqid();
@endphp

<div class="w-full">
    @if($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-gray-700 mb-1">
            {!! $label !!}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif
    
    <select 
        name="{{ $name }}" 
        id="{{ $id }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        class="{{ $classes }}"
    >
        @foreach($options as $value => $text)
            <option value="{{ $value }}" {{ old($name) == $value ? 'selected' : '' }}>{{ $text }}</option>
        @endforeach
        {{ $slot }}
    </select>
    
    @if($error)
        <p class="text-red-500 text-xs mt-1">{{ $error }}</p>
    @elseif($errors->has($name))
        <p class="text-red-500 text-xs mt-1">{{ $errors->first($name) }}</p>
    @endif
</div>