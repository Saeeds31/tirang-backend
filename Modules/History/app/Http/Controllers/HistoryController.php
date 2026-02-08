<?php

namespace Modules\History\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\History\Http\Requests\HistoryStoreRequest;
use Modules\History\Http\Requests\HistoryUpdateRequest;
use Modules\History\Models\History;
use Modules\Notifications\Services\NotificationService;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = History::query();
        if ($title = $request->get('title')) {
            $query->where('title', 'like', "%{$title}%");
        }
        $histories = $query->latest('date')->get();
        return response()->json([
            'success' => true,
            'message' => 'لیست تاریخچه',
            'data'    => $histories
        ]);
    }

    // Show single article
    public function show($id)
    {
        $history = History::find($id);

        if (!$history) {
            return response()->json([
                'success' => false,
                'message' => 'تاریخچه پیدا نشد',
            ], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'جزئیات تاریخچه',
            'data'    => $history
        ]);
    }

    // Store new article
    public function store(HistoryStoreRequest $request, NotificationService $notifications)
    {
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('histories', 'public');
            $data['image'] = $path;
        }
        $history = History::create($data);
        $notifications->create(
            " ثبت تاریخچه",
            "تاریخچه {$history->title} در سیستم ثبت  شد",
            "notification_content",
            ['history' => $history->id]
        );
        return response()->json([
            'success' => true,
            'message' => 'تاریخچه با موفقیت ثبت شد',
            'data'    => $history
        ], 201);
    }


    // Update article
    public function update(HistoryUpdateRequest $request, $id, NotificationService $notifications)
    {
        $history = History::findOrFail($id);
        if (!$history) {
            return response()->json([
                'success' => false,
                'message' => 'تاریخچه پیدا نشد',
            ], 404);
        }
        $data = $request->validated();
        if ($request->hasFile('image')) {
            if ($history->image) {
                Storage::disk('public')->delete($history->image);
            }
            $path = $request->file('image')->store('histories', 'public');
            $data['image'] = $path;
        }
        $history->update($data);
        $notifications->create(
            " ویرایش تاریخچه",
            "تاریخچه {$history->title} در سیستم ویرایش  شد",
            "notification_content",
            ['history' => $history->id]
        );
        return response()->json([
            'success' => true,
            'message' => 'تاریخچه ویرایش شد',
            'data'    => $history
        ]);
    }

    // Delete article
    public function destroy($id, NotificationService $notifications)
    {
        $history = History::find($id);

        if (!$history) {
            return response()->json([
                'success' => false,
                'message' => 'تاریخچه پیدا نشد',
            ], 404);
        }

        // Delete image if exists
        if ($history->image) {
            Storage::disk('public')->delete($history->image);
        }
        $notifications->create(
            " حذف تاریخچه",
            "تاریخچه {$history->title} از سیستم حذف  شد",
            "notification_content",
            ['history' => null]
        );
        $history->delete();

        return response()->json([
            'success' => true,
            'message' => 'تاریخچه با موفقیت ویرایش شد',
        ]);
    }
}
