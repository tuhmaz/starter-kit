<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ActivityController extends Controller
{
    public function index()
    {
        $activities = $this->getActivities(1);
        return view('content.dashboard.activities.index', compact('activities'));
    }

    public function loadMore(Request $request)
    {
        $page = $request->input('page', 1);
        $activities = $this->getActivities($page);
        
        return response()->json([
            'html' => view('content.dashboard.activities._list', compact('activities'))->render(),
            'hasMore' => count($activities) === 20
        ]);
    }

    private function getActivities($page)
    {
        // This is a placeholder implementation. Replace with your actual activity retrieval logic
        $activities = collect(range(1, 20))->map(function ($i) use ($page) {
            $offset = ($page - 1) * 20;
            return [
                'type' => collect(['article', 'news', 'comment'])->random(),
                'icon' => collect(['bx-news', 'bx-broadcast', 'bx-comment'])->random(),
                'action' => "Activity " . ($i + $offset),
                'description' => "Description for activity " . ($i + $offset),
                'time' => Carbon::now()->subHours(rand(1, 24)),
                'user' => 'User ' . rand(1, 10),
                'user_avatar' => null
            ];
        });

        return $activities;
    }
}
