<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\Models\Activity;
use App\Models\Download;
use App\Models\PointTransaction;
use App\Models\Notification;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'points',
        'is_admin',
        'is_active',
        'subscription_expires_at',
        'points_to_rollover'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
        'is_active' => 'boolean',
        'points' => 'integer',
        'subscription_expires_at' => 'datetime',
    ];

    /**
     * Get the downloads for the user.
     */
    public function downloads()
    {
        return $this->hasMany(Download::class);
    }

    public function pointTransactions()
    {
        return $this->hasMany(PointTransaction::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class)->orderBy('created_at', 'desc');
    }

    public function unreadNotifications()
    {
        return $this->notifications()->where('is_read', false);
    }

    public function addPoints(int $points, string $description, ?Download $download = null)
    {
        $this->increment('points', $points);
        
        $this->notifications()->create([
            'title' => 'Points Added',
            'message' => "You received {$points} points. {$description}",
        ]);

        return $this->pointTransactions()->create([
            'points' => $points,
            'type' => 'credit',
            'description' => $description,
            'download_id' => $download?->id
        ]);
    }

    public function deductPoints(int $points, string $description, ?Download $download = null)
    {
        if ($this->points < $points) {
            throw new \Exception('Insufficient points');
        }

        $this->points -= $points;
        $this->save();

        return $this->pointTransactions()->create([
            'points' => $points,
            'type' => 'debit',
            'description' => $description,
            'download_id' => $download?->id
        ]);
    }

    public function hasActiveSubscription()
    {
        return $this->subscription_expires_at && $this->subscription_expires_at->isFuture();
    }

    public function renewSubscription()
    {
        // Roll over remaining points if subscription is still active
        if ($this->hasActiveSubscription()) {
            $this->points += $this->points_to_rollover;
        }
        
        // Reset points to rollover
        $this->points_to_rollover = $this->points;
        
        // Extend subscription by one month
        $this->subscription_expires_at = $this->subscription_expires_at && $this->subscription_expires_at->isFuture() 
            ? $this->subscription_expires_at->addMonth() 
            : now()->addMonth();
        
        $this->save();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['subscription_expires_at', 'points', 'points_to_rollover'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "User subscription was {$eventName}")
            ->dontSubmitEmptyLogs();
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->log_name = 'subscription';
    }
}
