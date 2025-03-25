<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'is_read',
        'user_id'
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function sendToAllUsers($title, $message)
    {
        $users = User::all();
        foreach ($users as $user) {
            self::create([
                'title' => $title,
                'message' => $message,
                'user_id' => $user->id,
            ]);
        }
    }

    public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }
}
