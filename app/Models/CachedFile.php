<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CachedFile extends Model
{
    protected $fillable = [
        'service_id',
        'file_id',
        'original_filename',
        'stored_filename',
        'mime_type',
        'file_size',
        'path',
        'last_accessed_at'
    ];

    protected $casts = [
        'last_accessed_at' => 'datetime',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function updateLastAccessed()
    {
        $this->update(['last_accessed_at' => now()]);
    }

    public static function findByServiceAndFileId($serviceId, $fileId)
    {
        return static::where('service_id', $serviceId)
                    ->where('file_id', $fileId)
                    ->first();
    }
}
