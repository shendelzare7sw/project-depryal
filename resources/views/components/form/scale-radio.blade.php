@props(['kriteria', 'value' => null])
<div class="space-y-2">
    <div class="font-medium text-sm text-base-content flex items-center justify-between">
        <span>{{ $kriteria->nama }}</span>
        <span class="badge badge-sm badge-outline">{{ $kriteria->kode }} ({{ $kriteria->tipe->label() }})</span>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-5 gap-2">
        @foreach ($kriteria->skala as $skala)
        <label class="flex flex-col items-center justify-center p-3 rounded-lg border border-base-300 hover:border-primary cursor-pointer transition min-h-[44px] has-[:checked]:border-primary has-[:checked]:bg-primary/5 has-[:checked]:font-semibold text-center">
            <input type="radio"
                name="kriteria[{{ $kriteria->id }}]"
                value="{{ $skala->nilai }}"
                @checked((string) $value === (string) $skala->nilai || old("kriteria.{$kriteria->id}") == $skala->nilai)
                class="radio radio-primary radio-sm mb-1">
            <span class="text-sm">{{ $skala->nilai }}</span>
            <span class="text-xs text-base-content/70 mt-0.5 line-clamp-2">{{ $skala->label }}</span>
        </label>
        @endforeach
    </div>
</div>
