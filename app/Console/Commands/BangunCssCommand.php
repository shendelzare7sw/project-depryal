<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use RuntimeException;

/**
 * Bangun CSS tanpa npm/Vite (dijalankan developer setiap kali kelas di view berubah; hasil di-commit):
 * 1) Tailwind CSS v3 standalone CLI → public/css/app.css
 * 2) DaisyUI 4 "styled" + aturan dari build "full" yang benar-benar dipakai view (mis. lg:drawer-open)
 *    → public/vendor/daisyui/daisyui.min.css (±150 KB, bukan 2,9 MB).
 */
class BangunCssCommand extends Command
{
    private const TAILWIND = '3.4.17';

    private const DAISYUI = '4.12.24';

    /**
     * @var string
     */
    protected $signature = 'sikaset:css {--bin= : Path Tailwind standalone CLI yang sudah ada (lewati unduhan)}';

    /**
     * @var string
     */
    protected $description = 'Bangun public/css/app.css (Tailwind CLI) dan DaisyUI ramping tanpa npm';

    public function handle(): int
    {
        File::ensureDirectoryExists($this->cache());

        $this->components->task('Tailwind CSS v'.self::TAILWIND, fn () => $this->tailwind());
        $this->components->task('DaisyUI v'.self::DAISYUI, fn () => $this->daisyui());

        return self::SUCCESS;
    }

    private function tailwind(): bool
    {
        $bin = $this->option('bin') ?: $this->unduh($this->namaBinary(), 'https://github.com/tailwindlabs/tailwindcss/releases/download/v'.self::TAILWIND.'/'.$this->namaBinary(), true);

        $hasil = Process::path(base_path())->timeout(300)->run([
            $bin, '-c', 'tailwind.config.js', '-i', 'resources/css/app.css', '-o', 'public/css/app.css', '--minify',
        ]);

        if ($hasil->failed()) {
            throw new RuntimeException($hasil->errorOutput());
        }

        return true;
    }

    private function daisyui(): bool
    {
        $url = 'https://cdn.jsdelivr.net/npm/daisyui@'.self::DAISYUI.'/dist/';
        $styled = (string) file_get_contents($this->unduh('daisyui-'.self::DAISYUI.'-styled.min.css', $url.'styled.min.css'));
        $full = (string) file_get_contents($this->unduh('daisyui-'.self::DAISYUI.'-full.min.css', $url.'full.min.css'));

        $sudahAda = array_flip(array_map(fn (array $r) => $r[0]."\0".$r[1], $this->aturan($styled)));
        $dipakai = $this->kelasDipakai();
        $tambahan = [];

        foreach ($this->aturan($full) as [$media, $rule]) {
            if (isset($sudahAda[$media."\0".$rule])) {
                continue;
            }

            preg_match_all('/\.((?:\\\\.|[A-Za-z0-9_-])+)/', strstr($rule, '{', true) ?: '', $m);
            $kelas = array_map(fn (string $k) => stripslashes($k), $m[1]);

            if ($kelas !== [] && array_diff($kelas, $dipakai) === []) {
                $tambahan[$media][] = $rule;
            }
        }

        $css = '/*! daisyUI '.self::DAISYUI.' (MIT) — styled + kelas terpakai SIKASET; dibangun `php artisan sikaset:css` */'."\n".$styled;
        foreach ($tambahan as $media => $rules) {
            $css .= $media === '' ? implode('', $rules) : $media.'{'.implode('', $rules).'}';
        }

        File::ensureDirectoryExists(public_path('vendor/daisyui'));
        File::put(public_path('vendor/daisyui/daisyui.min.css'), $css."\n");

        return true;
    }

    /**
     * Pecah CSS (minified) menjadi daftar [konteks @media, aturan].
     *
     * @return list<array{0: string, 1: string}>
     */
    private function aturan(string $css, string $media = ''): array
    {
        $hasil = [];
        $panjang = strlen($css);

        for ($i = 0; $i < $panjang;) {
            $buka = strpos($css, '{', $i);
            if ($buka === false) {
                break;
            }

            $tutup = $this->kurungTutup($css, $buka);
            $kepala = trim(substr($css, $i, $buka - $i));
            $isi = substr($css, $buka + 1, $tutup - $buka - 1);

            $hasil = str_starts_with($kepala, '@media') || str_starts_with($kepala, '@supports')
                ? [...$hasil, ...$this->aturan($isi, $kepala)]
                : [...$hasil, [$media, $kepala.'{'.$isi.'}']];

            $i = $tutup + 1;
        }

        return $hasil;
    }

    private function kurungTutup(string $css, int $buka): int
    {
        for ($i = $buka, $kedalaman = 0, $n = strlen($css); $i < $n; $i++) {
            $kedalaman += match ($css[$i]) {
                '{' => 1,
                '}' => -1,
                default => 0,
            };

            if ($kedalaman === 0) {
                return $i;
            }
        }

        throw new RuntimeException('CSS DaisyUI tidak valid (kurung kurawal tidak seimbang).');
    }

    /**
     * @return list<string>
     */
    private function kelasDipakai(): array
    {
        $teks = collect([...File::allFiles(resource_path('views')), ...File::allFiles(app_path())])
            ->map(fn ($f) => File::get($f->getPathname()))->implode(' ');
        preg_match_all('/[A-Za-z0-9:_-]+/', $teks, $m);

        return array_values(array_unique($m[0]));
    }

    private function unduh(string $nama, string $url, bool $eksekusi = false): string
    {
        $path = $this->cache().DIRECTORY_SEPARATOR.$nama;

        if (! is_file($path)) {
            $this->line("  Mengunduh {$url}");
            Http::timeout(900)->withOptions(['sink' => $path])->get($url)->throw();
            if ($eksekusi) {
                chmod($path, 0755);
            }
        }

        return $path;
    }

    private function namaBinary(): string
    {
        $arm = in_array(strtolower(php_uname('m')), ['arm64', 'aarch64'], true);

        return match (PHP_OS_FAMILY) {
            'Windows' => 'tailwindcss-windows-x64.exe',
            'Darwin' => $arm ? 'tailwindcss-macos-arm64' : 'tailwindcss-macos-x64',
            default => $arm ? 'tailwindcss-linux-arm64' : 'tailwindcss-linux-x64',
        };
    }

    private function cache(): string
    {
        return storage_path('framework/tailwind');
    }
}
