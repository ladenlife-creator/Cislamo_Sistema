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
    
    try {
        return app(\App\Http\Controllers\API\V1\Auth\AuthController::class)->login($request);
    } catch (\Illuminate\Validation\ValidationException $e) {
        // Retornar erro mais amigável
        return response()->json([
            'message' => 'Credenciais inválidas. Verifique seu email e senha.',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        \Log::error('Login error: ' . $e->getMessage());
        
        // Mensagem mais clara para erro de conexão MySQL
        $errorMessage = $e->getMessage();
        $userMessage = 'Erro ao fazer login. Tente novamente.';
        
        if (strpos($errorMessage, 'Connection refused') !== false || 
            strpos($errorMessage, 'SQLSTATE[HY000] [2002]') !== false) {
            $userMessage = 'MySQL não está rodando. Por favor, execute no terminal: sudo systemctl start mysql';
        } elseif (strpos($errorMessage, 'SQLSTATE[HY000]') !== false) {
            $userMessage = 'Erro de conexão com o banco de dados. Verifique se o MySQL está rodando.';
        }
        
        return response()->json([
            'message' => $userMessage,
            'error' => $errorMessage
        ], 500);
    }
});

Route::post('/register', function (\Illuminate\Http\Request $request) {
    $request->merge([
        'identifier' => $request->input('email', $request->input('identifier')),
        'type' => 'email'
    ]);
    return app(\App\Http\Controllers\API\V1\Auth\AuthController::class)->register($request);
});

// Rota para servir arquivos de documentos (pública mas com verificação básica)
Route::get('/documents/file/{path}', function(\Illuminate\Http\Request $request, $path) {
    try {
        $decodedPath = urldecode($path);
        
        // Remover barra inicial se existir
        $decodedPath = ltrim($decodedPath, '/');
        
        // Verificar se o arquivo existe no storage público
        $fullPath = storage_path('app/public/' . $decodedPath);
        
        if (!file_exists($fullPath)) {
            // Tentar em public/documents
            $fullPath = public_path('documents/' . basename($decodedPath));
        }
        
        if (!file_exists($fullPath)) {
            // Tentar apenas o nome do arquivo em storage/app/public
            $fullPath = storage_path('app/public/' . basename($decodedPath));
        }
        
        if (!file_exists($fullPath)) {
            return response()->json(['message' => 'Arquivo não encontrado'], 404);
        }
        
        // Verificar se é um arquivo válido (não permitir acesso a diretórios)
        if (!is_file($fullPath)) {
            return response()->json(['message' => 'Arquivo inválido'], 400);
        }
        
        // Determinar content-type
        $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';
        
        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'attachment; filename="' . basename($fullPath) . '"'
        ]);
    } catch (\Exception $e) {
        \Log::error('Document file serve error: ' . $e->getMessage());
        return response()->json(['message' => $e->getMessage()], 500);
    }
})->where('path', '.*');

// Rota pública para listar eventos (para página home)
Route::get('/events/public', function(\Illuminate\Http\Request $request) {
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
        \Log::error('Events public endpoint error: ' . $e->getMessage());
        return response()->json(['data' => [], 'message' => $e->getMessage()], 500);
    }
});

// Rota pública para listar eventos com contagem de participantes
Route::get('/events/with-participants', function(\Illuminate\Http\Request $request) {
    try {
        $events = DB::table('events')
            ->leftJoin('users', 'events.user_id', '=', 'users.id')
            ->leftJoin('event_participants', 'events.id', '=', 'event_participants.event_id')
            ->select(
                'events.id',
                'events.title',
                'events.description',
                'events.start_date',
                'events.end_date',
                'events.location',
                'events.status',
                'events.meeting_code',
                'events.meeting_link',
                'events.created_at',
                'users.name as user_name',
                DB::raw('COUNT(DISTINCT event_participants.id) as participants_count')
            )
            ->groupBy(
                'events.id',
                'events.title',
                'events.description',
                'events.start_date',
                'events.end_date',
                'events.location',
                'events.status',
                'events.meeting_code',
                'events.meeting_link',
                'events.created_at',
                'users.name'
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
                    'meeting_code' => $event->meeting_code ?? null,
                    'meeting_link' => $event->meeting_link ?? null,
                    'participants_count' => (int)$event->participants_count,
                    'user' => [
                        'name' => $event->user_name ?? 'Sistema'
                    ]
                ];
            });
        
        return response()->json($events->toArray());
    } catch (\Exception $e) {
        \Log::error('Events with participants endpoint error: ' . $e->getMessage());
        return response()->json(['data' => [], 'message' => $e->getMessage()], 500);
    }
});

