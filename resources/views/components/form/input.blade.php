@props(['name', 'label', 'type' => 'text', 'value' => null, 'required' => false, 'hint' => null, 'placeholder' => ''])
<x-form.field :name="$name" :label="$label" :required="$required" :hint="$hint">
    <input id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        @if ($required) required @endif
        {{ $attributes->merge(['class' => 'input input-bordered w-full' . ($errors->has($name) ? ' input-error' : '')]) }}
    >
</x-form.field>
