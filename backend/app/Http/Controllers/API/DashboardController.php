<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function stats(Request $request)
    {
        $user = auth('api')->user();
        
        // Get users count
        $usersCount = User::count();
        
        // Get documents count from generic 'documents' table
        $documentsCount = DB::table('documents')->count();
        
        // Get events count from generic 'events' table
        $eventsCount = DB::table('events')->count();
        
        // Get recent documents (last 5) from generic 'documents' table
        $recentDocuments = DB::table('documents')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($doc) {
                return [
                    'id' => $doc->id,
                    'title' => $doc->title,
                    'description' => $doc->description ?? 'Sem descrição',
                    'created_at' => $doc->created_at,
                    'file_path' => $doc->file_path ?? null,
                    'file_type' => $doc->file_type ?? null
                ];
            });
        
        // Get upcoming events (next 5) from generic 'events' table
        $upcomingEvents = DB::table('events')
            ->where('start_date', '>=', now())
            ->orderBy('start_date', 'asc')
            ->limit(5)
            ->get()
            ->map(function($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'start_date' => $event->start_date,
                    'description' => $event->description ?? 'Sem descrição'
                ];
            });
        
        $stats = [
            'users_count' => $usersCount,
            'documents_count' => $documentsCount,
            'events_count' => $eventsCount,
            'recent_documents' => $recentDocuments,
            'upcoming_events' => $upcomingEvents,
        ];
        
        return response()->json($stats);
    }
}
