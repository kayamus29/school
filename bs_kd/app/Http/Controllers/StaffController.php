<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    public function index()
    {
        $staff = User::where('role', '!=', 'student')->paginate(10);
        return view('staff.index', compact('staff'));
    }

    public function create()
    {
        return view('staff.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:admin,teacher,accountant,librarian,staff',
            'gender' => 'required|string',
            'phone' => 'required|string',
            'nationality' => 'required|string',
            'address' => 'required|string',
            'address2' => 'nullable|string',
            'city' => 'required|string',
            'zip' => 'required|string',
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'gender' => $request->gender,
            'phone' => $request->phone,
            'nationality' => $request->nationality,
            'address' => $request->address,
            'address2' => $request->address2,
            'city' => $request->city,
            'zip' => $request->zip,
        ]);

        // Map column role to Spatie role and assign
        $roleMap = [
            'admin' => 'Admin',
            'teacher' => 'Teacher',
            'accountant' => 'Accountant',
            'librarian' => 'Normal Staff',
            'staff' => 'Normal Staff',
        ];

        if (isset($roleMap[$request->role])) {
            $user->assignRole($roleMap[$request->role]);
        }

        return redirect()->route('staff.index')->with('success', 'Staff member added successfully.');
    }

    public function edit($id)
    {
        $staff = User::findOrFail($id);
        return view('staff.edit', compact('staff'));
    }

    public function update(Request $request, $id)
    {
        $staff = User::findOrFail($id);

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'role' => 'required|string|in:admin,teacher,accountant,librarian,staff',
            'gender' => 'required|string',
            'phone' => 'required|string',
            'nationality' => 'required|string',
            'address' => 'required|string',
            'address2' => 'nullable|string',
            'city' => 'required|string',
            'zip' => 'required|string',
        ]);

        $staff->update($request->only([
            'first_name',
            'last_name',
            'email',
            'role',
            'gender',
            'phone',
            'nationality',
            'address',
            'address2',
            'city',
            'zip'
        ]));

        // Sync Spatie role
        $roleMap = [
            'admin' => 'Admin',
            'teacher' => 'Teacher',
            'accountant' => 'Accountant',
            'librarian' => 'Normal Staff',
            'staff' => 'Normal Staff',
        ];

        if (isset($roleMap[$request->role])) {
            $staff->syncRoles([$roleMap[$request->role]]);
        }

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:8|confirmed']);
            $staff->password = Hash::make($request->password);
            $staff->save();
        }

        return redirect()->route('staff.index')->with('success', 'Staff member updated successfully.');
    }
}
