<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    use HasFactory;

    protected $fillable = [
        'donor_id',
        'blood_bank_id',
        'blood_type',
        'units_donated',
        'donation_date',
        'hemoglobin_level',
        'blood_pressure',
        'pulse_rate',
        'temperature',
        'notes',
        'status',
    ];

    protected $casts = [
        'donation_date' => 'date',
        'hemoglobin_level' => 'decimal:2',
        'temperature' => 'decimal:2',
        'units_donated' => 'integer',
        'pulse_rate' => 'integer',
    ];

    /**
     * Get the donor who made the donation.
     */
    public function donor()
    {
        return $this->belongsTo(Donor::class);
    }

    /**
     * Get the blood bank that received the donation.
     */
    public function bloodBank()
    {
        return $this->belongsTo(BloodBank::class);
    }

    /**
     * Check if donation is completed
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Check if donation is scheduled
     */
    public function isScheduled()
    {
        return $this->status === 'scheduled';
    }

    /**
     * Check if donation is cancelled
     */
    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if donation is deferred
     */
    public function isDeferred()
    {
        return $this->status === 'deferred';
    }
} 