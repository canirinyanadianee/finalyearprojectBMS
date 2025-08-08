<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloodInventory extends Model
{
    use HasFactory;

    protected $table = 'blood_inventory';

    protected $fillable = [
        'blood_bank_id',
        'blood_type',
        'units_available',
        'units_reserved',
        'status',
        'expiry_date',
        'last_updated',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'last_updated' => 'datetime',
    ];

    /**
     * Get the blood bank that owns the inventory.
     */
    public function bloodBank()
    {
        return $this->belongsTo(BloodBank::class);
    }

    /**
     * Get total units (available + reserved)
     */
    public function getTotalUnitsAttribute()
    {
        return $this->units_available + $this->units_reserved;
    }

    /**
     * Check if inventory is low
     */
    public function isLow()
    {
        return $this->status === 'low';
    }

    /**
     * Check if inventory is urgent
     */
    public function isUrgent()
    {
        return $this->status === 'urgent';
    }

    /**
     * Check if blood is expiring soon (within 7 days)
     */
    public function isExpiringSoon()
    {
        if (!$this->expiry_date) {
            return false;
        }
        return $this->expiry_date->diffInDays(now()) <= 7;
    }
} 