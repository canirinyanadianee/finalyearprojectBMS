<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloodRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'hospital_id',
        'blood_type',
        'units_required',
        'urgency',
        'patient_name',
        'patient_age',
        'reason',
        'status',
        'requested_by',
        'approved_by',
    ];

    protected $casts = [
        'patient_age' => 'integer',
        'units_required' => 'integer',
    ];

    /**
     * Get the hospital that made the request.
     */
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    /**
     * Get the user who requested the blood.
     */
    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get the user who approved the request.
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Check if request is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if request is approved
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * Check if request is completed
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Check if request is urgent
     */
    public function isUrgent()
    {
        return $this->urgency === 'urgent' || $this->urgency === 'emergency';
    }

    /**
     * Check if request is emergency
     */
    public function isEmergency()
    {
        return $this->urgency === 'emergency';
    }
} 