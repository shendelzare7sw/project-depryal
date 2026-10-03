<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Services\Dashboard\AdminDashboard;
use App\Services\Dashboard\OperatorDashboard;
use App\Services\Dashboard\PimpinanDashboard;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $role = $request->user()->role;
        $service = match ($role) {
            UserRole::Admin => app(AdminDashboard::class),
            UserRole::Operator => app(OperatorDashboard::class),
            UserRole::Pimpinan => app(PimpinanDashboard::class),
        };

        return view("dashboard.{$role->value}", $service->data());
    }
}
