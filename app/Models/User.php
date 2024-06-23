<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, HasUuids, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */


    protected $guarded = [
        'id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Check if the user is admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if the user is creator
     */
    public function isHod()
    {
        return $this->role === 'hod';
    }

    /**
     * Check if the user is member
     */
    public function isApplicant()
    {
        return $this->role === 'applicant';
    }

    public function programme()
    {

        return $this->belongsTo(Programme::class);
    }
    public function schools()
    {
        return $this->hasMany(School::class);
    }
    public function olevelExams()
    {
        return $this->hasMany(OlevelExam::class);
    }
    public function olevelsubjectGrades()
    {
        return $this->hasMany(OlevelSubjectGrade::class);
    }
    public function certificateUploads()
    {
        return $this->hasMany(CertificateUpload::class);
    }
    public function proposedCourse()
    {

        return $this->hasOne(ProposedCourse::class)->withDefault();
    }
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
    public function hasPaid(string $payment): bool
    {

        return $this->transactions()->where(
            ['status' => TransactionStatus::APPROVED, 'resource' => $payment]
        )->exists() ?? false;
    }
    public function getFullNameAttribute()
    {
        return $this->surname . ' ' . $this->firstname . ' ' . $this->m_name;
    }
    public function hasInvoice(string $paymentType)
    {
        return $this->transactions()->where(
            ['resource' => $paymentType]
        )->count() ?? false;
    }
    public function hodDetails()
    {
        return $this->hasOne(HodUser::class, 'user_id');
    }
}