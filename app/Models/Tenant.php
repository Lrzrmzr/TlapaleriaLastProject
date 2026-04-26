<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant
{
    use HasDomains;

    /**
     * El ID es el slug del tenant (string), no un entero auto-incremental.
     * Sobrescribimos GeneratesIds::getIncrementing() para forzar tipo string.
     */
    public $incrementing = false;
    protected $keyType = 'string';

    public function getIncrementing(): bool
    {
        return false;
    }

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'settings'      => 'array',
        'data'          => 'array',
    ];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'rfc',
            'email',
            'phone',
            'logo',
            'status',
            'plan',
            'trial_ends_at',
            'settings',
        ];
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trial']);
    }

    public function isTrial(): bool
    {
        return $this->status === 'trial';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }
}
