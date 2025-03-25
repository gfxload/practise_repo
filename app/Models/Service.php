<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'points_cost',
        'is_active',
        'is_video',
        'image_path',
        'url_pattern',
        'file_id_pattern',
        'expected_url_format',
        'sort_order'
    ];

    protected $casts = [
        'points_cost' => 'integer',
        'is_active' => 'boolean',
        'is_video' => 'boolean',
        'sort_order' => 'integer'
    ];

    protected $appends = ['image_url'];

    public function downloads()
    {
        return $this->hasMany(Download::class);
    }

    public function videoOptions()
    {
        return $this->hasMany(VideoOption::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getImageUrlAttribute()
    {
        if ($this->image_path) {
            return Storage::url($this->image_path);
        }
        return null;
    }

    public function deleteImage()
    {
        if ($this->image_path && Storage::exists($this->image_path)) {
            Storage::delete($this->image_path);
        }
    }

    public function validateUrl($url)
    {
        if (empty($this->url_pattern)) {
            return true;
        }
        
        try {
            $pattern = trim($this->url_pattern, '/');
            return (bool)preg_match('/' . $pattern . '/', $url);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function extractFileId($url)
    {
        if (empty($this->file_id_pattern) || empty($url)) {
            return null;
        }
        
        try {
            $pattern = trim($this->file_id_pattern, '/');
            preg_match('/' . $pattern . '/', $url, $matches);
            return $matches[1] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Find the service that matches the given URL and extract its file ID
     *
     * @param string $url
     * @return array|null Returns ['service' => Service, 'file_id' => string] if found, null otherwise
     */
    public static function findServiceAndExtractId($url)
    {
        // Get all active services
        $services = self::active()->get();
        
        foreach ($services as $service) {
            // Skip services without URL pattern
            if (empty($service->url_pattern)) {
                continue;
            }
            
            // Check if URL matches this service's pattern
            if ($service->validateUrl($url)) {
                // Extract file ID if pattern exists
                $fileId = $service->extractFileId($url);
                
                return [
                    'service' => $service,
                    'file_id' => $fileId,
                ];
            }
        }
        
        return null;
    }
}
