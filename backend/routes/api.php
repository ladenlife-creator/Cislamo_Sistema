<?php


use App\Models\Settings\Tenant;
use App\Http\Controllers\API\V1\School\SchoolController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

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
    
    // Events routes
    Route::get('/events', function(\Illuminate\Http\Request $request) {
        try {
            // Buscar eventos da tabela genérica 'events'
            $events = DB::table('events')
                ->leftJoin('users', 'events.user_id', '=', 'users.id')
                ->select(
                    'events.id',
                    'events.title',
                    'events.description',
                    'events.start_date',
                    'events.end_date',
                    'events.location',
                    'events.status',
                    'events.created_at',
                    'users.name as user_name'
                )
                ->orderBy('events.start_date', 'desc')
                ->limit(100)
                ->get()
                ->map(function($event) {
                    return [
                        'id' => $event->id,
                        'title' => $event->title,
                        'description' => $event->description ?? 'Sem descrição',
                        'start_date' => $event->start_date ? \Carbon\Carbon::parse($event->start_date)->toIso8601String() : null,
                        'end_date' => $event->end_date ? \Carbon\Carbon::parse($event->end_date)->toIso8601String() : null,
                        'status' => $event->status ?? 'scheduled',
                        'location' => $event->location ?? null,
                        'user' => [
                            'name' => $event->user_name ?? 'Sistema'
                        ]
                    ];
                });
            
            return response()->json($events->toArray());
        } catch (\Exception $e) {
            \Log::error('Events endpoint error: ' . $e->getMessage());
            return response()->json(['data' => [], 'message' => $e->getMessage()], 500);
        }
    });
    
    Route::post('/events', function(\Illuminate\Http\Request $request) {
        try {
            $user = $request->user();
            
            // Criar evento na tabela events
            $eventId = DB::table('events')->insertGetId([
                'title' => $request->input('title', 'Novo Evento'),
                'description' => $request->input('description', ''),
                'start_date' => $request->input('start_date') ? \Carbon\Carbon::parse($request->input('start_date'))->format('Y-m-d H:i:s') : now(),
                'end_date' => $request->input('end_date') ? \Carbon\Carbon::parse($request->input('end_date'))->format('Y-m-d H:i:s') : now(),
                'status' => $request->input('status', 'scheduled'),
                'location' => $request->input('location', ''),
                'user_id' => $user->id ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            $event = DB::table('events')
                ->leftJoin('users', 'events.user_id', '=', 'users.id')
                ->where('events.id', $eventId)
                ->select(
                    'events.id',
                    'events.title',
                    'events.description',
                    'events.start_date',
                    'events.end_date',
                    'events.location',
                    'events.status',
                    'events.created_at',
                    'users.name as user_name'
                )
                ->first();
            
            $response = [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description ?? 'Sem descrição',
                'start_date' => $event->start_date ? \Carbon\Carbon::parse($event->start_date)->toIso8601String() : null,
                'end_date' => $event->end_date ? \Carbon\Carbon::parse($event->end_date)->toIso8601String() : null,
                'status' => $event->status ?? 'scheduled',
                'location' => $event->location ?? null,
                'user' => [
                    'name' => $event->user_name ?? 'Sistema'
                ]
            ];
            
            return response()->json($response, 201);
        } catch (\Exception $e) {
            \Log::error('Events create error: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    });
    
    Route::put('/events/{id}', function(\Illuminate\Http\Request $request, $id) {
        try {
            DB::table('events')
                ->where('id', $id)
                ->update([
                    'title' => $request->input('title'),
                    'description' => $request->input('description'),
                    'start_date' => $request->input('start_date') ? \Carbon\Carbon::parse($request->input('start_date'))->format('Y-m-d H:i:s') : null,
                    'end_date' => $request->input('end_date') ? \Carbon\Carbon::parse($request->input('end_date'))->format('Y-m-d H:i:s') : null,
                    'status' => $request->input('status'),
                    'location' => $request->input('location'),
                    'updated_at' => now()
                ]);
            
            $event = DB::table('events')
                ->leftJoin('users', 'events.user_id', '=', 'users.id')
                ->where('events.id', $id)
                ->select(
                    'events.id',
                    'events.title',
                    'events.description',
                    'events.start_date',
                    'events.end_date',
                    'events.location',
                    'events.status',
                    'events.created_at',
                    'users.name as user_name'
                )
                ->first();
            
            if (!$event) {
                return response()->json(['message' => 'Evento não encontrado'], 404);
            }
            
            $response = [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description ?? 'Sem descrição',
                'start_date' => $event->start_date ? \Carbon\Carbon::parse($event->start_date)->toIso8601String() : null,
                'end_date' => $event->end_date ? \Carbon\Carbon::parse($event->end_date)->toIso8601String() : null,
                'status' => $event->status ?? 'scheduled',
                'location' => $event->location ?? null,
                'user' => [
                    'name' => $event->user_name ?? 'Sistema'
                ]
            ];
            
            return response()->json($response);
        } catch (\Exception $e) {
            \Log::error('Events update error: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    });
    
    Route::delete('/events/{id}', function(\Illuminate\Http\Request $request, $id) {
        try {
            $deleted = DB::table('events')->where('id', $id)->delete();
            
            if ($deleted) {
                return response()->json(['message' => 'Evento removido com sucesso'], 200);
            } else {
                return response()->json(['message' => 'Evento não encontrado'], 404);
            }
        } catch (\Exception $e) {
            \Log::error('Events delete error: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    });
    
    // Documents routes
    Route::get('/documents', function(\Illuminate\Http\Request $request) {
        try {
            // Buscar documentos da tabela genérica 'documents'
            $documents = DB::table('documents')
                ->leftJoin('users', 'documents.user_id', '=', 'users.id')
                ->select(
                    'documents.id',
                    'documents.title',
                    'documents.description',
                    'documents.category',
                    'documents.file_path',
                    'documents.file_type',
                    'documents.file_size',
                    'documents.created_at',
                    'users.name as user_name'
                )
                ->orderBy('documents.created_at', 'desc')
                ->limit(100)
                ->get()
                ->map(function($doc) {
                    return [
                        'id' => $doc->id,
                        'title' => $doc->title,
                        'description' => $doc->description ?? 'Sem descrição',
                        'category' => $doc->category ?? 'Outro',
                        'file_path' => $doc->file_path ?? null,
                        'file_type' => $doc->file_type ?? null,
                        'file_size' => $doc->file_size ?? null,
                        'user' => [
                            'name' => $doc->user_name ?? 'Sistema'
                        ]
                    ];
                });
            
            return response()->json($documents->toArray());
        } catch (\Exception $e) {
            \Log::error('Documents endpoint error: ' . $e->getMessage());
            return response()->json(['data' => [], 'message' => $e->getMessage()], 500);
        }
    });
    
    Route::post('/documents', function(\Illuminate\Http\Request $request) {
        try {
            $user = $request->user();
            
            // Criar documento na tabela documents
            $documentId = DB::table('documents')->insertGetId([
                'title' => $request->input('title', 'Novo Documento'),
                'description' => $request->input('description', ''),
                'category' => $request->input('category', 'Outro'),
                'file_path' => $request->input('file_path', null),
                'file_type' => $request->input('file_type', null),
                'file_size' => $request->input('file_size', null),
                'user_id' => $user->id ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            $document = DB::table('documents')
                ->leftJoin('users', 'documents.user_id', '=', 'users.id')
                ->where('documents.id', $documentId)
                ->select(
                    'documents.id',
                    'documents.title',
                    'documents.description',
                    'documents.category',
                    'documents.file_path',
                    'documents.file_type',
                    'documents.file_size',
                    'documents.created_at',
                    'users.name as user_name'
                )
                ->first();
            
            $response = [
                'id' => $document->id,
                'title' => $document->title,
                'description' => $document->description ?? 'Sem descrição',
                'category' => $document->category ?? 'Outro',
                'file_path' => $document->file_path ?? null,
                'file_type' => $document->file_type ?? null,
                'file_size' => $document->file_size ?? null,
                'user' => [
                    'name' => $document->user_name ?? 'Sistema'
                ]
            ];
            
            return response()->json($response, 201);
        } catch (\Exception $e) {
            \Log::error('Documents create error: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    });
    
    Route::put('/documents/{id}', function(\Illuminate\Http\Request $request, $id) {
        try {
            DB::table('documents')
                ->where('id', $id)
                ->update([
                    'title' => $request->input('title'),
                    'description' => $request->input('description'),
                    'category' => $request->input('category'),
                    'file_path' => $request->input('file_path'),
                    'file_type' => $request->input('file_type'),
                    'file_size' => $request->input('file_size'),
                    'updated_at' => now()
                ]);
            
            $document = DB::table('documents')
                ->leftJoin('users', 'documents.user_id', '=', 'users.id')
                ->where('documents.id', $id)
                ->select(
                    'documents.id',
                    'documents.title',
                    'documents.description',
                    'documents.category',
                    'documents.file_path',
                    'documents.file_type',
                    'documents.file_size',
                    'documents.created_at',
                    'users.name as user_name'
                )
                ->first();
            
            if (!$document) {
                return response()->json(['message' => 'Documento não encontrado'], 404);
            }
            
            $response = [
                'id' => $document->id,
                'title' => $document->title,
                'description' => $document->description ?? 'Sem descrição',
                'category' => $document->category ?? 'Outro',
                'file_path' => $document->file_path ?? null,
                'file_type' => $document->file_type ?? null,
                'file_size' => $document->file_size ?? null,
                'user' => [
                    'name' => $document->user_name ?? 'Sistema'
                ]
            ];
            
            return response()->json($response);
        } catch (\Exception $e) {
            \Log::error('Documents update error: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    });
    
    Route::delete('/documents/{id}', function(\Illuminate\Http\Request $request, $id) {
        try {
            $deleted = DB::table('documents')->where('id', $id)->delete();
            
            if ($deleted) {
                return response()->json(['message' => 'Documento removido com sucesso'], 200);
            } else {
                return response()->json(['message' => 'Documento não encontrado'], 404);
            }
        } catch (\Exception $e) {
            \Log::error('Documents delete error: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
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
