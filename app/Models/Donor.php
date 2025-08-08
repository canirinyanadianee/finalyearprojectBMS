<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'blood_type',
        'phone',
        'address',
        'date_of_birth',
        'last_donation_date',
        'eligibility_status',
        'health_conditions',
        'emergency_contact',
        'emergency_phone',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'last_donation_date' => 'date',
    ];

    /**
     * Get the user that owns the donor profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the donations for the donor.
     */
    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    /**
     * Get the appointments for the donor.
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Check if donor is eligible for donation
     */
    public function isEligible()
    {
        return $this->eligibility_status === 'eligible';
    }

    /**
     * Get total donations count
     */
    public function getTotalDonationsAttribute()
    {
        return $this->donations()->where('status', 'completed')->count();
    }

    /**
     * Get total units donated
     */
    public function getTotalUnitsDonatedAttribute()
    {
        return $this->donations()->where('status', 'completed')->sum('units_donated');
    }
} 