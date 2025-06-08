<?php

namespace App\Modules\Doctor\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Modules\Appointment\Models\Appointment;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'specialty',
        'qualification',
        'experience_years',
        'consultation_fee',
        'bio',
        'is_available'
    ];

    protected $casts = [
        'consultation_fee' => 'decimal:2',
        'is_available' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
