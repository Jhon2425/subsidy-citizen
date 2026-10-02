<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * Available system roles.
     *
     * @var array<string, string>
     */
    public const ROLES = [
        'admin'     => 'Administrator',
        'osca_head' => 'OSCA Head',
        'encoder'   => 'Encoder',
        'staff'     => 'Staff',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_id',
        'position',
        'department',
        'contact_number',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /**
     * Human-readable label for the user's role.
     */
    public function getRoleLabelAttribute(): string
    {
        return self::ROLES[$this->role] ?? ucfirst((string) $this->role);
    }

    /**
     * Position and office combined, e.g. "Encoder — OSCA".
     */
    public function getJobTitleAttribute(): string
    {
        return collect([$this->position, $this->department])
            ->filter()
            ->implode(' — ');
    }

    /*
    |--------------------------------------------------------------------------
    | ROLE HELPERS
    |--------------------------------------------------------------------------
    */

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOscaHead(): bool
    {
        return $this->role === 'osca_head';
    }

    /**
     * Check the user against one or more roles.
     */
    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<User>  $query
     */
    public function scopeActive($query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<User>  $query
     */
    public function scopeRole($query, string $role): void
    {
        $query->where('role', $role);
    }

    /*
    |--------------------------------------------------------------------------
    | ACTIVITY
    |--------------------------------------------------------------------------
    */

    /**
     * Record a successful login without touching updated_at.
     */
    public function recordLogin(?string $ip = null): void
    {
        $this->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $ip,
        ])->saveQuietly();
    }
}