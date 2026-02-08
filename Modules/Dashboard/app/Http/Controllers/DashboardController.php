<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Comments\Models\Comment;
use Modules\Course\Models\Course;
use Modules\CourseOrder\Models\CourseOrder;
use Modules\Orders\Models\Order;
use Modules\Products\Models\Product;
use Modules\Users\Models\User;

class DashboardController extends Controller
{

    public function dashboard()
    {

        return response()->json(
            [
                'message' => 'dashboard content',
                'success' => true,
                'data' => [
                ]
            ]
        );
    }
}
