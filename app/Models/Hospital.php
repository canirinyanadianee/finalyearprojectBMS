<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hospital extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'hospital_name',
        'address',
        'phone',
        'license_number',
        'region',
    ];

    /**
     * Get the user that owns the hospital profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the blood requests for the hospital.
     */
    public function bloodRequests()
    {
        return $this->hasMany(BloodRequest::class);
    }

    /**
     * Get pending blood requests count
     */
    public function getPendingRequestsAttribute()
    {
        return $this->bloodRequests()->where('status', 'pending')->count();
    }

    /**
     * Get approved blood requests count
     */
    public function getApprovedRequestsAttribute()
    {
        return $this->bloodRequests()->where('status', 'approved')->count();
    }

    /**
     * Get completed blood requests count
     */
    public function getCompletedRequestsAttribute()
    {
        return $this->bloodRequests()->where('status', 'completed')->count();
    }
} 