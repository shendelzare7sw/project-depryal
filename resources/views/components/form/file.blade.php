@props(['name', 'label', 'required' => false, 'hint' => null, 'accept' => '*', 'isImage' => false])
<x-form.field :name="$name" :label="$label" :required="$required"
    :hint="$hint ?? ($isImage ? 'Maks. 4 MB (jpg/png)' : 'Maks. 12 MB')">
    <input id="{{ $name }}"
        name="{{ $name }}"
        type="file"
        accept="{{ $accept }}"
        @if ($required) required @endif
        {{ $attributes->class([
            'file-input file-input-bordered w-full border-zinc-300 bg-white text-sm file:border-0 file:bg-brand-50 file:font-semibold file:text-brand-700 focus:outline-none focus:ring-4 focus:ring-brand-600/10',
            'border-rose-400' => $errors->has($name),
        ]) }}>
</x-form.field>
