<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SubscriptionManagementController extends Controller
{
    public function index()
    {
        $users = User::query()
            ->select('users.*')
            ->selectRaw('CASE 
                WHEN subscription_expires_at > NOW() THEN "active"
                WHEN subscription_expires_at IS NULL THEN "never_subscribed"
                ELSE "expired"
            END as subscription_status')
            ->orderBy('subscription_expires_at', 'desc')
            ->paginate(10);

        return view('admin.subscriptions.index', compact('users'));
    }

    public function edit(User $user)
    {
        return view('admin.subscriptions.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'subscription_expires_at' => ['required', 'date'],
            'points' => ['required', 'integer', 'min:0'],
            'points_to_rollover' => ['required', 'integer', 'min:0'],
        ]);

        $user->subscription_expires_at = Carbon::parse($request->subscription_expires_at);
        $user->points = $request->points;
        $user->points_to_rollover = $request->points_to_rollover;
        $user->save();

        // Log the subscription update
        activity()
            ->performedOn($user)
            ->causedBy(auth()->user())
            ->withProperties([
                'old_expires_at' => $user->getOriginal('subscription_expires_at'),
                'new_expires_at' => $user->subscription_expires_at,
                'old_points' => $user->getOriginal('points'),
                'new_points' => $user->points,
                'old_points_to_rollover' => $user->getOriginal('points_to_rollover'),
                'new_points_to_rollover' => $user->points_to_rollover,
            ])
            ->log('subscription_updated');

        return redirect()
            ->route('admin.subscriptions.index')
            ->with('success', 'Subscription updated successfully.');
    }

    public function renew(User $user)
    {
        $user->renewSubscription();
        
        return redirect()
            ->route('admin.subscriptions.index')
            ->with('success', 'Subscription renewed successfully.');
    }
}
