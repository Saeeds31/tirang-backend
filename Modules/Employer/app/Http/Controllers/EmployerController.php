<?php

namespace Modules\Employer\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Employer\Http\Requests\EmployerStoreRequest;
use Modules\Employer\Http\Requests\EmployerUpdateRequest;
use Modules\Employer\Models\Employer;
use Modules\Notifications\Services\NotificationService;
use Modules\Portfolio\Models\Portfolio;

class EmployerController extends Controller
{
    public function index(Request $request)
    {
        $query = Employer::query();
        if ($fullName = $request->get('full_name')) {
            $query->where('full_name', 'like', "%{$fullName}%");
        }
        if ($business_label = $request->get('business_label')) {
            $query->where('business_label', 'like', "%{$business_label}%");
        }
        if ($mobile = $request->get('mobile')) {
            $query->where('mobile', 'like', "%{$mobile}%");
        }
        $employers = $query->latest('id')->get();
        return response()->json([
            'success' => true,
            'message' => 'لیست کارفرمایان',
            'data'    => $employers
        ]);
    }

    // Show single article
    public function show($id)
    {
        $employer = Employer::find($id);

        if (!$employer) {
            return response()->json([
                'success' => false,
                'message' => 'کارفرما پیدا نشد',
            ], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'جزئیات کارفرما',
            'data'    => $employer
        ]);
    }

    // Store new article
    public function store(EmployerStoreRequest $request, NotificationService $notifications)
    {
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('employers', 'public');
            $data['image'] = $path;
        }
        if ($request->hasFile('business_logo')) {
            $path = $request->file('business_logo')->store('employers', 'public');
            $data['business_logo'] = $path;
        }
        $employer = Employer::create($data);
        $notifications->create(
            " ثبت کارفرما",
            "کارفرما {$employer->full_name} در سیستم ثبت  شد",
            "notification_users",
            ['employer' => $employer->id]
        );
        return response()->json([
            'success' => true,
            'message' => 'کارفرما با موفقیت ثبت شد',
            'data'    => $employer
        ], 201);
    }


    // Update article
    public function update(EmployerUpdateRequest $request, $id, NotificationService $notifications)
    {
        $employer = Employer::findOrFail($id);
        if (!$employer) {
            return response()->json([
                'success' => false,
                'message' => 'کارفرما پیدا نشد',
            ], 404);
        }
        $data = $request->validated();
        if ($request->hasFile('image')) {
            if ($employer->image) {
                Storage::disk('public')->delete($employer->image);
            }
            $path = $request->file('image')->store('employers', 'public');
            $data['image'] = $path;
        }
        if ($request->hasFile('business_logo')) {
            if ($employer->business_logo) {
                Storage::disk('public')->delete($employer->business_logo);
            }
            $path = $request->file('business_logo')->store('employers', 'public');
            $data['business_logo'] = $path;
        }
        $employer->update($data);
        $notifications->create(
            " ویرایش کارفرما",
            "کارفرما {$employer->full_name} در سیستم ویرایش  شد",
            "notification_users",
            ['employer' => $employer->id]
        );
        return response()->json([
            'success' => true,
            'message' => 'کارفرما ویرایش شد',
            'data'    => $employer
        ]);
    }

    // Delete article
    public function destroy($id, NotificationService $notifications)
    {
        $employer = Employer::find($id);

        if (!$employer) {
            return response()->json([
                'success' => false,
                'message' => 'کارفرما پیدا نشد',
            ], 404);
        }
        $exist = Portfolio::where('employer_id', $employer->id)->exists();
        if ($exist) {
            return response()->json([
                'success' => true,
                'message' => 'کارفرمادر یک نمونه کار استفاده شده و قابل حذف نیست',
            ], 403);
        }

        // Delete image if exists
        if ($employer->image) {
            Storage::disk('public')->delete($employer->image);
        }
        if ($employer->business_logo) {
            Storage::disk('public')->delete($employer->business_logo);
        }
        $notifications->create(
            " حذف کارفرما",
            "کارفرما {$employer->full_name} از سیستم حذف  شد",
            "notification_users",
            ['employer' => null]
        );
        $employer->delete();

        return response()->json([
            'success' => true,
            'message' => 'کارفرما با موفقیت ویرایش شد',
        ]);
    }
}
