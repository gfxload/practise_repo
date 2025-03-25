<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function renew(Request $request)
    {
        $user = auth()->user();
        
        // Here you would typically handle payment processing
        // For now, we'll just renew the subscription
        
        $user->renewSubscription();
        
        return redirect()->back()->with('success', 'Subscription renewed successfully! Your points have been rolled over.');
    }
    
    public function status()
    {
        $user = auth()->user();
        
        return response()->json([
            'is_active' => $user->hasActiveSubscription(),
            'expires_at' => $user->subscription_expires_at?->format('Y-m-d'),
            'points' => $user->points,
            'points_to_rollover' => $user->points_to_rollover,
        ]);
    }
}
