<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Skill;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

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
        $skills = Skill::all();
        return view('admin.users.create', compact('roles','permissions','skills'));
    }

    public function store(Request $request)
    {
        // Basic validation rules
        $rules = [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'roles'    => 'required|array',
            'phone'    => 'required',
        ];
    
        // إذا تم تمرير حقول المُدرب دائمًا
        $rules['age']         = 'required|integer|min:18';
        $rules['gender']      = 'required|in:male,female';
        $rules['nationality'] = 'required|string|max:255';
        $rules['notes']       = 'required|string';
        $rules['skills']      = 'required|array';
    
        $validatedData = $request->validate($rules);
    
        // Create the user with basic fields
        $user = new User();
        $user->name     = $validatedData['name'];
        $user->email    = $validatedData['email'];
        $user->password = Hash::make($validatedData['password']);
        $user->phone    = $validatedData['phone'];
        $user->save();
    
        $user->age         = $request->age;
        $user->gender      = $request->gender;
        $user->nationality = $request->nationality;
        $user->notes       = $request->notes;
    
        if ($request->filled('video_path')) {
            $user->video = $request->video_path;
        } elseif ($request->hasFile('video')) {
            // Fallback: if file is directly uploaded
            $videoPath = $request->file('video')->store('videos', 'public');
            $user->video = $videoPath;
        }
    
        $user->skills()->sync($request->skills);
    
        $user->save();
    
        // Attach roles
        foreach ($validatedData['roles'] as $role) {
            $user->assignRole($role);
        }
    
        // Optionally assign permissions if provided
        if ($request->filled('permissions')) {
            $user->givePermissionTo($request->permissions);
        }
    
        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }
    
    
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $roles = Role::all();
        $skills = Skill::all();
        return view('admin.users.edit', compact('user', 'roles','skills'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $oldValues = $user->getOriginal();

        // Basic validation rules
        $rules = [
            'name'       => 'required|string|max:255',
            'email'      => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone'      => 'required',
            'roles'      => 'array',
            // الحقول الخاصة بالمدرب متاحة للجميع:
            'age'         => 'required|integer|min:18',
            'gender'      => 'required|in:male,female',
            'nationality' => 'required|string|max:255',
            'notes'       => 'required|string',
            'skills'      => 'required|array',
            'video'       => 'nullable|file|mimes:mp4,avi,mov,wmv|max:10240',
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'string|min:6|confirmed';
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Update basic fields
        $user->name  = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // تحديث الحقول الخاصة بالمدرب (متاحة للجميع)
        $user->age         = $request->age;
        $user->gender      = $request->gender;
        $user->nationality = $request->nationality;
        $user->notes       = $request->notes;

        if ($request->hasFile('video')) {
            // يمكنك حذف الفيديو القديم إن رغبت:
            // Storage::delete('public/' . $user->video);
            $videoPath = $request->file('video')->store('videos', 'public');
            $user->video = $videoPath;
        }

        // تحديث المهارات
        $user->skills()->sync($request->skills);

        // تحديث الأدوار والصلاحيات
        $roles = Role::whereIn('name', $request->roles ?? [])->pluck('name')->toArray();
        $permissions = Permission::whereIn('name', $request->permissions ?? [])->pluck('name')->toArray();

        $user->syncRoles($roles);
        $user->syncPermissions($permissions);

        $user->save();

        // إعداد سجل التدقيق (Audit Log)
        $newValues = $user->getChanges();
        $changesDescription = [];
        foreach ($newValues as $key => $value) {
            $oldVal = isset($oldValues[$key]) ? $oldValues[$key] : 'null';
            $changesDescription[] = "$key changed from '{$oldVal}' to '{$value}'";
        }

        $auditUserId = auth()->check() ? auth()->id() : null;
        AuditLog::create([
            'user_id'     => $auditUserId,
            'description' => 'Updated user: ' . $user->name . ' (' . implode(', ', $changesDescription) . ')',
            'type'        => 'update',
            'entity_id'   => $user->id,
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
            'user_id'     => Auth::id(),
            'description' => 'Deleted user: ' . $user->name,
            'type'        => 'delete',
            'entity_id'   => $user->id,
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
            'user_id'     => Auth::id(),
            'description' => 'Toggled user status: ' . $user->name . ' from ' . $oldStatus . ' to ' . $user->status,
            'type'        => 'toggle_status',
            'entity_id'   => $user->id,
            'entity_type' => User::class,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User status updated successfully.');
    }
}
