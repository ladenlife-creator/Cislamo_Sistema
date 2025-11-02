<?php


use App\Models\Settings\Tenant;
use App\Http\Controllers\API\V1\School\SchoolController;
use Illuminate\Support\Facades\Route;

// Endpoint público temporário para teste (remover em produção)
Route::get('/schools/public', [SchoolController::class, 'publicIndex']);
Route::post('/schools/public', [SchoolController::class, 'publicStore']);

// Endpoint público temporário para estudantes (remover em produção)
Route::get('/students/public', [\App\Http\Controllers\API\V1\Student\StudentController::class, 'publicIndex']);
Route::get('/students/public/{id}', [\App\Http\Controllers\API\V1\Student\StudentController::class, 'publicShow']);
Route::post('/students/public', [\App\Http\Controllers\API\V1\Student\StudentController::class, 'publicStore']);
Route::put('/students/public/{id}', [\App\Http\Controllers\API\V1\Student\StudentController::class, 'publicUpdate']);
Route::delete('/students/public/{id}', [\App\Http\Controllers\API\V1\Student\StudentController::class, 'publicDestroy']);


// Legacy auth routes for backward compatibility (without v1 prefix)
Route::prefix('auth')->group(function () {
    Route::post('sign-in', [\App\Http\Controllers\API\V1\Auth\AuthController::class, 'login']);
    Route::post('sign-up', [\App\Http\Controllers\API\V1\Auth\AuthController::class, 'register']);
    Route::post('forgot-password', [\App\Http\Controllers\API\V1\Auth\PasswordController::class, 'forgotPassword']);
    Route::post('reset-password', [\App\Http\Controllers\API\V1\Auth\PasswordController::class, 'reset']);
    Route::post('validate-token', [\App\Http\Controllers\API\V1\Auth\AuthController::class, 'validateToken']);

    Route::middleware('auth:api')->group(function () {
        Route::post('sign-out', [\App\Http\Controllers\API\V1\Auth\AuthController::class, 'logout']);
        Route::post('logout', [\App\Http\Controllers\API\V1\Auth\AuthController::class, 'logout']);
        Route::post('refresh', [\App\Http\Controllers\API\V1\Auth\AuthController::class, 'refresh']);
        Route::get('me', [\App\Http\Controllers\API\V1\Auth\AuthController::class, 'me']);
        Route::post('change-password', [\App\Http\Controllers\API\V1\Auth\PasswordController::class, 'change']);
    });
});

// Frontend compatibility routes (convert email to identifier)
Route::post('/login', function (\Illuminate\Http\Request $request) {
    $request->merge([
        'identifier' => $request->input('email', $request->input('identifier')),
        'type' => 'email'
    ]);
    return app(\App\Http\Controllers\API\V1\Auth\AuthController::class)->login($request);
});

