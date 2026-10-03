<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ModulAudit;
use App\Enums\UserRole;
use App\Models\Concerns\TercatatAudit;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @use HasFactory<UserFactory>
 *
 * @property UserRole $role
 * @property bool $is_active
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TercatatAudit;

    protected $table = 'users';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'is_active',
        'nip',
        'jabatan',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isOperator(): bool
    {
        return $this->role === UserRole::Operator;
    }

    public function isPimpinan(): bool
    {
        return $this->role === UserRole::Pimpinan;
    }

    /**
     * @param  Builder<User>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<User>  $query
     */
    public function scopeRole(Builder $query, UserRole|string $role): void
    {
        $roleValue = $role instanceof UserRole ? $role->value : $role;
        $query->where('role', $roleValue);
    }

    /**
     * Filter daftar pengguna: q (nama/username/email), role, status (aktif|nonaktif).
     *
     * @param  Builder<User>  $query
     * @param  array<string, mixed>  $filters
     */
    public function scopeFilter(Builder $query, array $filters): void
    {
        $q = trim((string) ($filters['q'] ?? ''));
        $role = UserRole::tryFrom((string) ($filters['role'] ?? ''));
        $status = $filters['status'] ?? null;

        $query
            ->when($q !== '', fn (Builder $b) => $b->where(fn (Builder $w) => $w
                ->where('name', 'like', "%{$q}%")->orWhere('username', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")))
            ->when($role, fn (Builder $b) => $b->where('role', $role?->value))
            ->when(in_array($status, ['aktif', 'nonaktif'], true), fn (Builder $b) => $b->where('is_active', $status === 'aktif'));
    }

    /**
     * @return HasMany<Keputusan, $this>
     */
    public function keputusan(): HasMany
    {
        return $this->hasMany(Keputusan::class, 'user_id');
    }

    /**
     * @return HasMany<Laporan, $this>
     */
    public function laporan(): HasMany
    {
        return $this->hasMany(Laporan::class, 'user_id');
    }

    public function modulAudit(): ModulAudit
    {
        return ModulAudit::Pengguna;
    }

    public function labelAudit(): string
    {
        return $this->username;
    }

    /**
     * @return list<string>
     */
    protected function atributTanpaAudit(): array
    {
        return ['password', 'remember_token', 'created_at', 'updated_at'];
    }
}
