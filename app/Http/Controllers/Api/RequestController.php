<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Request as RequestModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = RequestModel::query()->with(['item', 'requester', 'statusHistory.changedBy']);

        // Se não for armazém/gestor/admin, mostrar apenas próprias solicitações
        if (! $user->canManageStock()) {
            $query->where('requester_id', $user->id);
        }

        // Filtros
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('requester_id') && $user->canManageStock()) {
            $query->byRequester($request->requester_id);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('date_from')) {
            $query->where('requested_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('requested_at', '<=', $request->date_to);
        }

        // Ordenação
        $sortField = $request->get('sort_field', 'requested_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortField, $sortOrder);

        $requests = $query->paginate($request->get('per_page', 15));

        return response()->json($requests);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:items,id',
            'reference' => 'required|string|max:100',
            'work_order' => 'required|string|max:100',
            'quantity' => 'required|numeric|min:0.01',
            'equipment_id' => 'required|string|max:100',
            'replacement_reason' => 'required|in:Desgaste,Erro de Fabrico,Outro',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Verificar se o item existe e está ativo
        $item = Item::find($request->item_id);
        if (! $item->is_active) {
            return response()->json([
                'message' => 'Item não está ativo',
            ], 422);
        }

        // Verificar stock disponível (apenas alertar, não bloquear)
        $stockAvailable = $item->current_stock;
        $warning = null;
        if ($request->quantity > $stockAvailable) {
            $warning = "Atenção: Quantidade solicitada ({$request->quantity}) excede stock disponível ({$stockAvailable})";
        }

        // Criar solicitação
        $requestData = $request->all();
        $requestData['requester_id'] = Auth::id();
        $requestData['status'] = 'solicitado';

        $requestModel = RequestModel::create($requestData);

        // Criar primeiro registro de histórico
        $requestModel->statusHistory()->create([
            'from_status' => null,
            'to_status' => 'solicitado',
            'changed_by' => Auth::id(),
            'notes' => 'Solicitação criada',
            'changed_at' => now(),
        ]);

        $requestModel->load(['item', 'requester']);

        return response()->json([
            'message' => 'Solicitação criada com sucesso',
            'data' => $requestModel,
            'warning' => $warning,
        ], 201);
    }

    public function show(RequestModel $request): JsonResponse
    {
        $request->load([
            'item',
            'requester',
            'statusHistory.changedBy',
            'stockMovements',
        ]);

        return response()->json($request);
    }

    public function update(Request $request, RequestModel $requestModel): JsonResponse
    {
        // Apenas permitir editar se status for "solicitado"
        if (! $requestModel->canEdit()) {
            return response()->json([
                'message' => 'Apenas solicitações com status "solicitado" podem ser editadas',
            ], 422);
        }

        // Apenas o solicitante ou admin/gestor pode editar
        $user = Auth::user();
        if ($requestModel->requester_id !== $user->id && ! $user->isGestor()) {
            return response()->json([
                'message' => 'Sem permissão para editar esta solicitação',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:items,id',
            'reference' => 'required|string|max:100',
            'work_order' => 'required|string|max:100',
            'quantity' => 'required|numeric|min:0.01',
            'equipment_id' => 'required|string|max:100',
            'replacement_reason' => 'required|in:Desgaste,Erro de Fabrico,Outro',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $requestModel->update($request->only([
            'item_id',
            'reference',
            'work_order',
            'quantity',
            'equipment_id',
            'replacement_reason',
            'notes',
        ]));

        return response()->json([
            'message' => 'Solicitação atualizada com sucesso',
            'data' => $requestModel->fresh(['item', 'requester']),
        ]);
    }

    public function destroy(RequestModel $request): JsonResponse
    {
        // Apenas permitir cancelar se status for "solicitado"
        if (! $request->canCancel()) {
            return response()->json([
                'message' => 'Apenas solicitações com status "solicitado" podem ser canceladas',
            ], 422);
        }

        // Apenas o solicitante ou admin/gestor pode cancelar
        $user = Auth::user();
        if ($request->requester_id !== $user->id && ! $user->isGestor()) {
            return response()->json([
                'message' => 'Sem permissão para cancelar esta solicitação',
            ], 403);
        }

        $request->changeStatus('cancelado', $user, 'Solicitação cancelada pelo usuário');

        return response()->json([
            'message' => 'Solicitação cancelada com sucesso',
        ]);
    }

    public function my(Request $request): JsonResponse
    {
        $query = RequestModel::query()
            ->with(['item', 'requester'])
            ->where('requester_id', Auth::id());

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        $requests = $query->latest('requested_at')->paginate(15);

        return response()->json($requests);
    }

    public function history(RequestModel $request): JsonResponse
    {
        $history = $request->statusHistory()
            ->with('changedBy')
            ->orderBy('changed_at', 'desc')
            ->get();

        return response()->json($history);
    }
}
