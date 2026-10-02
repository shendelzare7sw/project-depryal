<?php

declare(strict_types=1);

namespace App\View\Components\Layouts;

use App\Models\User;
use App\Support\Navigation;
use Illuminate\Contracts\View\View;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

/**
 * Shell pascalogin: sidebar, topbar (judul halaman + notifikasi + menu pengguna), bottom-nav mobile.
 */
class App extends Component
{
    /** @var list<array{label: string, items: list<array{label: string, route: string, icon: string, active: string, mobile: bool}>}> */
    public array $navigation = [];

    /** @var list<array{label: string, route: string, icon: string, active: string, mobile: bool}> */
    public array $mobileNavigation = [];

    /** @var Collection<int, DatabaseNotification> */
    public Collection $notifikasi;

    public int $notifikasiBelumDibaca = 0;

    public function __construct(
        public ?string $title = null,
        public ?string $subtitle = null,
    ) {
        $this->notifikasi = collect();
        $user = Auth::user();

        if ($user instanceof User) {
            $this->navigation = Navigation::for($user);
            $this->mobileNavigation = Navigation::mobile($user);
            $this->notifikasi = $user->notifications()->limit(6)->get();
            $this->notifikasiBelumDibaca = $user->unreadNotifications()->count();
        }
    }

    public function render(): View
    {
        return view('layouts.app');
    }
}
