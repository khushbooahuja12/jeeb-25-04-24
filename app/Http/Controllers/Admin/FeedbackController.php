<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use App\Model\UserFeedback;
use App\Model\OrderReview;
use Str;

class FeedbackController extends CoreApiController {

    public function reviews(Request $request) {
        $filter = $request->query('filter');
        $ratingKeys = ['products_quality', 'delivery_experience', 'overall_satisfaction'];
    
        // Initialize the query builder for the reviews
        $query = OrderReview::orderBy('id', 'desc');
    
        // Apply filters based on the request parameters
        if (!empty($filter)) {
            $query->where('review', 'like', '%' . $filter . '%');
        }
    
        foreach ($ratingKeys as $key) {
            $rating = $request->input($key);
    
            if (!empty($rating)) {
                if (Str::contains($rating, '-and-less')) {
                    // Split the string using '-' as the delimiter and get the first part
                    $splitRating = explode('-', $rating)[0];
                    // Now, $splitRating contains the value before '-and-less'
                    $query->where(function($query) use ($key, $splitRating) {
                        $query->where($key, '<=', $splitRating);
                    });
                } else {
                    $query->where(function($query) use ($key, $rating) {
                        $query->where($key, '=', $rating);
                    });
                }
            }
        }
    
        // Retrieve the paginated results
        $reviews = $query->paginate(20);
    
        $params = ['filter' => $filter];
        foreach ($ratingKeys as $key) {
            $params[$key] = $request->input($key);
        }
    
        $reviews->appends($params);
    
        return view('admin.reviews_and_feedback.reviews', compact('reviews', 'filter', 'ratingKeys','params'));
    }

    public function feedback(Request $request) {
        $filter = $request->query('filter');

        if (!empty($filter)) {
            $feedbacks = UserFeedback::where('experience', 'like', '%' . $filter . '%')
                    ->orWhere('description', 'like', '%' . $filter . '%')
                    ->orderBy('id', 'desc')
                    ->paginate(20);
        } else {
            $feedbacks = UserFeedback::orderBy('id', 'desc')
                    ->paginate(20);
        }
        $feedbacks->appends(['filter' => $filter]);

        return view('admin.reviews_and_feedback.feedback', [
            'feedbacks' => $feedbacks,
            'filter' => $filter
        ]);
    }

}
