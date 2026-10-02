@props(['name', 'label', 'value' => null, 'rows' => 3, 'required' => false, 'hint' => null, 'placeholder' => ''])
<x-form.field :name="$name" :label="$label" :required="$required" :hint="$hint">
    <textarea id="{{ $name }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        @if ($required) required @endif
        {{ $attributes->merge(['class' => 'textarea textarea-bordered w-full' . ($errors->has($name) ? ' textarea-error' : '')]) }}>{{ old($name, $value) }}</textarea>
</x-form.field>
