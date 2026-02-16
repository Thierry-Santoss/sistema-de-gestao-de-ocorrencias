<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Dispatch;
use App\Models\Occurrence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class OccurrenceController extends Controller
{
    public function index(Request $request)
    {
        $cacheKey = 'occurrences_' . md5(json_encode($request->all()));

        return Cache::remember($cacheKey, 10, function () use ($request) {
            return Occurrence::query()
                ->when($request->status, fn($q) => $q->where('status', $request->status))
                ->when($request->type, fn($q) => $q->where('type', $request->type))
                ->with('dispatches')
                ->latest()
                ->paginate(10);
        });
    }

    public function start($id)
    {
        $occurrence = Occurrence::findOrFail($id);

        if (!$occurrence->transitionTo('in_progress')) {
            return response()->json(['error' => 'Transição de status inválida.'], 422);
        }

        return response()->json(['message' => 'Atendimento iniciado.', 'data' => $occurrence]);
    }

    public function resolve($id)
    {
        $occurrence = Occurrence::findOrFail($id);

        if (!$occurrence->transitionTo('resolved')) {
            return response()->json(['error' => 'Não é possível resolver esta ocorrência.'], 422);
        }

        return response()->json(['message' => 'Ocorrência encerrada.']);
    }

    public function addDispatch(Request $request, $id)
    {
        $occurrence = Occurrence::findOrFail($id);

        $validated = $request->validate([
            'resourceCode' => 'required|string|max:10',
        ]);

        $dispatch = $occurrence->dispatches()->create([
            'resource_code' => $validated['resourceCode'],
            'status' => 'assigned',
        ]);

        return response()->json([
            'message' => 'Viatura despachada com sucesso.',
            'data' => $dispatch
        ], 201);
    }

    public function show($id)
    {
        return Occurrence::with(['dispatches' => function ($q) {
            $q->latest();
        }])->findOrFail($id);
    }

    public function cancel(string $id)
    {
        $occurrence = Occurrence::findOrFail($id);

        // Tentamos transitar para o estado 'cancelled'
        if (!$occurrence->transitionTo('cancelled')) {
            return response()->json([
                'message' => 'Não é possível cancelar uma ocorrência neste estado.'
            ], 422);
        }

        return response()->json([
            'message' => 'Ocorrência cancelada com sucesso.',
            'status' => $occurrence->status
        ]);
    }

    public function updateDispatchStatus(Request $request, string $id)
    {
        $dispatch = Dispatch::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:assigned,en_route,on_site,closed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!$dispatch->updateStatus($request->status)) {
            return response()->json([
                'error' => "Transição de status inválida: de {$dispatch->status} para {$request->status}."
            ], 422);
        }

        return response()->json([
            'message' => 'Status da viatura atualizado.',
            'dispatch' => $dispatch
        ]);
    }
}
