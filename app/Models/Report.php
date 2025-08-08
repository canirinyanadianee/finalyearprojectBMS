<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_type',
        'generated_by',
        'parameters',
        'file_path',
        'status',
    ];

    protected $casts = [
        'parameters' => 'array',
    ];

    /**
     * Get the user who generated the report.
     */
    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /**
     * Check if report is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if report is completed
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Check if report failed
     */
    public function isFailed()
    {
        return $this->status === 'failed';
    }

    /**
     * Scope to get reports by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('report_type', $type);
    }

    /**
     * Scope to get completed reports
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get pending reports
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
} 