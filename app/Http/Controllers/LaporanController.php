<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\FormatLaporan;
use App\Enums\JenisLaporan;
use App\Enums\StatusPeriode;
use App\Http\Requests\StoreLaporanRequest;
use App\Models\Laporan;
use App\Models\PeriodePenilaian;
use App\Services\Laporan\LaporanGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    public function index(): View
    {
        $laporan = Laporan::with(['periode', 'user'])->latest('id')->paginate(15);
        $periode = PeriodePenilaian::whereIn('status', [StatusPeriode::Dihitung->value, StatusPeriode::Final->value])->latest('id')->get();

        return view('laporan.index', compact('laporan', 'periode'));
    }

    public function store(StoreLaporanRequest $request, LaporanGenerator $generator): RedirectResponse
    {
        $laporan = $generator->generate(JenisLaporan::from($request->validated('jenis')), FormatLaporan::from($request->validated('format')),
            PeriodePenilaian::findOrFail($request->validated('periode_id')), $request->user());

        return to_route('laporan.index')->with('success', "Laporan {$laporan->nama_file} berhasil dibuat.")->with('unduh', route('laporan.download', $laporan));
    }

    public function download(Laporan $laporan, LaporanGenerator $generator): StreamedResponse
    {
        return Storage::disk('local')->download($generator->path($laporan), $laporan->nama_file);
    }
}
