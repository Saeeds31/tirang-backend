<?php

namespace Modules\Portfolio\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Image\Models\Image;
use Modules\Notifications\Services\NotificationService;
use Modules\Portfolio\Http\Requests\PortfolioStoreRequest;
use Modules\Portfolio\Http\Requests\PortfolioUpdateRequest;
use Modules\Portfolio\Models\InstagramInfo;
use Modules\Portfolio\Models\Portfolio;
use Modules\PortfolioCategory\Models\PortfolioCategory;

class PortfolioController extends Controller
{
    public function index(Request $request)
    {
        $query = Portfolio::query()->with('category', 'employer');
        if ($title = $request->get('title')) {
            $query->where('title', 'like', "%{$title}%");
        }
        if ($category_id = $request->get('category_id')) {
            $query->where('category_id', $category_id);
        }
        if ($employer = $request->get('employer')) {
            $query->whereHas('employer', function ($q) use ($employer) {
                $q->where('title', 'like', "%$employer%");
            });
        }
        $portfolios = $query->latest('id')->paginate(20);
        return response()->json([
            'success' => true,
            'message' => 'لیست نمونه کار',
            'data'    => $portfolios
        ]);
    }
    public function indexFront(Request $request)
    {
        $query = Portfolio::query()->with('category', 'employer');
        if ($title = $request->get('title')) {
            $query->where('title', 'like', "%{$title}%");
        }
        $category = null;
        if ($category_id = $request->get('category_id')) {
            $category = PortfolioCategory::findOrFail($category_id);
            $query->where('category_id', $category_id);
        }
        $portfolios = $query->latest('id')->paginate(20);
        return response()->json([
            'success' => true,
            'category' => $category,
            'message' => 'لیست نمونه کار',
            'data'    => $portfolios
        ]);
    }
    public function show($id)
    {
        $portfolio = Portfolio::with('images', 'category', 'employer', 'instagramInfo')->find($id);
        if (!$portfolio) {
            return response()->json([
                'success' => false,
                'message' => 'نمونه کار پیدا نشد',
            ], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'جزئیات نمونه کار',
            'data'    => $portfolio
        ]);
    }

    public function showFront($id)
    {
        $portfolio = Portfolio::with('images', 'category', 'employer')->find($id);
        if (!$portfolio) {
            return response()->json([
                'success' => false,
                'message' => 'نمونه کار پیدا نشد',
            ], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'جزئیات نمونه کار',
            'data'    => $portfolio
        ]);
    }


    // Store new article
    public function store(PortfolioStoreRequest $request, NotificationService $notifications)
    {
        $data = $request->validated();

        // Handle main image
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('portfolios', 'public');
            $data['image'] = $path;
        }