// Rota pública para obter estatísticas gerais
Route::get('/statistics/public', function(\Illuminate\Http\Request $request) {
    try {
        // Total de eventos
        $eventsCount = DB::table('events')->count();
        
        // Total de participantes (contar todos os registros de participação)
        $participantsCount = DB::table('event_participants')->count();
        
        // Total de organizações (usuários ou escolas - usando usuários por enquanto)
        $organizationsCount = DB::table('users')->count();
        
        // Se existir tabela de escolas, usar ela
        if (DB::getSchemaBuilder()->hasTable('schools')) {
            $organizationsCount = DB::table('schools')->count();
        }
        
        // Satisfação - pode ser calculado baseado em avaliações ou usar um valor padrão
        // Por enquanto, vamos calcular baseado em eventos concluídos vs total
        $completedEvents = DB::table('events')
            ->where('status', 'completed')
            ->orWhere('status', 'finished')
            ->count();
        
        $satisfaction = 98; // Valor padrão
        if ($eventsCount > 0) {
            // Calcular satisfação baseado em eventos concluídos
            $satisfaction = min(100, round(($completedEvents / $eventsCount) * 100));
            // Se não houver eventos concluídos, usar valor padrão baseado em participantes
            if ($completedEvents == 0 && $participantsCount > 0) {
                $satisfaction = 95; // Valor estimado
            }
        }
        
        return response()->json([
            'events_count' => $eventsCount,
            'participants_count' => $participantsCount,
            'organizations_count' => $organizationsCount,
            'satisfaction_percentage' => $satisfaction
        ]);
    } catch (\Exception $e) {
        \Log::error('Statistics public endpoint error: ' . $e->getMessage());
        return response()->json([
            'events_count' => 0,
            'participants_count' => 0,
            'organizations_count' => 0,
            'satisfaction_percentage' => 0
        ], 500);
    }
});

