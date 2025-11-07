<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'password' => 'hashed',
        ];
    }

    /**
     * Get the role change logs for the user.
     */
    public function roleChangeLogs(): HasMany
    {
        return $this->hasMany(RoleChangeLog::class);
    }

    /**
     * Get the dashboard route based on user role.
     */
    public function getDashboardRoute(): string
    {
        if ($this->hasRole('administrator')) {
            return route('admin.dashboard');
        }

        if ($this->hasRole('collection_crew')) {
            return route('crew.dashboard');
        }

        if ($this->hasRole('resident')) {
            return route('resident.dashboard');
        }

        return route('dashboard');
    }
}