        // Handle video
        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('portfolios-videos', 'public');
            $data['video'] = $videoPath;
        }

        // Create portfolio
        $portfolio = Portfolio::create($data);

        // Handle gallery images
        if ($request->hasFile('images')) {
            $imageIds = [];
            foreach ($request->file('images') as $file) {
                $path = $file->store('portfolio-gallery', 'public');
                $image = Image::create([
                    'path' => $path,
                    'alt'  => $portfolio->title,
                ]);
                $imageIds[] = $image->id;
            }
            $portfolio->images()->sync($imageIds);
        }

        // Handle Instagram Info - فقط اگر وجود داشته باشد
        if ($request->has('instagram_info') && is_array($request->instagram_info)) {
            $instagramData = $request->instagram_info;

            // Handle images
            $instagramFields = ['brand_logo', 'insta_base_image', 'first_image', 'second_image', 'third_image'];
            foreach ($instagramFields as $field) {
                if ($request->hasFile("instagram_info.{$field}")) {
                    $file = $request->file("instagram_info.{$field}");
                    $path = $file->store('instagram-info', 'public');
                    $instagramData[$field] = $path;
                }
            }

            // فقط اگر داده‌ای وجود داشت ذخیره کن
            $hasData = false;
            foreach ($instagramData as $key => $value) {
                if ($key !== 'portfolio_id' && !empty($value)) {
                    $hasData = true;
                    break;
                }
            }

            if ($hasData) {
                $instagramData['portfolio_id'] = $portfolio->id;
                InstagramInfo::create($instagramData);
            }
        }

        // Create notification
        $notifications->create(
            "ثبت نمونه کار",
            "نمونه کار {$portfolio->title} در سیستم ثبت شد",
            "notification_content",
            ['portfolio' => $portfolio->id]
        );

        return response()->json([
            'success' => true,
            'message' => 'نمونه کار با موفقیت ثبت شد',
            'data'    => $portfolio->load('instagramInfo')
        ], 201);
    }

    // Update article
    public function update(PortfolioUpdateRequest $request, $id, NotificationService $notifications)
    {
        $portfolio = Portfolio::with('instagramInfo')->findOrFail($id);
        $data = $request->validated();

        // Update main image
        if ($request->hasFile('image')) {
            if ($portfolio->image) {
                Storage::disk('public')->delete($portfolio->image);
            }
            $path = $request->file('image')->store('portfolios', 'public');
            $data['image'] = $path;
        }

        // Update video
        if ($request->hasFile('video')) {
            if ($portfolio->video) {
                Storage::disk('public')->delete($portfolio->video);
            }
            $videoPath = $request->file('video')->store('portfolios-videos', 'public');
            $data['video'] = $videoPath;
        }

        $portfolio->update($data);

        // Update gallery images
        if ($request->hasFile('images')) {
            $imageIds = [];
            foreach ($request->file('images') as $file) {
                $path = $file->store('portfolio-gallery', 'public');
                $image = Image::create([
                    'path' => $path,
                    'alt'  => $portfolio->title,
                ]);
                $imageIds[] = $image->id;
            }
            $portfolio->images()->syncWithoutDetaching($imageIds);
        }

        // Handle Instagram Info
        if ($request->has('instagram_info')) {
            if ($request->instagram_info === null) {
                // حذف اطلاعات اینستاگرام
                if ($portfolio->instagramInfo) {
                    // حذف فایل‌های مربوطه
                    $instagramFields = ['brand_logo', 'insta_base_image', 'first_image', 'second_image', 'third_image'];
                    foreach ($instagramFields as $field) {
                        if ($portfolio->instagramInfo->$field) {
                            Storage::disk('public')->delete($portfolio->instagramInfo->$field);
                        }
                    }
                    $portfolio->instagramInfo()->delete();
                }
            } else {
                $instagramData = $request->instagram_info;

                // Handle images
                $instagramFields = ['brand_logo', 'insta_base_image', 'first_image', 'second_image', 'third_image'];
                foreach ($instagramFields as $field) {
                    if ($request->hasFile("instagram_info.{$field}")) {
                        // حذف فایل قدیمی
                        if ($portfolio->instagramInfo && $portfolio->instagramInfo->$field) {
                            Storage::disk('public')->delete($portfolio->instagramInfo->$field);
                        }
                        $file = $request->file("instagram_info.{$field}");
                        $path = $file->store('instagram-info', 'public');
                        $instagramData[$field] = $path;
                    }
                }

                // Update or create Instagram info
                if ($portfolio->instagramInfo) {
                    $portfolio->instagramInfo->update($instagramData);
                } else {
                    $instagramData['portfolio_id'] = $portfolio->id;
                    InstagramInfo::create($instagramData);
                }
            }
        }

        $notifications->create(
            "ویرایش نمونه کار",
            "نمونه کار {$portfolio->title} در سیستم ویرایش شد",
            "notification_content",
            ['portfolio' => $portfolio->id]
        );

        return response()->json([
            'success' => true,
            'message' => 'نمونه کار با موفقیت ویرایش شد',
            'data'    => $portfolio->load('instagramInfo')
        ], 200);
    }

    public function destroy($id, NotificationService $notifications)
    {
        $portfolio = Portfolio::with('instagramInfo', 'images')->findOrFail($id);

        // حذف تصویر اصلی
        if ($portfolio->image && Storage::disk('public')->exists($portfolio->image)) {
            Storage::disk('public')->delete($portfolio->image);
        }

        // حذف ویدیو
        if ($portfolio->video && Storage::disk('public')->exists($portfolio->video)) {
            Storage::disk('public')->delete($portfolio->video);
        }

        // حذف تصاویر گالری
        foreach ($portfolio->images as $image) {
            if (Storage::disk('public')->exists($image->path)) {
                Storage::disk('public')->delete($image->path);
            }
            $image->delete();
        }

        // حذف اطلاعات اینستاگرام و فایل‌های مربوطه
        if ($portfolio->instagramInfo) {
            $instagramFields = [
                'brand_logo',
                'insta_base_image',
                'first_image',
                'second_image',
                'third_image'
            ];

            foreach ($instagramFields as $field) {
                if (
                    $portfolio->instagramInfo->$field &&
                    Storage::disk('public')->exists($portfolio->instagramInfo->$field)
                ) {
                    Storage::disk('public')->delete($portfolio->instagramInfo->$field);
                }
            }

            $portfolio->instagramInfo()->delete();
        }

        // حذف نمونه کار
        $portfolio->delete();

        $notifications->create(
            "حذف نمونه کار",
            "نمونه کار {$portfolio->title} حذف شد",
            "notification_content",
            ['portfolio' => $portfolio->id]
        );

        return response()->json([
            'success' => true,
            'message' => 'نمونه کار با موفقیت حذف شد'
        ]);
    }
    public function destroyImage(Request $request, $portfolioId)
    {
        $imageId = $request->imageId;
        $portfolio = Portfolio::findOrFail($portfolioId);
        $image = $portfolio->images()->where('images.id', $imageId)->firstOrFail();

        if (Storage::disk('public')->exists($image->path)) {
            Storage::disk('public')->delete($image->path);
        }

        $portfolio->images()->detach($image->id);

        /**
         * delete image record
         */
        $image->delete();

        return response()->json([
            'success' => true,
            'message' => 'تصویر با موفقیت حذف شد'
        ]);
    }
}
