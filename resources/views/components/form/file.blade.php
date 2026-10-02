@props(['name', 'label', 'required' => false, 'hint' => null, 'accept' => '*', 'isImage' => false])
<x-form.field :name="$name" :label="$label" :required="$required"
    :hint="$hint ?? ($isImage ? 'Maks. 4 MB (jpg/png)' : 'Maks. 12 MB')">
    <input id="{{ $name }}"
        name="{{ $name }}"
        type="file"
        accept="{{ $accept }}"
        @if ($required) required @endif
        {{ $attributes->merge(['class' => 'file-input file-input-bordered w-full' . ($errors->has($name) ? ' file-input-error' : '')]) }}
    >
</x-form.field>
