@props(['name', 'label', 'type' => 'text', 'value' => null, 'required' => false, 'hint' => null, 'placeholder' => ''])
<x-form.field :name="$name" :label="$label" :required="$required" :hint="$hint">
    <input id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        @if ($type !== 'password') value="{{ old($name, $value) }}" @endif
        placeholder="{{ $placeholder }}"
        @if ($required) required @endif
        {{ $attributes->class([
            'input input-bordered w-full border-zinc-300 bg-white text-sm placeholder:text-zinc-400 focus:border-brand-600 focus:outline-none focus:ring-4 focus:ring-brand-600/10 read-only:bg-zinc-50 read-only:text-zinc-500',
            'border-rose-400 focus:border-rose-500 focus:ring-rose-500/10' => $errors->has($name),
        ]) }}>
</x-form.field>
