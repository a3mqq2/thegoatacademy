<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Level;
use App\Models\Skill;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;

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
        $levels = Level::all();
        return view('admin.users.create', compact('roles','permissions','skills','levels'));
    }

    public function store(Request $request)
    {
        // Basic validation rules
        $rules = [
            'name'           => 'required|string|max:255',
            'email'          => 'required|string|email|max:255|unique:users,email',
            'password'       => 'required|string|min:6|confirmed',
            'roles'          => 'required|array',
            'phone'          => 'required',
            'avatar'         => 'nullable',
            'cost_per_hour'  => 'nullable|numeric',
        ];
    
        // Trainer fields (always provided)
        $rules['age']         = 'required|integer|min:18';
        $rules['gender']      = 'required|in:male,female';
        $rules['nationality'] = 'required|string|max:255';
        $rules['notes']       = 'required|string';
        $rules['skills']      = 'nullable|array';
        $rules['levels']      = 'nullable|array';
    
        // Optionally validate shifts if provided.
        // Here we expect an array of arrays, but in your case the structure might be disjoint.
        // We'll add a basic rule to check if shifts is an array.
        $rules['shifts'] = 'nullable|array';
    
        $validatedData = $request->validate($rules);
    
        // Create the user with basic fields
        $user = new User();
        $user->name     = $validatedData['name'];
        $user->email    = $validatedData['email'];
        $user->password = Hash::make($validatedData['password']);
        $user->phone    = $validatedData['phone'];
        $user->save();
    
        // Update trainer-specific and additional fields
        $user->age           = $request->age;
        $user->gender        = $request->gender;
        $user->nationality   = $request->nationality;
        $user->notes         = $request->notes;
        $user->cost_per_hour = $request->cost_per_hour;
        
        if ($request->filled('video_path')) {
            $user->video = $request->video_path;
        } elseif ($request->hasFile('video')) {
            // Fallback: if file is directly uploaded
            $videoPath = $request->file('video')->store('videos', 'public');
            $user->video = $videoPath;
        }
        
        if ($request->has('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $avatarPath;
        }
        
        $user->skills()->sync($request->skills);
        $user->levels()->sync($request->levels);
        $user->save();
        
        // Handle user shifts (if provided)
        if ($request->has('shifts')) {
            $shifts = $request->input('shifts');
            
            // Check if shifts come as separate arrays like:
            // 0 => ['day' => 'Saturday'],
            // 1 => ['start_time' => '05:37'],
            // 2 => ['end_time' => '09:37']
            if (count($shifts) === 3
                && isset($shifts[0]['day'])
                && isset($shifts[1]['start_time'])
                && isset($shifts[2]['end_time'])) {
                
                $combinedShift = [
                    'day'        => $shifts[0]['day'],
                    'start_time' => $shifts[1]['start_time'],
                    'end_time'   => $shifts[2]['end_time']
                ];
                $user->shifts()->create($combinedShift);
            }
            // Otherwise, assume shifts is already an array of associative arrays
            else {
                foreach ($shifts as $shift) {
                    if (isset($shift['day'], $shift['start_time'], $shift['end_time'])) {
                        $user->shifts()->create([
                            'day'        => $shift['day'],
                            'start_time' => $shift['start_time'],
                            'end_time'   => $shift['end_time'],
                        ]);
                    }
                }
            }
        }
        
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
        $levels = Level::all();
        return view('admin.users.edit', compact('user', 'roles','skills','levels'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Basic validation rules
        $rules = [
            'name'           => 'required|string|max:255',
            'email'          => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone'          => 'required',
            'roles'          => 'array',
            'avatar'         => 'nullable',
            'cost_per_hour'  => 'nullable|numeric',
        ];
        
        // Trainer fields (always provided)
        $rules['age']         = 'required|integer|min:18';
        $rules['gender']      = 'required|in:male,female';
        $rules['nationality'] = 'required|string|max:255';
        $rules['notes']       = 'required|string';
        $rules['skills']      = 'nullable|array';
        $rules['levels']      = 'nullable|array';
        
        // Validate shifts as an array
        $rules['shifts'] = 'nullable|array';
        
        // Validate password if provided
        if ($request->filled('password')) {
            $rules['password'] = 'string|min:6|confirmed';
        }
        
        $validatedData = $request->validate($rules);
        
        // Update basic fields
        $user->name  = $validatedData['name'];
        $user->email = $validatedData['email'];
        $user->phone = $validatedData['phone'];
        
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        
        // Update trainer-specific and additional fields
        $user->age           = $request->age;
        $user->gender        = $request->gender;
        $user->nationality   = $request->nationality;
        $user->notes         = $request->notes;
        $user->cost_per_hour = $request->cost_per_hour;
        
        if ($request->filled('video_path')) {
            $user->video = $request->video_path;
        } elseif ($request->hasFile('video')) {
            // Optionally, delete the old video
            $videoPath = $request->file('video')->store('videos', 'public');
            $user->video = $videoPath;
        }
        
        if ($request->has('avatar')) {
            // Optionally, delete the old avatar
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $avatarPath;
        }
        
        $user->skills()->sync($request->skills);
        $user->levels()->sync($request->levels);
        $user->save();
        
        // Update user shifts:
        // Delete existing shifts first
        $user->shifts()->delete();
        
        // Process submitted shifts as an array of associative arrays
        if ($request->has('shifts')) {
            foreach ($request->input('shifts') as $shift) {
                if (!empty($shift['day']) && !empty($shift['start_time']) && !empty($shift['end_time'])) {
                    $user->shifts()->create([
                        'day'        => $shift['day'],
                        'start_time' => $shift['start_time'],
                        'end_time'   => $shift['end_time'],
                    ]);
                }
            }
        }
        
        // Sync roles
        $user->syncRoles($validatedData['roles'] ?? []);
        
        // Optionally sync permissions if provided
        if ($request->filled('permissions')) {
            $user->syncPermissions($request->permissions);
        }
        
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
