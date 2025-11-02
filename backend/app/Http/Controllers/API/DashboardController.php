<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\V1\Transport\StudentTransportEvent;
use App\Models\V1\SIS\Student\StudentDocument;

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
        
        // Get documents count
        $documentsCount = StudentDocument::count();
        
        // Get events count
        $eventsCount = StudentTransportEvent::count();
        
        // Get recent documents (last 5)
        $recentDocuments = StudentDocument::with(['student:id,first_name,last_name'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($doc) {
                return [
                    'title' => $doc->document_name,
                    'description' => $doc->verification_notes ?? 'Sem descrição',
                    'created_at' => $doc->created_at
                ];
            });
        
        // Get upcoming events (next 5)
        $upcomingEvents = StudentTransportEvent::where('event_timestamp', '>=', now())
            ->orderBy('event_timestamp', 'asc')
            ->limit(5)
            ->get()
            ->map(function($event) {
                return [
                    'title' => ucfirst(str_replace('_', ' ', $event->event_type ?? 'Evento de Transporte')),
                    'start_date' => $event->event_timestamp,
                    'description' => $event->notes ?? 'Evento de transporte escolar'
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
