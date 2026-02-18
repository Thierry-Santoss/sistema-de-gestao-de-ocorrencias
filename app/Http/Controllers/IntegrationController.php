<?php

namespace App\Http\Controllers;

use App\Services\OccurrenceIntegrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class IntegrationController extends Controller
{
    public function store(Request $request, OccurrenceIntegrationService $service): JsonResponse
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

        $event = $service->handle($request->all(), $idempotencyKey);

        $status = $event->wasRecentlyCreated ? 202 : 200;

        return response()->json([
            'commandId' => $event->id,
            'status' => $event->wasRecentlyCreated ? 'accepted' : 'already_accepted'
        ], $status);
    }
}
