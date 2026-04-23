<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Role;
use App\Enums\ProgrammesEnum;
use App\Enums\ApplicationStatus;
use App\Enums\TransactionStatus;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use App\Notifications\SendVerificationEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'id',
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
    protected $appends = ['isDe'];
    public function sendEmailVerificationNotification()
    {
        $this->notify(new SendVerificationEmail);
    }
        /**
     * Impersonation context for admin/CIT role switching.
     */
    protected $impersonateRole = null;
    protected $impersonateProgramme = null;

    /**
     * Set the impersonation context (role or programme).
     */
    public function impersonateAs(?string $role = null, ?int $programme = null): void
    {
        $this->impersonateRole = $role;
        $this->impersonateProgramme = $programme;
    }

    /**
     * Clear impersonation context.
     */
    public function clearImpersonation(): void
    {
        $this->impersonateRole = null;
        $this->impersonateProgramme = null;
    }
    public function profilePicture()
    {
        return asset('storage/' . $this->picture);
    }

    /**
     * Check if the user is admin
     */
    public function isAdmin(): bool
    {
        if ($this->impersonateRole) {
            return $this->impersonateRole === 'admin';
        }
        return $this->role === 'admin';
    }

    /**
     * Check if the user is creator
     */
    public function isHod(): bool
    {
        if ($this->impersonateRole) {
            return $this->impersonateRole === 'hod';
        }
        return $this->role === 'hod';
    }
    public function isUndergraduate(): bool
    {
        if ($this->impersonateProgramme) {
            return $this->impersonateProgramme === ProgrammesEnum::Undergraduate->value;
        }
        return $this->programme_id === ProgrammesEnum::Undergraduate->value;
    }
    public function isPostgraduate(): bool
    {
        if ($this->impersonateProgramme) {
            return $this->impersonateProgramme === ProgrammesEnum::PG->value;
        }
        return $this->programme_id === ProgrammesEnum::PG->value;
    }

    /**
     * Check if the user is member
     */
    public function isApplicant(): bool
    {
        if ($this->impersonateRole) {
            return $this->impersonateRole === 'applicant';
        }
        return $this->role === 'applicant';
    }
    public function isStudent(): bool
    {
        if ($this->impersonateRole) {
            return $this->impersonateRole === Role::STUDENT->value;
        }
        return $this->role === Role::STUDENT->value;
    }
    public function isCit(): bool
    {
        if ($this->impersonateRole) {
            return $this->impersonateRole === Role::CIT->value;
        }
        return $this->role === Role::CIT->value;
    }
    public function isCoordinator(): bool
    {
        if ($this->impersonateRole) {
            return $this->impersonateRole === Role::COORDINATOR->value;
        }
        return $this->role === Role::COORDINATOR->value;
    }
    public function getIsDeAttribute(): bool
    {
        // Direct Entry students are those without JAMB scores (0 or null)
        if (!$this->relationLoaded('proposedCourse')) {
            // Avoid lazy loading - return false if relationship not loaded
            return false;
        }
        return $this->proposedCourse && ($this->proposedCourse->jamb_score === null || $this->proposedCourse->jamb_score === 0);
    }
    public function postUtmeUpload(): HasOne
    {
        return $this->hasOne(PostUtmeUpload::class, 'jamb_no', 'jamb_no');
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
    public function isShortlisted(): int
    {
        return $this->proposedCourse()->where(['status' => ApplicationStatus::SHORTLISTED])->count();
    }
    public function isRecommended(): bool
    {
        return $this->proposedCourse()->where(['status' => ApplicationStatus::RECOMMENDED->toString()])->exists();
    }
    public function hodDetails()
    {
        return $this->hasOne(HodUser::class, 'user_id');
    }
    public function coordinator()
    {
        return $this->hasOne(Coordinator::class, 'user_id');
    }
    public function academicDetail()
    {
        return $this->hasOne(AcademicDetail::class);
    }

    public function studentTransactions(): HasMany
    {
        return $this->hasMany(StudentTransaction::class);
    }
}
