<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->input('email') . '%');
        }

        if ($request->filled('role')) {
            $roleId = $request->input('role');
            $query->whereHas('roles', function ($q) use ($roleId) {
                $q->where('roles.id', $roleId);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->paginate(10);
        $roles = Role::all();
        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::all();
        $permissions = Permission::all();
        return view('admin.users.create', compact('roles','permissions'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->syncRoles($request->roles);
        $user->syncPermissions($request->permissions);

        AuditLog::create([
            'user_id' => Auth::id(),
            'description' => 'Created a new user: ' . $user->name,
            'type' => 'create',
            'entity_id' => $user->id,
            'entity_type' => User::class,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $oldValues = $user->getOriginal();

        $rules = [
            'name'  => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'roles' => 'array',
        ];



        if ($request->filled('password')) {
            $rules['password'] = 'string|min:6|confirmed';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user->name  = $request->name;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }


        $roles = Role::whereIn('id', $request->roles)->pluck('name')->toArray();
        $permissions = Permission::whereIn('id', $request->permissions)->pluck('name')->toArray();

        
        $user->syncRoles($roles);
        $user->syncPermissions($permissions);


        $user->save();

        $newValues = $user->getChanges();
        $changesDescription = [];

        foreach ($newValues as $key => $value) {
            $changesDescription[] = "$key changed from '{$oldValues[$key]}' to '{$value}'";
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'description' => 'Updated user: ' . $user->name . ' (' . implode(', ', $changesDescription) . ')',
            'type' => 'update',
            'entity_id' => $user->id,
            'entity_type' => User::class,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.show', compact('user'));
    }




    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        AuditLog::create([
            'user_id' => Auth::id(),
            'description' => 'Deleted user: ' . $user->name,
            'type' => 'delete',
            'entity_id' => $user->id,
            'entity_type' => User::class,
        ]);
        
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }

    public function toggle($id)
    {
        $user = User::findOrFail($id);
        $oldStatus = $user->status;
        $user->status = $user->status == 'active' ? 'inactive' : 'active';
        $user->save();

        AuditLog::create([
            'user_id' => Auth::id(),
            'description' => 'Toggled user status: ' . $user->name . ' from ' . $oldStatus . ' to ' . $user->status,
            'type' => 'toggle_status',
            'entity_id' => $user->id,
            'entity_type' => User::class,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User status updated successfully.');
    }
}
