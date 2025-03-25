<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Service;
use App\Models\PointTransaction;
use App\Models\VideoOption;
use App\Jobs\ProcessDownload;

class Download extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'service_id',
        'original_url',
        'file_id',
        'points_spent',
        'local_path',
        'status',
        'error_message',
        'image_url',
        'expires_at',
        'order_id',
        'video_option_id'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'expires_at'
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    protected static function booted()
    {
        static::creating(function ($download) {
            $download->expires_at = now()->addMonth();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function videoOption()
    {
        return $this->belongsTo(VideoOption::class);
    }

    public function pointTransaction()
    {
        return $this->hasOne(PointTransaction::class);
    }

    public function updateStatus(string $status, ?string $errorMessage = null)
    {
        $this->status = $status;
        $this->error_message = $errorMessage;
        $this->save();
    }

    public function markAsProcessing($orderId = null)
    {
        $this->status = 'processing';
        if ($orderId) {
            $this->order_id = $orderId;
        }
        $this->save();

        // جدولة مهمة المعالجة بعد دقيقة
        ProcessDownload::dispatch($this)
            ->onQueue('downloads')
            ->delay(now()->addMinute());
    }

    public function markAsFailed($message)
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $message
        ]);
    }

    public function markAsCompleted($localPath)
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'local_path' => $localPath
        ]);
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isProcessing()
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeForUser($query)
    {
        $isAdminRoute = str_contains(request()->path(), 'admin');
        if (!$isAdminRoute) {
            return $query->notExpired();
        }
        return $query;
    }
}
