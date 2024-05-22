<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
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
        return $this->role_id === 1;
    }

    /**
     * Check if the user is creator
     */
    public function isCreator()
    {
        return $this->role_id === 2;
    }

    /**
     * Check if the user is member
     */
    public function isMember()
    {
        return $this->role_id === 3;
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
}
