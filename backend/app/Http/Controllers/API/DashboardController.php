<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function stats(Request $request)
    {
        $user = auth('api')->user();
        
        // Get users count (scoped to tenant if applicable)
        $usersCount = User::count();
        
        // For now, return simple counts
        // You can enhance this later to include tenant/scoped queries
        $stats = [
            'users_count' => $usersCount,
            'documents_count' => 0, // TODO: Implement when documents model exists
            'events_count' => 0, // TODO: Implement when events model exists
            'recent_documents' => [],
            'upcoming_events' => [],
        ];
        
        return response()->json($stats);
    }
}
