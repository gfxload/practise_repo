<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        Log::info('Creating user with data:', $request->all());

        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'points' => ['required', 'integer', 'min:0'],
                'subscription_expires_at' => ['nullable', 'date'],
            ]);

            Log::info('Validation passed');

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'points' => $validated['points'],
                'is_admin' => $request->has('is_admin'),
                'is_active' => $request->has('is_active'),
                'subscription_expires_at' => $request->filled('subscription_expires_at') ? $request->subscription_expires_at : null,
            ]);

            Log::info('User created successfully:', ['id' => $user->id]);

            // Send welcome notification to the new user
            Notification::create([
                'user_id' => $user->id,
                'title' => 'Welcome to the System',
                'message' => "Welcome {$user->name}! Your account has been created successfully.",
            ]);

            // Send notification to all admins about new user
            $admins = User::where('is_admin', true)->get();
            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => 'New User Registration',
                    'message' => "New user {$user->name} ({$user->email}) has been created.",
                ]);
            }

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'User created successfully');
        } catch (\Exception $e) {
            Log::error('Error creating user:', ['error' => $e->getMessage()]);
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create user: ' . $e->getMessage()]);
        }
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
                'points' => ['required', 'integer', 'min:0'],
                'subscription_expires_at' => ['nullable', 'date'],
            ]);

            if ($request->filled('password')) {
                $request->validate([
                    'password' => ['required', 'confirmed', Rules\Password::defaults()],
                ]);
                $validated['password'] = Hash::make($request->password);
            }

            $oldStatus = $user->is_active;
            $newStatus = $request->has('is_active');

            // تخزين تاريخ الاشتراك القديم للمقارنة
            $oldSubscriptionDate = $user->subscription_expires_at;
            
            // تحديث بيانات المستخدم
            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'points' => $validated['points'],
                'is_admin' => $request->has('is_admin'),
                'is_active' => $newStatus,
            ];

            // إضافة كلمة المرور إذا تم تغييرها
            if (isset($validated['password'])) {
                $updateData['password'] = $validated['password'];
            }

            // إضافة تاريخ انتهاء الاشتراك
            if ($request->filled('subscription_expires_at')) {
                $updateData['subscription_expires_at'] = $request->subscription_expires_at;
            } else {
                $updateData['subscription_expires_at'] = null;
            }

            $user->update($updateData);

            // إنشاء إشعار إذا تم تغيير تاريخ الاشتراك
            if ($oldSubscriptionDate != $user->subscription_expires_at) {
                Notification::create([
                    'user_id' => $user->id,
                    'title' => 'Subscription Date Updated',
                    'message' => $user->subscription_expires_at 
                        ? 'Your subscription expiry date has been updated to: ' . $user->subscription_expires_at->format('Y-m-d H:i:s')
                        : 'Your subscription expiry date has been removed.',
                ]);
            }

            // إشعار المستخدم إذا تغيرت حالة حسابه
            if ($oldStatus !== $newStatus) {
                Notification::create([
                    'user_id' => $user->id,
                    'title' => 'Account Status Updated',
                    'message' => 'Your account has been ' . ($newStatus ? 'activated' : 'deactivated') . '.',
                ]);
            }

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'User updated successfully');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update user: ' . $e->getMessage()]);
        }
    }

    public function destroy(User $user)
    {
        try {
            // Don't allow deleting your own account
            if ($user->id === auth()->id()) {
                return back()->withErrors(['error' => 'You cannot delete your own account']);
            }

            $user->delete();

            // Notify admins about user deletion
            $admins = User::where('is_admin', true)->get();
            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => 'User Deleted',
                    'message' => "User {$user->name} ({$user->email}) has been deleted.",
                ]);
            }

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'User deleted successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete user: ' . $e->getMessage()]);
        }
    }

    public function addPoints(Request $request, User $user)
    {
        $request->validate([
            'points' => ['required', 'integer', 'min:1']
        ]);

        try {
            $points = $request->input('points');
            $user->points += $points;
            $user->save();

            Log::info('Points added to user:', [
                'user_id' => $user->id,
                'points_added' => $points,
                'new_total' => $user->points
            ]);

            return back()->with('success', "Added {$points} points to {$user->name}");
        } catch (\Exception $e) {
            Log::error('Error adding points to user:', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors(['error' => 'Failed to add points: ' . $e->getMessage()]);
        }
    }
}
