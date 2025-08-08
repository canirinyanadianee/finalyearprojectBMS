<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'donor_id',
        'blood_bank_id',
        'appointment_date',
        'appointment_time',
        'status',
        'notes',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'appointment_time' => 'datetime:H:i',
    ];

    /**
     * Get the donor for the appointment.
     */
    public function donor()
    {
        return $this->belongsTo(Donor::class);
    }

    /**
     * Get the blood bank for the appointment.
     */
    public function bloodBank()
    {
        return $this->belongsTo(BloodBank::class);
    }

    /**
     * Check if appointment is scheduled
     */
    public function isScheduled()
    {
        return $this->status === 'scheduled';
    }

    /**
     * Check if appointment is confirmed
     */
    public function isConfirmed()
    {
        return $this->status === 'confirmed';
    }

    /**
     * Check if appointment is completed
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Check if appointment is cancelled
     */
    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if appointment is no show
     */
    public function isNoShow()
    {
        return $this->status === 'no_show';
    }

    /**
     * Get full appointment datetime
     */
    public function getAppointmentDateTimeAttribute()
    {
        return $this->appointment_date->format('Y-m-d') . ' ' . $this->appointment_time;
    }
} 