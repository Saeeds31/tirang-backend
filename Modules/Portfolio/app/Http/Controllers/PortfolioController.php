<?php

namespace Modules\Portfolio\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Image\Models\Image;
use Modules\Notifications\Services\NotificationService;
use Modules\Portfolio\Http\Requests\PortfolioStoreRequest;
use Modules\Portfolio\Http\Requests\PortfolioUpdateRequest;
use Modules\Portfolio\Models\Portfolio;

class PortfolioController extends Controller
{
    public function index(Request $request)
    {
        $query = Portfolio::with('category', 'employer')->query();
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
    public function show($id)
    {
        $portfolio = Portfolio::find($id);
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
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('portfolios', 'public');
            $data['image'] = $path;
        }
        $portfolio = Portfolio::create($data);
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
            // attach images to portfolio
            $portfolio->images()->sync($imageIds);
        }
        $notifications->create(
            " ثبت نمونه کار",
            "نمونه کار {$portfolio->title} در سیستم ثبت  شد",
            "notification_content",
            ['portfolio' => $portfolio->id]
        );
        return response()->json([
            'success' => true,
            'message' => 'نمونه کار با موفقیت ثبت شد',
            'data'    => $portfolio
        ], 201);
    }
    // Update article
    public function update(
        PortfolioUpdateRequest $request,
        Portfolio $portfolio,
        NotificationService $notifications
    ) {
        $data = $request->validated();
        if ($request->hasFile('image')) {
            if ($portfolio->image && Storage::disk('public')->exists($portfolio->image)) {
                Storage::disk('public')->delete($portfolio->image);
            }
            $path = $request->file('image')->store('portfolios', 'public');
            $data['image'] = $path;
        }
        $portfolio->update($data);
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
        $notifications->create(
            "ویرایش نمونه کار",
            "نمونه کار {$portfolio->title} ویرایش شد",
            "notification_content",
            ['portfolio' => $portfolio->id]
        );
        return response()->json([
            'success' => true,
            'message' => 'نمونه کار با موفقیت ویرایش شد',
            'data'    => $portfolio->load(['images', 'category', 'employer'])
        ]);
    }


    // Delete article

    public function destroy(Portfolio $portfolio, NotificationService $notifications)
    {
        if ($portfolio->image && Storage::disk('public')->exists($portfolio->image)) {
            Storage::disk('public')->delete($portfolio->image);
        }
        foreach ($portfolio->images as $image) {
            if (Storage::disk('public')->exists($image->path)) {
                Storage::disk('public')->delete($image->path);
            }
            $image->delete();
        }
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
    public function destroyImage($portfolioId, $imageId)
    {
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
