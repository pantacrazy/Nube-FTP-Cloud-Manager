@props([
    'type' => 'text',
    'name' => '',
    'label' => '',
    'placeholder' => '',
    'value' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'error' => null,
])

@php
    $baseClasses = 'w-full border rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 transition';
    
    $errorClasses = $error ? 'border-red-400 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500';
    
    $disabledClasses = $disabled ? 'bg-gray-100 cursor-not-allowed' : '';
    
    $classes = trim("{$baseClasses} {$errorClasses} {$disabledClasses}");
    
    $id = $name ?: 'input_' . uniqid();
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
    
    <input 
        type="{{ $type }}" 
        name="{{ $name }}" 
        id="{{ $id }}"
        value="{{ $value ?? old($name) }}" 
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $readonly ? 'readonly' : '' }}
        class="{{ $classes }}"
    >
    
    @if($error)
        <p class="text-red-500 text-xs mt-1">{{ $error }}</p>
    @elseif($errors->has($name))
        <p class="text-red-500 text-xs mt-1">{{ $errors->first($name) }}</p>
    @endif
</div>