// Rota pública para obter código de meeting do evento
Route::get('/events/{id}/meeting-code', function(\Illuminate\Http\Request $request, $id) {
    try {
        $event = DB::table('events')->where('id', $id)->first();
        
        if (!$event) {
            return response()->json(['message' => 'Evento não encontrado'], 404);
        }
        
        // Retornar o código de meeting do evento (se existir)
        if (!$event->meeting_code) {
            return response()->json([
                'success' => false,
                'message' => 'Este evento não possui código de meeting configurado.'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'meeting_code' => $event->meeting_code,
            'meeting_link' => $event->meeting_link ?? null,
            'event_id' => $id,
            'event_title' => $event->title,
            'message' => 'Código do evento obtido com sucesso'
        ]);
    } catch (\Exception $e) {
        \Log::error('Get meeting code error: ' . $e->getMessage());
        return response()->json(['message' => $e->getMessage()], 500);
    }
});

// Rota pública para gerar código de participação para um evento (mantida para compatibilidade)
Route::post('/events/{id}/generate-code', function(\Illuminate\Http\Request $request, $id) {
    try {
        $event = DB::table('events')->where('id', $id)->first();
        
        if (!$event) {
            return response()->json(['message' => 'Evento não encontrado'], 404);
        }
        
        // Se o evento tem meeting_code, retornar ele
        if ($event->meeting_code) {
            return response()->json([
                'success' => true,
                'participation_code' => $event->meeting_code,
                'meeting_code' => $event->meeting_code,
                'meeting_link' => $event->meeting_link ?? null,
                'event_id' => $id,
                'message' => 'Código do evento obtido com sucesso'
            ]);
        }
        
        // Caso contrário, gerar código único (fallback)
        $participationCode = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        
        // Verificar se já existe um código para este evento
        $existingCode = DB::table('event_participants')
            ->where('event_id', $id)
            ->where('participation_code', $participationCode)
            ->first();
        
        // Se existir, gerar novo código
        while ($existingCode) {
            $participationCode = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
            $existingCode = DB::table('event_participants')
                ->where('event_id', $id)
                ->where('participation_code', $participationCode)
                ->first();
        }
        
        return response()->json([
            'success' => true,
            'participation_code' => $participationCode,
            'event_id' => $id,
            'message' => 'Código gerado com sucesso'
        ]);
    } catch (\Exception $e) {
        \Log::error('Generate participation code error: ' . $e->getMessage());
        return response()->json(['message' => $e->getMessage()], 500);
    }
});

// Rota pública para registrar participação em evento com código
Route::post('/events/register', function(\Illuminate\Http\Request $request) {
    try {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'participation_code' => 'required|string|min:6',
            'nome' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telefone' => 'nullable|string|max:20',
            'meeting_link' => 'nullable|url',
            'meeting_code' => 'nullable|string',
            'observations' => 'nullable|string'
        ]);
        
        $event = DB::table('events')->where('id', $request->event_id)->first();
        
        if (!$event) {
            return response()->json(['message' => 'Evento não encontrado'], 404);
        }
        
        // Verificar se o código já foi usado
        $existingParticipant = DB::table('event_participants')
            ->where('participation_code', $request->participation_code)
            ->where('event_id', $request->event_id)
            ->first();
        
        if ($existingParticipant) {
            return response()->json([
                'success' => false,
                'message' => 'Este código já foi utilizado. Por favor, gere um novo código.'
            ], 400);
        }
        
        // Verificar se o email já está registrado neste evento
        $existingEmail = DB::table('event_participants')
            ->where('event_id', $request->event_id)
            ->where('email', $request->email)
            ->first();
        
        if ($existingEmail) {
            return response()->json([
                'success' => false,
                'message' => 'Este email já está registrado para este evento.'
            ], 400);
        }
        
        // Criar registro de participante
        $participantId = DB::table('event_participants')->insertGetId([
            'event_id' => $request->event_id,
            'nome' => $request->nome,
            'email' => $request->email,
            'telefone' => $request->telefone,
            'participation_code' => $request->participation_code,
            'meeting_link' => $request->meeting_link,
            'meeting_code' => $request->meeting_code,
            'status' => 'confirmed',
            'observations' => $request->observations,
            'registered_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        $participant = DB::table('event_participants')
            ->where('id', $participantId)
            ->first();
        
        return response()->json([
            'success' => true,
            'message' => 'Participação registrada com sucesso!',
            'data' => [
                'id' => $participant->id,
                'event_id' => $participant->event_id,
                'nome' => $participant->nome,
                'email' => $participant->email,
                'participation_code' => $participant->participation_code,
                'meeting_link' => $participant->meeting_link,
                'meeting_code' => $participant->meeting_code,
                'status' => $participant->status
            ]
        ], 201);
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Dados inválidos',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        \Log::error('Event registration error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
});

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [\App\Http\Controllers\API\V1\Auth\AuthController::class, 'logout']);
    
    // Dashboard routes
    Route::get('/dashboard/stats', [\App\Http\Controllers\API\DashboardController::class, 'stats']);
    
    // Frontend compatibility routes (without v1 prefix)
    Route::get('/users/{id}', function(\Illuminate\Http\Request $request, $id) {
        try {
            $user = \App\Models\User::findOrFail($id);
            
            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->type === 'email' ? $user->identifier : null,
                'identifier' => $user->identifier,
                'created_at' => $user->created_at
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'data' => null,
                'message' => 'Utilizador não encontrado.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'data' => null,
                'message' => $e->getMessage()
            ], 500);
        }
    });
    
    Route::get('/users', function(\Illuminate\Http\Request $request) {
        try {
            $users = \App\Models\User::select('id', 'name', 'identifier', 'type', 'created_at')
                ->orderBy('name')
                ->limit(100)
                ->get()
                ->map(function($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->type === 'email' ? $user->identifier : null,
                        'identifier' => $user->identifier,
                        'created_at' => $user->created_at
                    ];
                });
            
            return response()->json($users->toArray());
        } catch (\Exception $e) {
            return response()->json(['data' => [], 'message' => $e->getMessage()], 500);
        }
    });
    
    Route::post('/users', function(\Illuminate\Http\Request $request) {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255',
                'password' => 'required|string|min:8',
            ]);
            
            // Check if identifier (email) already exists
            $existingUser = \App\Models\User::where('identifier', $request->email)
                ->where('type', 'email')
                ->first();
            
            if ($existingUser) {
                return response()->json([
                    'data' => null,
                    'message' => 'Este email já está registrado.'
                ], 422);
            }
            
            // Create user with identifier/type structure
            $user = \App\Models\User::create([
                'name' => $request->name,
                'identifier' => $request->email,
                'type' => 'email',
                'password' => \Illuminate\Support\Facades\Hash::make($request->password),
                'is_active' => true,
            ]);
            
            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->identifier,
                'identifier' => $user->identifier,
                'created_at' => $user->created_at
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'data' => null,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'data' => null,
                'message' => $e->getMessage()
            ], 500);
        }
    });
    
    Route::put('/users/{id}', function(\Illuminate\Http\Request $request, $id) {
        try {
            $user = \App\Models\User::findOrFail($id);
            
            $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|string|email|max:255',
                'password' => 'sometimes|string|min:8',
            ]);
            
            $updateData = [];
            
            if ($request->has('name')) {
                $updateData['name'] = $request->name;
            }
            
            if ($request->has('email')) {
                // Check if email is being changed and if new email already exists
                if ($request->email !== $user->identifier) {
                    $existingUser = \App\Models\User::where('identifier', $request->email)
                        ->where('type', 'email')
                        ->where('id', '!=', $id)
                        ->first();
                    
                    if ($existingUser) {
                        return response()->json([
                            'data' => null,
                            'message' => 'Este email já está registrado.'
                        ], 422);
                    }
                }
                $updateData['identifier'] = $request->email;
            }
            
            if ($request->has('password')) {
                $updateData['password'] = \Illuminate\Support\Facades\Hash::make($request->password);
            }
            
            $user->update($updateData);
            $user->refresh();
            
            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->type === 'email' ? $user->identifier : null,
                'identifier' => $user->identifier,
                'created_at' => $user->created_at
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'data' => null,
                'message' => 'Utilizador não encontrado.'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'data' => null,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'data' => null,
                'message' => $e->getMessage()
            ], 500);
        }
    });
    
    Route::delete('/users/{id}', function(\Illuminate\Http\Request $request, $id) {
        try {
            $user = \App\Models\User::findOrFail($id);
            $user->delete();
            
            return response()->json([
                'message' => 'Utilizador removido com sucesso.'
            ], 200);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'data' => null,
                'message' => 'Utilizador não encontrado.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'data' => null,
                'message' => $e->getMessage()
            ], 500);
        }
    });
    
    // Events routes (autenticadas)
    Route::get('/events', function(\Illuminate\Http\Request $request) {
        try {
            // Buscar eventos da tabela genérica 'events' com contagem de participantes
            $events = DB::table('events')
                ->leftJoin('users', 'events.user_id', '=', 'users.id')
                ->leftJoin('event_participants', 'events.id', '=', 'event_participants.event_id')
                ->select(
                    'events.id',
                    'events.title',
                    'events.description',
                    'events.start_date',
                    'events.end_date',
                    'events.location',
                    'events.status',
                    'events.meeting_code',
                    'events.meeting_link',
                    'events.created_at',
                    'users.name as user_name',
                    DB::raw('COUNT(DISTINCT event_participants.id) as participants_count')
                )
                ->groupBy(
                    'events.id',
                    'events.title',
                    'events.description',
                    'events.start_date',
                    'events.end_date',
                    'events.location',
                    'events.status',
                    'events.meeting_code',
                    'events.meeting_link',
                    'events.created_at',
                    'users.name'
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
                        'meeting_code' => $event->meeting_code ?? null,
                        'meeting_link' => $event->meeting_link ?? null,
                        'participants_count' => (int)$event->participants_count,
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
                'meeting_code' => $request->input('meeting_code', null),
                'meeting_link' => $request->input('meeting_link', null),
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
                    'events.meeting_code',
                    'events.meeting_link',
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
                'meeting_code' => $event->meeting_code ?? null,
                'meeting_link' => $event->meeting_link ?? null,
                'participants_count' => 0,
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
    
    Route::get('/documents/{id}', function(\Illuminate\Http\Request $request, $id) {
        try {
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
                'created_at' => $document->created_at,
                'user' => [
                    'name' => $document->user_name ?? 'Sistema'
                ]
            ];
            
            return response()->json($response);
        } catch (\Exception $e) {
            \Log::error('Document get error: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    });
    
    Route::post('/documents', function(\Illuminate\Http\Request $request) {
        try {
            $user = $request->user();
            
            // Preparar dados do documento
            $documentData = [
                'title' => $request->input('title', 'Novo Documento'),
                'description' => $request->input('description', ''),
                'category' => $request->input('category', 'Outro'),
                'file_path' => $request->input('file_path', ''),
                'file_type' => $request->input('file_type', ''),
                'file_size' => $request->input('file_size', 0),
                'user_id' => $user->id ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            // Criar documento na tabela documents
            $documentId = DB::table('documents')->insertGetId($documentData);
            
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
