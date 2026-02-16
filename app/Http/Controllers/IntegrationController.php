<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessExternalOccurrence;
use App\Models\EventInbox;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class IntegrationController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'externalId' => 'required|string',
            'type' => 'required|string',
            'description' => 'required|string',
            'reportedAt' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $idempotencyKey = $request->header('Idempotency-Key');

        if (!$idempotencyKey) {
            return response()->json(['message' => 'O cabeçalho Idempotency-Key é obrigatório.'], 400);
        }

        $existingEvent = EventInbox::where('idempotency_key', $idempotencyKey)->first();

        if ($existingEvent) {
            return response()->json([
                'commandId' => $existingEvent->id,
                'status' => 'already_accepted'
            ], 200);
        }

        $event = EventInbox::create([
            'idempotency_key' => $idempotencyKey,
            'source' => 'sistema_externo',
            'type' => 'occurrence.created',
            'payload' => $request->all(),
            'status' => 'pending',
        ]);

        ProcessExternalOccurrence::dispatch($event);

        return response()->json([
            'commandId' => $event->id,
            'status' => 'accepted'
        ], 202);
    }
}
