@props(['name', 'label', 'value' => null, 'rows' => 3, 'required' => false, 'hint' => null, 'placeholder' => ''])
<x-form.field :name="$name" :label="$label" :required="$required" :hint="$hint">
    <textarea id="{{ $name }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        @if ($required) required @endif
        {{ $attributes->class([
            'textarea textarea-bordered w-full border-zinc-300 bg-white text-sm leading-relaxed placeholder:text-zinc-400 focus:border-brand-600 focus:outline-none focus:ring-4 focus:ring-brand-600/10',
            'border-rose-400' => $errors->has($name),
        ]) }}>{{ old($name, $value) }}</textarea>
</x-form.field>
