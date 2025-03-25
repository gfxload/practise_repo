<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\Download;

class ApiLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'method',
        'api_type',
        'url',
        'order_id',
        'status',
        'http_status',
        'request_data',
        'response_data',
        'user_id',
        'download_id',
    ];

    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the API log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the download associated with the API log.
     */
    public function download(): BelongsTo
    {
        return $this->belongsTo(Download::class);
    }

    /**
     * Scope a query to only include logs of a specific method.
     */
    public function scopeMethod($query, $method)
    {
        return $query->where('method', $method);
    }

    /**
     * Scope a query to only include logs of a specific API type.
     */
    public function scopeApiType($query, $type)
    {
        return $query->where('api_type', $type);
    }

    /**
     * Scope a query to only include successful logs.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope a query to only include failed logs.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}

