<?php

namespace Modules\Contact\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Contact\Http\Requests\ContactStoreRequest;
use Modules\Contact\Models\Contact;
use Modules\Notifications\Services\NotificationService;

class ContactController extends Controller
{

    /**
     * ذخیره اطلاعات تماس از سمت فرانت‌اند
     */
    public function store(ContactStoreRequest $request, NotificationService $notifications)
    {
        try {
            DB::beginTransaction();

            $contact = Contact::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'mobile' => $request->mobile,
                'email' => $request->email,
                'address' => $request->address,
                'body' => $request->body,
            ]);
            $notifications->create(
                "فرم تماس با ما",
                "یک پیام در سیستم ثبت شد",
                "notification_contact",
                ['contact' => $contact->id]
            );
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'پیام شما با موفقیت ثبت شد.',
                'data' => $contact
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'خطا در ثبت پیام. لطفاً مجدداً تلاش کنید.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * نمایش لیست پیام‌ها در پنل ادمین
     */
    public function adminIndex(Request $request)
    {
        $query = Contact::query();

        // فیلتر بر اساس وضعیت دیده شدن
        if ($request->has('status')) {
            if ($request->status == 'unseen') {
                $query->unseen();
            } elseif ($request->status == 'seen') {
                $query->seen();
            }
        }

        // جستجو
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            });
        }

        // مرتب‌سازی
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $contacts = $query->paginate($request->get('per_page', 15));

        // آمار
        $statistics = [
            'total' => Contact::count(),
            'unseen' => Contact::unseen()->count(),
            'seen' => Contact::seen()->count(),
            'today' => Contact::whereDate('created_at', today())->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $contacts,
            'statistics' => $statistics,
            'filters' => $request->all()
        ]);
    }

    public function updateSeenAt(Request $request, $id, NotificationService $notifications)
    {
        $request->validate([
            'seen_at' => 'nullable|date'
        ]);

        try {
            $contact = Contact::findOrFail($id);

            $contact->update([
                'seen_at' => $request->seen_at ?? now()
            ]);
            $notifications->create(
                "ویرایش فرم تماس با ما",
                "سطر شماره {$contact->id} در سیستم ویرایش  شد",
                "notification_contact",
                ['contact' => $contact->id]
            );
            return response()->json([
                'success' => true,
                'message' => 'تاریخ دیده شدن با موفقیت بروزرسانی شد.',
                'data' => [
                    'seen_at' => $contact->seen_at,
                    'old_seen_at' => $contact->getOriginal('seen_at')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در بروزرسانی تاریخ دیده شدن.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * بروزرسانی یادداشت ادمین (admin_note)
     */
    public function updateAdminNote(Request $request, $id,NotificationService $notifications)
    {
        $request->validate([
            'admin_note' => 'nullable|date'
        ]);

        try {
            $contact = Contact::findOrFail($id);

            $contact->update([
                'admin_note' => $request->admin_note
            ]);
            $notifications->create(
                "ویرایش فرم تماس با ما",
                "سطر شماره {$contact->id} در سیستم ویرایش  شد",
                "notification_contact",
                ['contact' => $contact->id]
            );
            return response()->json([
                'success' => true,
                'message' => 'یادداشت ادمین با موفقیت بروزرسانی شد.',
                'data' => [
                    'admin_note' => $contact->admin_note,
                    'old_admin_note' => $contact->getOriginal('admin_note')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در بروزرسانی یادداشت ادمین.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
