<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloodBank extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bank_name',
        'address',
        'phone',
        'license_number',
        'region',
    ];

    /**
     * Get the user that owns the blood bank profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the blood inventory for the blood bank.
     */
    public function bloodInventory()
    {
        return $this->hasMany(BloodInventory::class);
    }

    /**
     * Get the donations for the blood bank.
     */
    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    /**
     * Get the appointments for the blood bank.
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get total units available
     */
    public function getTotalUnitsAvailableAttribute()
    {
        return $this->bloodInventory()->sum('units_available');
    }

    /**
     * Get total units reserved
     */
    public function getTotalUnitsReservedAttribute()
    {
        return $this->bloodInventory()->sum('units_reserved');
    }

    /**
     * Get low stock blood types
     */
    public function getLowStockBloodTypesAttribute()
    {
        return $this->bloodInventory()->where('status', 'low')->pluck('blood_type');
    }
} 