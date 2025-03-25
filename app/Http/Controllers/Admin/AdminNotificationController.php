<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    public function index()
    {
        $users = User::all(['id', 'name', 'email']);
        return view('admin.notifications.create', compact('users'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'users' => ['required', 'array'],
            'users.*' => ['required', 'string'], 
        ]);

        try {
            $selectedUsers = $request->input('users');
            $userIds = [];

            // If "all" is selected, get all user IDs
            if (in_array('all', $selectedUsers)) {
                $userIds = User::pluck('id')->toArray();
            } else {
                // Convert string IDs to integers and validate they exist
                $userIds = User::whereIn('id', $selectedUsers)->pluck('id')->toArray();
                
                if (empty($userIds)) {
                    return back()
                        ->withInput()
                        ->withErrors(['users' => 'Please select at least one valid user.']);
                }
            }

            // Create notifications for selected users
            foreach ($userIds as $userId) {
                Notification::create([
                    'user_id' => $userId,
                    'title' => $request->title,
                    'message' => $request->message,
                    'is_read' => false,
                ]);
            }

            return redirect()
                ->route('admin.notifications.index')
                ->with('success', 'Notifications sent successfully to ' . count($userIds) . ' users.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to send notifications: ' . $e->getMessage()]);
        }
    }
}
