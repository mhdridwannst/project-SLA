<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        // 1. OTOMATIS FILTER DATA: Saat user mengambil data (SELECT),
        // hanya tampilkan data milik perusahaan (tenant) user yang sedang login.
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (Auth::check() && Auth::user()->tenant_id) {
                $builder->where('tenant_id', Auth::user()->tenant_id);
            }
        });

        // 2. OTOMATIS ISI TENANT_ID: Saat user membuat data baru (INSERT),
        // otomatis isi kolom tenant_id sesuai perusahaan user yang sedang login.
        static::creating(function ($model) {
            if (Auth::check() && Auth::user()->tenant_id && ! $model->tenant_id) {
                $model->tenant_id = Auth::user()->tenant_id;
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
