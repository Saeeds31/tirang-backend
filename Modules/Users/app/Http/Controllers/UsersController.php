<?php

namespace Modules\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\Notifications\Services\NotificationService;
use Modules\Users\Http\Requests\UserStoreRequest;
use Modules\Users\Http\Requests\UserUpdateRequest;
use Modules\Users\Models\Role;
use Modules\Users\Models\User;
use Modules\Users\Models\Validity;
use Modules\Wallet\Models\Wallet;

class UsersController extends Controller
{

    public function setValidity(Request $request, $userId, NotificationService $notifications)
    {
        $user = User::findOrFail($userId);
        $validated_data = $request->validate([
            'to_date' => 'required|date'
        ]);
        $toDate = Carbon::parse($validated_data['to_date']);
        $today = Carbon::today();

        if ($toDate->lt($today)) {
            $notifications->create(
                " انقضاء تاریخ اعتبار کاربر",
                "تاریخ اعتبار کاربر {$user->full_name} لغو  شد",
                "notification_users",
                ['users' => null]
            );
            Validity::where('user_id', $user->id)->delete();
            return response()->json([
                'message' => 'تاریخ منقضی شده است. اعتبار کاربر لغو شد.',
                'success' => true,
            ]);
        }
        Validity::updateOrCreate(
            ['user_id' => $user->id],
            [
                'to_date' => $toDate,
                'status' => true
            ]
        );
        $notifications->create(
            " افزایش تاریخ اعتبار کاربر",
            "تاریخ اعتبار کاربر {$user->full_name} تمدید شد",
            "notification_users",
            ['users' => null]
        );
        return response()->json([
            'message' => 'تاریخ اعتبار تمدید شد.',
            'success' => true,
        ]);
    }
    public function adminInfo(Request $request)
    {
        $user = $request->user();
        $permissions = $user->permissions;
        return response()->json([
            'message' => 'اطلاعات ادمین',
            'user' => $user,
            'permissions' => $permissions
        ]);
    }
    public function index(Request $request)
    {
        $query = User::with(['validity', 'register'])
            ->whereHas('roles', function ($q) {
                $q->where('slug', 'customer');
            });

        //  1) دسترسی‌های کاربر لاگین
        $admin = $request->user();

        // اگر attribute permissions وجود نداشت خطا نده
        $adminPermissions = is_array($admin->permissions ?? null) ? $admin->permissions : [];

        //  2) پیدا کردن permission هایی که با citymanager_ شروع می‌شوند
        $cityPermissions = array_filter($adminPermissions, function ($permission) {
            return str_starts_with($permission, 'citymanager_');
        });

        //  3) استخراج city_id ها از پرمیژن‌ها
        $cityIds = array_map(function ($perm) {
            return intval(str_replace('citymanager_', '', $perm));
        }, $cityPermissions);

        //  4) اگر کاربر admin به چند شهر دسترسی داشت → فقط همان شهرها
        if (!empty($cityIds)) {
            $query->whereIn('city_id', $cityIds);
        }
        // اگر خالی بود → یعنی ادمین همه را ببیند (هیچ محدودیتی نزن)

        // فیلترهای دیگر
        if ($fullName = $request->get('full_name')) {
            $query->where('full_name', 'like', "%{$fullName}%");
        }

        if ($mobile = $request->get('mobile')) {
            $query->where('mobile', 'like', "%{$mobile}%");
        }

        if ($nationalCode = $request->get('national_code')) {
            $query->where('national_code', 'like', "%{$nationalCode}%");
        }

        if ($request->filled('referral')) {
            if ($request->referral == 1) {
                $query->whereNotNull('referred_by');
            } elseif ($request->referral == 0) {
                $query->whereNull('referred_by');
            }
        }

        if ($birthDate = $request->get('birth_date')) {
            $query->whereDate('birth_date', $birthDate);
        }

        if ($provinceId = $request->get('province_id')) {
            $query->whereHas('city', function ($q) use ($provinceId) {
                $q->where('province_id', $provinceId);
            });
        }

        return response()->json($query->paginate(20));
    }


    public function validityReject($userId, NotificationService $notifications)
    {
        $user = User::where('id', $userId)->first();
        if (!is_null($user->referred_by)) {
            return response()->json([
                'success' => false,
                'message' => "این کاربر کد معرف دارد و برای این موضوع نمیتوانید اعتبارش را غیرفعال کنید"
            ], 422);
        }
        $validity = Validity::firstWhere('user_id', $userId);

        if ($validity) {
            $validity->delete();
        }
        $notifications->create(
            "عدم تایید کاربر",
            "کاربر {$user->full_name}  به علت عدم وجود معرف رد شد",
            "notification_users",
            ['users' => null]
        );
        return response()->json([
            'success' => true,
            'message' => "کاربر غیرفعال شد"
        ]);
    }
    public function userProfile(Request $request)
    {
        $user = $request->user();
        $validity = $user->validity()->first();
        if ($validity && $validity->to_date) {
            $toDate = Carbon::parse($validity->to_date);
            $today = Carbon::today();

            if ($toDate->lt($today)) {
                $validity->status = false;
                $validity->save();
            }
        }
        return response()->json([
            'message' => 'پروفایل کاربر',
            'success' => true,
            'user' => $user->load(['city', 'identity_document', 'physical', 'important_document', 'register', 'wallet'])
        ]);
    }
    public function userValidity(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'message' => 'وضعیت کاربر',
            'success' => true,
            'validity' => $user->validity
        ]);
    }
    // لیست مدیران
    public function managerIndex()
    {
        $users = User::with(['roles', 'addresses', 'wallet'])
            ->whereHas('roles', function ($query) {
                $query->whereNotIn('slug', ['customer', 'super admin']);
            })
            ->get();
        return response()->json($users);
    }
    // ساخت کاربر جدید
    public function store(UserStoreRequest $request, NotificationService $notifications)
    {
        $data = $request->validated();
        $customerRoleId = Role::where('slug', 'customer')->value('id');
        if (!$customerRoleId) {
            return response()->json([
                'message' => 'نقش پیشفرض مشتری وجود ندارد لطفا این نقش را در دیتابیس تعریف کنید'
            ], 422);
        }
        $user = User::create($data);
        Wallet::create([
            'user_id' => $user->id,
            'balance' =>  0,
        ]);
        $user->roles()->sync([$customerRoleId]);
        $notifications->create(
            "ساخت کاربر",
            "یک کاربر در سیستم ثبت شد {$user->full_name}",
            "notification_users",
            ['users' => null]
        );
        return response()->json($user->load(['roles', 'addresses', 'wallet']), 201);
    }

    // نمایش یک کاربر
    public function show(User $user)
    {
        return response()->json($user->load(['roles', 'wallet', 'referrer', 'validity', 'register', 'important_document', 'physical', 'identity_document', 'city.province']));
    }

    // ویرایش کاربر
    public function update(UserUpdateRequest $request, User $user)
    {
        $data = $request->validated();
        if (isset($data['mobile'])) {
            unset($data['mobile']);
        }
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $user->update($data);
        return response()->json($user->load(['roles', 'addresses', 'wallet']));
    }

    // حذف کاربر
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }
}
