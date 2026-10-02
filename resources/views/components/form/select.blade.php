@props(['name', 'label', 'options' => [], 'value' => null, 'required' => false, 'hint' => null, 'placeholder' => '— Pilih —'])
<x-form.field :name="$name" :label="$label" :required="$required" :hint="$hint">
    <select id="{{ $name }}"
        name="{{ $name }}"
        @if ($required) required @endif
        {{ $attributes->class([
            'select select-bordered w-full border-zinc-300 bg-white text-sm font-normal focus:border-brand-600 focus:outline-none focus:ring-4 focus:ring-brand-600/10 disabled:bg-zinc-50 disabled:text-zinc-500',
            'border-rose-400' => $errors->has($name),
        ]) }}>
        <option value="">{{ $placeholder }}</option>
        @foreach ($options as $val => $optLabel)
        <option value="{{ $val }}" @selected((string) old($name, $value) === (string) $val)>{{ $optLabel }}</option>
        @endforeach
    </select>
</x-form.field>