Route::post('/register', function (\Illuminate\Http\Request $request) {
    $request->merge([
        'identifier' => $request->input('email', $request->input('identifier')),
        'type' => 'email'
    ]);
    return app(\App\Http\Controllers\API\V1\Auth\AuthController::class)->register($request);
});

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [\App\Http\Controllers\API\V1\Auth\AuthController::class, 'logout']);
    
    // Dashboard routes
    Route::get('/dashboard/stats', [\App\Http\Controllers\API\DashboardController::class, 'stats']);
    
    // Frontend compatibility routes (without v1 prefix)
    Route::get('/users', function(\Illuminate\Http\Request $request) {
        try {
            $users = \App\Models\User::select('id', 'name', 'identifier', 'email', 'created_at')
                ->orderBy('name')
                ->limit(100)
                ->get()
                ->map(function($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email ?? $user->identifier,
                        'identifier' => $user->identifier,
                        'created_at' => $user->created_at
                    ];
                });
            
            return response()->json($users->toArray());
        } catch (\Exception $e) {
            return response()->json(['data' => [], 'message' => $e->getMessage()], 500);
        }
    });
    
    Route::get('/events', function(\Illuminate\Http\Request $request) {
        try {
            $events = \App\Models\V1\Transport\StudentTransportEvent::withoutGlobalScopes()
                ->with([
                    'student:id,first_name,last_name',
                    'fleetBus:id,license_plate',
                    'busStop:id,name'
                ])
                ->orderBy('event_timestamp', 'desc')
                ->limit(100)
                ->get()
                ->map(function($event) {
                    return [
                        'id' => $event->id,
                        'title' => ucfirst(str_replace('_', ' ', $event->event_type ?? 'Evento de Transporte')),
                        'description' => $event->notes ?? 'Evento de transporte escolar',
                        'start_date' => $event->event_timestamp ? $event->event_timestamp->toIso8601String() : null,
                        'end_date' => $event->event_timestamp ? $event->event_timestamp->toIso8601String() : null,
                        'status' => 'scheduled',
                        'location' => $event->busStop ? $event->busStop->name : null,
                        'user' => [
                            'name' => $event->student ? ($event->student->first_name . ' ' . $event->student->last_name) : 'Sistema'
                        ]
                    ];
                });
            
            return response()->json($events->toArray());
        } catch (\Exception $e) {
            \Log::error('Events endpoint error: ' . $e->getMessage());
            return response()->json(['data' => [], 'message' => $e->getMessage()], 500);
        }
    });
    
    Route::get('/documents', function(\Illuminate\Http\Request $request) {
        try {
            $documents = \App\Models\V1\SIS\Student\StudentDocument::withoutGlobalScopes()
                ->with([
                    'student:id,first_name,last_name',
                    'uploader:id,name'
                ])
                ->orderBy('created_at', 'desc')
                ->limit(100)
                ->get()
                ->map(function($doc) {
                    return [
                        'id' => $doc->id,
                        'title' => $doc->document_name,
                        'description' => $doc->verification_notes ?? 'Sem descrição',
                        'category' => $doc->document_type ?? 'Outro',
                        'user' => [
                            'name' => $doc->uploader ? $doc->uploader->name : ($doc->student ? ($doc->student->first_name . ' ' . $doc->student->last_name) : 'Sistema')
                        ]
                    ];
                });
            
            return response()->json($documents->toArray());
        } catch (\Exception $e) {
            \Log::error('Documents endpoint error: ' . $e->getMessage());
            return response()->json(['data' => [], 'message' => $e->getMessage()], 500);
        }
    });
});

//v1 group
Route::prefix('v1')->group(function () {
    require __DIR__ . '/modules/auth.php';
    require __DIR__ . '/modules/users.php';
    require __DIR__ . '/modules/forms.php';
    require __DIR__ . '/modules/notification.php';
    require __DIR__ . '/modules/tenant.php';
    require __DIR__ . '/modules/school.php';
    require __DIR__ . '/modules/academic-years.php';
    require __DIR__ . '/modules/students.php';
    require __DIR__ . '/modules/roles_permission/roles.php';
    require __DIR__ . '/modules/academic/academic.php';
    require __DIR__ . '/modules/schedule/schedule.php';
    require __DIR__ . '/modules/library.php';
    require __DIR__ . '/modules/financial.php';
    require __DIR__ . '/modules/assessment.php';
});

// Transport Module Routes
Route::middleware(['api', 'throttle:api'])->group(function () {
    require_once __DIR__ . '/modules/transport/transport.php';
});





// File upload routes
Route::middleware(['auth:api', 'tenant'])->group(function () {
    Route::prefix('v1/files')->group(function () {
        Route::post('/upload', [\App\Http\Controllers\API\V1\FileUploadController::class, 'upload']);
        Route::post('/upload-multiple', [\App\Http\Controllers\API\V1\FileUploadController::class, 'uploadMultiple']);
        Route::delete('/delete', [\App\Http\Controllers\API\V1\FileUploadController::class, 'delete']);
        Route::get('/info', [\App\Http\Controllers\API\V1\FileUploadController::class, 'info']);
    });
});

// Additional route model bindings for transport
Route::bind('route', function ($value) {
    return \App\Models\V1\Transport\TransportRoute::findOrFail($value);
});

Route::bind('bus', function ($value) {
    return \App\Models\V1\Transport\FleetBus::findOrFail($value);
});

Route::bind('stop', function ($value) {
    return \App\Models\V1\Transport\BusStop::findOrFail($value);
});

Route::bind('subscription', function ($value) {
    return \App\Models\V1\Transport\StudentTransportSubscription::findOrFail($value);
});

//
Route::bind('incident', function ($value) {
    return \App\Models\V1\Transport\TransportIncident::findOrFail($value);
});


// 
Route::bind('event', function ($value) {
    return \App\Models\V1\Transport\StudentTransportEvent::findOrFail($value);
});

// Permission route model binding 
Route::bind('permission', function ($value) {
    return \Spatie\Permission\Models\Permission::findOrFail($value);
});
