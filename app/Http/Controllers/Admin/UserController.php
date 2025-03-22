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
    
        // Trainer fields
        $rules['age']         = 'required|integer|min:18';
        $rules['gender']      = 'required|in:male,female';
        $rules['nationality'] = 'required|string|max:255';
        $rules['notes']       = 'required|string';
        $rules['skills']      = 'nullable|array';
        $rules['levels']      = 'nullable|array';
    
        // Shifts validation
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
        
        $user->video = $request->video;

        // Handle avatar
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $avatarPath;
        }
        
        // Sync skills and levels
        if ($request->filled('skills')) {
            $user->skills()->sync($request->skills);
        }
        if ($request->filled('levels')) {
            $user->levels()->sync($request->levels);
        }

        $user->save();
        

        if ($request->has('shifts')) {
            // تقسيم المصفوفة إلى مجموعات من 3 عناصر
            $chunkedShifts = array_chunk($request->shifts, 3);
            foreach ($chunkedShifts as $shiftGroup) {
                // shiftGroup المتوقع:
                // [
                //   ["day" => "Monday"],
                //   ["start_time" => "16:10"],
                //   ["end_time" => "21:29"]
                // ]
                $day        = $shiftGroup[0]['day']        ?? null;
                $start_time = $shiftGroup[1]['start_time'] ?? null;
                $end_time   = $shiftGroup[2]['end_time']   ?? null;
                    
                // التحقق من سلامة البيانات
                if ($day && $start_time && $end_time) {
                    // مثال على إنشاء الشفت
                    $user->shifts()->create([
                        'day'        => $day,
                        'start_time' => $start_time,
                        'end_time'   => $end_time,
                    ]);
                }
            }
        }
        
        
        // Attach roles
        foreach ($validatedData['roles'] as $role) {
            $user->assignRole($role);
        }
        
        // Attach permissions if provided
        if ($request->filled('permissions')) {
            $user->givePermissionTo($request->permissions);
        }
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }
    
    
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $roles = Role::all();
        $skills = Skill::all();
        $levels = Level::all();
        return view('admin.users.edit', compact('user', 'roles','skills','levels'));
    }


    public function update(Request $request, User $user)
    {
        // تحضير قواعد التحقق الأساسية
        // في حقل الـemail نحتاج لتجاهل الـid الحالي عند التحقق من الـunique
        $rules = [
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone'          => 'required',
            'avatar'         => 'nullable|file',
            'cost_per_hour'  => 'nullable|numeric',
            'roles'          => 'required|array', // يجب أن لا تكون فارغة
        ];

        // إذا أراد المستخدم تغيير كلمة المرور (ليست إلزامية)
        // نضيف لها شرط الطول والتأكيد
        if ($request->filled('password')) {
            $rules['password'] = 'required|string|min:6|confirmed';
        }

        // حقول تخص المدرب
        $rules['age']         = 'required|integer|min:18';
        $rules['gender']      = 'required|in:male,female';
        $rules['nationality'] = 'required|string|max:255';
        $rules['notes']       = 'required|string';
        $rules['skills']      = 'nullable|array';
        $rules['levels']      = 'nullable|array';

        // التحقق من وجود مصفوفة الـshifts (ليست إلزامية)
        $rules['shifts'] = 'nullable|array';

        // تحقق المدخلات
        $validatedData = $request->validate($rules);

        // تحديث البيانات الأساسية
        $user->name  = $validatedData['name'];
        $user->email = $validatedData['email'];
        $user->phone = $validatedData['phone'];

        // إذا تم إدخال كلمة مرور جديدة
        if ($request->filled('password')) {
            $user->password = Hash::make($validatedData['password']);
        }

        // تحديث الحقول الإضافية (المدرب/المعلومات)
        $user->age           = $request->age;
        $user->gender        = $request->gender;
        $user->nationality   = $request->nationality;
        $user->notes         = $request->notes;
        $user->cost_per_hour = $request->cost_per_hour;
        $user->video = $request->video;
        // معالجة الصورة (أفاتار)
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $avatarPath;
        }

        // حفظ التغييرات قبل ربط العلاقات
        $user->save();

        // مزامنة الـskills والـlevels إن وُجدت
        if ($request->filled('skills')) {
            $user->skills()->sync($request->skills);
        } else {
            $user->skills()->sync([]); 
        }
        if ($request->filled('levels')) {
            $user->levels()->sync($request->levels);
        } else {
            $user->levels()->sync([]);
        }

        // الشفتات: سنحذف الشفتات القديمة وننشئ الجديدة
        // أو يمكنك تعديل المنطق حسب احتياجك (مثلاً تحديث كل سجل)
        $user->shifts()->delete();
        if ($request->has('shifts')) {
            // تقسيم العناصر لـمجموعات من 3 (day, start_time, end_time)
            $chunkedShifts = array_chunk($request->shifts, 3);
            foreach ($chunkedShifts as $shiftGroup) {
                $day        = $shiftGroup[0]['day']        ?? null;
                $start_time = $shiftGroup[1]['start_time'] ?? null;
                $end_time   = $shiftGroup[2]['end_time']   ?? null;
                
                if ($day && $start_time && $end_time) {
                    $user->shifts()->create([
                        'day'        => $day,
                        'start_time' => $start_time,
                        'end_time'   => $end_time,
                    ]);
                }
            }
        }

        // الأدوار (Roles) 
        // إما نستخدم syncRoles أو نقوم بنفس الطريقة السابقة مع 'assignRole'
        // ينصح باستخدام syncRoles لحذف الأدوار القديمة التلقائيًا 
        // وإضافة الجديدة.
        $user->syncRoles($validatedData['roles']);

        // في حالة permissions 
        // نفضل فك الارتباط القديم ومن ثم تعيين الجديد (إذا أردت مسح القديم):
        $user->permissions()->detach();
        if ($request->filled('permissions')) {
            $user->givePermissionTo($request->permissions);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
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
