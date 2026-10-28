<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter');

        return view('admin.reviews.index', [
            'reviews' => Review::with('product')
                ->when($filter === 'pending', fn ($q) => $q->where('is_approved', false))
                ->when($filter === 'approved', fn ($q) => $q->where('is_approved', true))
                ->latest()->paginate(20)->withQueryString(),
            'filter'  => $filter,
        ]);
    }

    public function toggle(Review $review)
    {
        $review->update(['is_approved' => ! $review->is_approved]);

        return back()->with('status', $review->is_approved ? 'Review published.' : 'Review unpublished.');
    }

    public function destroy(Review $review)
    {
        $review->delete();

        return back()->with('status', 'Review deleted.');
    }
}
