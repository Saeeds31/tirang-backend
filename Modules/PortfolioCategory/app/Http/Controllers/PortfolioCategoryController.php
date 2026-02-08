<?php

namespace Modules\PortfolioCategory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Notifications\Services\NotificationService;
use Modules\Portfolio\Models\Portfolio;
use Modules\PortfolioCategory\Http\Requests\PortfolioCategoryStoreRequest;
use Modules\PortfolioCategory\Http\Requests\PortfolioCategoryUpdateRequest;
use Modules\PortfolioCategory\Models\PortfolioCategory;

class PortfolioCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = PortfolioCategory::query();
        if ($title = $request->get('title')) {
            $query->where('title_fa', 'like', "%{$title}%");
            $query->where('title_en', 'like', "%{$title}%");
        }
        $categories = $query->latest('id')->paginate(20);
        return response()->json([
            'success' => true,
            'message' => 'لیست دسته بندی',
            'data'    => $categories
        ]);
    }
    public function show($id)
    {
        $categories = PortfolioCategory::find($id);
        if (!$categories) {
            return response()->json([
                'success' => false,
                'message' => 'دسته بندی پیدا نشد',
            ], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'جزئیات دسته بندی',
            'data'    => $categories
        ]);
    }

    // Store new article
    public function store(PortfolioCategoryStoreRequest $request, NotificationService $notifications)
    {
        $data = $request->validated();
        if ($request->hasFile('icon')) {
            $path = $request->file('icon')->store('categories', 'public');
            $data['icon'] = $path;
        }
        $category = PortfolioCategory::create($data);

        $notifications->create(
            " ثبت دسته بندی",
            "دسته بندی {$category->title_fa} در سیستم ثبت  شد",
            "notification_content",
            ['portfolio-category' => $category->id]
        );
        return response()->json([
            'success' => true,
            'message' => 'دسته بندی با موفقیت ثبت شد',
            'data'    => $category
        ], 201);
    }
    // Update article
    public function update(
        PortfolioCategoryUpdateRequest $request,
        PortfolioCategory $category,
        NotificationService $notifications
    ) {
        $data = $request->validated();
        if ($request->hasFile('icon')) {
            if ($category->icon && Storage::disk('public')->exists($category->icon)) {
                Storage::disk('public')->delete($category->icon);
            }
            $path = $request->file('icon')->store('categories', 'public');
            $data['icon'] = $path;
        }
        $category->update($data);

        $notifications->create(
            "ویرایش دسته بندی",
            "دسته بندی {$category->title_fa} ویرایش شد",
            "notification_content",
            ['portfolio-category' => $category->id]
        );
        return response()->json([
            'success' => true,
            'message' => 'دسته بندی با موفقیت ویرایش شد',
            'data'    => $category
        ]);
    }


    // Delete article

    public function destroy(PortfolioCategory $category, NotificationService $notifications)
    {
        $exist = Portfolio::where('category_id')->exists();
        if ($exist) {
            return response()->json([
                'success' => true,
                'message' => 'دسته دارای نمونه کار است و قابل حذف نیست'
            ], 403);
        }
        if ($category->icon && Storage::disk('public')->exists($category->icon)) {
            Storage::disk('public')->delete($category->icon);
        }
        $category->delete();
        $notifications->create(
            "حذف دسته بندی",
            "دسته بندی {$category->title_fa} حذف شد",
            "notification_content",
            ['portfolio-category' => $category->id]
        );

        return response()->json([
            'success' => true,
            'message' => 'دسته بندی با موفقیت حذف شد'
        ]);
    }
}
