<?php

namespace App\Models;

use App\Models\Settings\Tenant;
use App\Models\Traits\TenantPermission;
use App\Models\V1\SIS\School\SchoolUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasRoles, TenantPermission {
        TenantPermission::hasPermissionTo insteadof HasRoles;
        HasRoles::hasPermissionTo as hasRolePermissionTo;
        TenantPermission::hasRole insteadof HasRoles;
        HasRoles::hasRole as hasRoleBase;
    }

    protected $guard_name = 'api';

    protected $fillable = [
        'tenant_id',
        'name',
        'identifier',
        'type',
        'verified_at',
        'password',
        'must_change',
        'remember_token',
        'phone',
        'company',
        'job_title',
        'bio',
        'profile_photo_path',
        'created_at',
        'updated_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'verified_at' => 'datetime',
        'must_change' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'identifier' => $this->identifier,
            'name' => $this->name,
        ];
    }

    public function tenants(): BelongsToMany
    {
        // Retorna uma relação vazia - tabela tenant_users não existe mais
        // Usamos uma query que sempre retorna vazio sem acessar a tabela
        // Nota: Isso ainda pode causar erro se a tabela não existir
        // A solução é não usar mais este método no código
        try {
            return $this->belongsToMany(Tenant::class, 'tenant_users')
                ->whereRaw('1 = 0'); // Condição impossível - sempre retorna vazio
        } catch (\Exception $e) {
            // Se a tabela não existir, retornamos uma relação que nunca será executada
            // Isso é um workaround - o ideal seria remover todas as chamadas a este método
            return $this->belongsToMany(Tenant::class, 'tenant_users')
                ->whereRaw('1 = 0');
        }
    }

    public function activeTenants(): BelongsToMany
    {
        // Retorna uma relação vazia já que não usamos mais tenants
        try {
            return $this->tenants()
                ->whereRaw('1 = 0'); // Sempre retorna vazio
        } catch (\Exception $e) {
            // Se der erro, retorna a relação mesmo assim (vai falhar depois, mas evita erro imediato)
            return $this->tenants();
        }
    }

    public function getCurrentTenant(): ?Tenant
    {
        // Retorna null já que não usamos mais tenants
        return null;
    }

    public function switchTenant(int $tenantId): bool
    {
        // Retorna false já que não usamos mais tenants
        return false;
    }

    /**
     * Clear tenant-related cache
     */
    public function clearTenantCache(): void
    {
        $oldTenantId = session('tenant_id');
        if ($oldTenantId) {
            Cache::forget("tenant_{$oldTenantId}");
        }
        Cache::forget("user_current_tenant_{$this->id}");
        Cache::forget("user_tenant_{$this->id}");
    }

    public function belongsToTenant(int $tenantId): bool
    {
        // Retorna false já que não usamos mais tenants
        return false;
    }



    public function isActive(): bool
    {
        return (bool) ($this->is_active ?? true);
    }

    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->whereHas('tenants', function ($q) use ($tenantId) {
            $q->where('tenants.id', $tenantId)->wherePivot('status', 'active');
        });
    }

    /**
     * Get the schools associated with this user through the school_users pivot table.
     */
    public function schools(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\V1\SIS\School\School::class, 'school_users')
            ->using(\App\Models\V1\SIS\School\SchoolUser::class)
            ->withPivot(['role', 'status', 'start_date', 'end_date', 'permissions'])
            ->withTimestamps();
    }

    /**
     * Get the active schools for this user.
     */
    public function activeSchools()
    {
        $user = auth()->user();
        //check all school users and get the active ones
        $schoolUsers = SchoolUser::where('user_id', $user->id)->where('status', 'active')->get();
        Log::info('School users', ['schoolUsers' => $schoolUsers]);
        return $schoolUsers;

        // return $this->schools()
        //     ->wherePivot('status', 'active')
        //     ->where(function ($query) {
        //         $query->where(function ($subQuery) {
        //             // Check that start_date is not in the future
        //             $subQuery->whereNull('school_users.start_date')
        //                      ->orWhere('school_users.start_date', '<=', now());
        //         })
        //         ->where(function ($subQuery) {
        //             // Check that end_date is not in the past
        //             $subQuery->whereNull('school_users.end_date')
        //                      ->orWhere('school_users.end_date', '>=', now());
        //         });
        //     });
    }

    /**
     * Get the current school for this user (from session or first active school).
     */
    public function getCurrentSchool(): ?\App\Models\V1\SIS\School\School
    {
        // Check session first
        $schoolId = session('current_school_id');

        if ($schoolId) {
            $school = $this->activeSchools()->find($schoolId);
            if ($school) {
                return $school;
            }
        }

        // Fallback to first active school
        return $this->activeSchools()->first();
    }

    /**
     * Switch to a different school.
     */
    public function switchSchool(int $schoolId): bool
    {
        if (!$this->activeSchools()->where('schools.id', $schoolId)->exists()) {
            return false;
        }

        session(['current_school_id' => $schoolId]);
        return true;
    }
}
