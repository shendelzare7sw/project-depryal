@props(['name', 'label', 'hint' => null, 'required' => false])
<div class="form-control w-full">
    <label class="label pb-1" for="{{ $name }}">
        <span class="label-text font-medium">
            {{ $label }}
            @if ($required)<span class="text-error ml-0.5">*</span>@endif
        </span>
        @if ($hint)
        <span class="label-text-alt text-base-content/50">{{ $hint }}</span>
        @endif
    </label>
    {{ $slot }}
    @error($name)
    <label class="label pt-0.5">
        <span class="label-text-alt text-error">{{ $message }}</span>
    </label>
    @enderror
</div>
