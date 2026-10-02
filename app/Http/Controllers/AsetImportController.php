<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Aset\ImportAset;
use App\Exports\AsetTemplateExport;
use App\Http\Requests\ImportAsetRequest;
use App\Services\Aset\AsetImportFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Import data BMD dari Excel: unggah → pratinjau validasi → konfirmasi simpan.
 */
class AsetImportController extends Controller
{
    public function create(): View
    {
        return view('aset.import');
    }

    public function store(ImportAsetRequest $request, AsetImportFile $file): RedirectResponse
    {
        $file->put($request->user(), $request->file('file'));

        return to_route('aset.import.preview');
    }

    public function preview(Request $request, AsetImportFile $file): View
    {
        return view('aset.import-preview', ['result' => $file->read($request->user())]);
    }

    public function confirm(Request $request, AsetImportFile $file, ImportAset $import): RedirectResponse
    {
        $jumlah = $import->execute($file->read($request->user()));
        $file->clear($request->user());

        return to_route('aset.index')->with('success', "{$jumlah} data aset berhasil diimpor.");
    }

    public function template(): BinaryFileResponse
    {
        return Excel::download(new AsetTemplateExport, 'template-import-bmd.xlsx');
    }
}
