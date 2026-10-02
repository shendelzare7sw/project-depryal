@props(['name', 'label', 'options' => [], 'value' => null, 'required' => false, 'hint' => null, 'placeholder' => '-- Pilih --'])
<x-form.field :name="$name" :label="$label" :required="$required" :hint="$hint">
    <select id="{{ $name }}"
        name="{{ $name }}"
        @if ($required) required @endif
        {{ $attributes->merge(['class' => 'select select-bordered w-full' . ($errors->has($name) ? ' select-error' : '')]) }}>
        <option value="">{{ $placeholder }}</option>
        @foreach ($options as $val => $optLabel)
        <option value="{{ $val }}" @selected((string) old($name, $value) === (string) $val)>{{ $optLabel }}</option>
        @endforeach
    </select>
</x-form.field>
