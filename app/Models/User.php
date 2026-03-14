<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Ecourse\UserCourse;
use App\Models\Ecourse\UserSubscription;
use App\Models\Ecourse\CourseTransaction;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'phone',
        'email',
        'password',
        'google_id',
        'avatar',
        'gender',
        'birth_place',
        'birth_date',
        'city',
        'province',
        'job',
        'instagram',
        'linkedin',
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

    public function upanel()
    {   
        return $this->hasOne(Upanel::class);
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    public function isAdmin(): bool
    {
        return Upanel::where('user_id', $this->id)->exists();
    }

    public function ecourses()
    {
        return $this->hasMany(UserCourse::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CourseTransaction::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function courseCertificates(): HasMany
    {
        return $this->hasMany(\App\Models\Ecourse\CourseCertificate::class, 'user_id');
    }

    /**
     * Vouchers explicitly assigned to a user.
     */
    public function vouchers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Voucher::class, 'voucher_user')
                    ->withPivot(['assigned_at', 'max_usage', 'used_count', 'notes'])
                    ->withTimestamps();
    }

    public function activeSubscription(): ?UserSubscription
    {
        return $this->subscriptions()
            ->active()
            ->latest('start_date')
            ->first();
    }

}
