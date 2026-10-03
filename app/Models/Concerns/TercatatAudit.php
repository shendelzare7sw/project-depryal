<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Enums\ModulAudit;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Audit perubahan model (spatie/laravel-activitylog): siapa, kapan, sebelum/sesudah.
 * Model pemakai wajib mengimplementasikan modulAudit() dan labelAudit().
 */
trait TercatatAudit
{
    use LogsActivity;

    abstract public function modulAudit(): ModulAudit;

    /**
     * Identitas singkat objek untuk deskripsi log (mis. nama barang).
     */
    abstract public function labelAudit(): string;

    /**
     * Atribut yang tidak boleh tercatat (mis. password).
     *
     * @return list<string>
     */
    protected function atributTanpaAudit(): array
    {
        return ['created_at', 'updated_at'];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logExcept($this->atributTanpaAudit())
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName($this->modulAudit()->value)
            ->setDescriptionForEvent(fn (string $event): string => $this->modulAudit()->label().' '.ModulAudit::kataKerja($event).': '.$this->labelAudit());
    }
}
