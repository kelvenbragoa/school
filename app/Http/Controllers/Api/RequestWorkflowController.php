<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Request as RequestModel;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RequestWorkflowController extends Controller
{
    public function prepare(Request $request, string $id): JsonResponse
    {
        $requestModel = RequestModel::findOrFail($id);
        // Verificar permissão
        if (! Auth::user()->canChangeRequestStatus()) {
            return response()->json(['message' => 'Sem permissão'], 403);
        }

        // Verificar status atual
        if ($requestModel->status !== 'solicitado') {
            return response()->json([
                'message' => 'Apenas solicitações com status "solicitado" podem ser preparadas',
            ], 422);
        }

        // Verificar stock disponível
        $item = $requestModel->item;
        if ($requestModel->quantity > $item->current_stock) {
            // Apenas alertar, não bloquear (permitir backorder)
            $available = $item->current_stock;

            return response()->json([
                'message' => "Stock insuficiente. Disponível: {$available} {$item->unit}. Necessário: {$requestModel->quantity} {$item->unit}",
                'allow_backorder' => true,
                'available_stock' => $available,
            ], 422);
        }

        $requestModel->changeStatus('preparando', Auth::user(), $request->notes);

        return response()->json([
            'message' => 'Pedido em preparação',
            'data' => $requestModel->fresh(['item', 'requester', 'statusHistory']),
        ]);
    }

    public function send(Request $request, string $id): JsonResponse
    {
        $requestModel = RequestModel::findOrFail($id);

        // Verificar permissão
        if (! Auth::user()->canChangeRequestStatus()) {
            return response()->json(['message' => 'Sem permissão'], 403);
        }

        // Verificar status atual
        if ($requestModel->status !== 'preparando') {
            return response()->json([
                'message' => 'Apenas solicitações "preparando" podem ser enviadas',
            ], 422);
        }

        $item = $requestModel->item;

        // Verificar stock disponível novamente
        if ($requestModel->quantity > $item->current_stock) {
            return response()->json([
                'message' => "Stock insuficiente para enviar. Disponível: {$item->current_stock}",
            ], 422);
        }

        DB::transaction(function () use ($requestModel, $item, $request) {
            // Registrar saída de stock
            $previousStock = $item->current_stock;
            $newStock = $previousStock - $requestModel->quantity;

            StockMovement::create([
                'item_id' => $item->id,
                'movement_type' => 'saida',
                'movement_reason' => 'pedido',
                'quantity' => $requestModel->quantity,
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'request_id' => $requestModel->id,
                'reference' => $requestModel->request_number,
                'destination' => $requestModel->equipment_id,
                'location' => $item->location,
                'user_id' => Auth::id(),
                'notes' => $request->notes ?? "Saída para pedido {$requestModel->request_number}",
                'movement_date' => now(),
            ]);

            // Atualizar stock do item
            $item->update(['current_stock' => $newStock]);

            // Mudar status do pedido
            $requestModel->changeStatus('enviando', Auth::user(), $request->notes);
        });

        return response()->json([
            'message' => 'Pedido enviado e stock baixado',
            'data' => $requestModel->fresh(['item', 'requester', 'statusHistory', 'stockMovements']),
        ]);
    }

    public function receive(Request $request, string $id): JsonResponse
    {
        $requestModel = RequestModel::findOrFail($id);

        // Verificar status atual
        if ($requestModel->status !== 'enviando') {
            return response()->json([
                'message' => 'Apenas solicitações "enviando" podem ser recebidas',
            ], 422);
        }

        // Permitir solicitante ou armazém confirmar recebimento
        $user = Auth::user();
        if ($requestModel->requester_id !== $user->id && ! $user->canChangeRequestStatus()) {
            return response()->json(['message' => 'Sem permissão'], 403);
        }

        $requestModel->changeStatus('recebido', $user, $request->notes);

        return response()->json([
            'message' => 'Recebimento confirmado',
            'data' => $requestModel->fresh(['item', 'requester', 'statusHistory']),
        ]);
    }

    public function deliver(Request $request, string $id): JsonResponse
    {
        $requestModel = RequestModel::findOrFail($id);

        // Verificar status atual
        if ($requestModel->status !== 'recebido') {
            return response()->json([
                'message' => 'Apenas solicitações "recebido" podem ser entregues',
            ], 422);
        }

        // Apenas solicitante ou gestor pode confirmar entrega
        $user = Auth::user();
        if ($requestModel->requester_id !== $user->id && ! $user->isGestor()) {
            return response()->json(['message' => 'Sem permissão'], 403);
        }

        $requestModel->changeStatus('entregue', $user, $request->notes);

        return response()->json([
            'message' => 'Entrega confirmada. Pedido finalizado.',
            'data' => $requestModel->fresh(['item', 'requester', 'statusHistory']),
        ]);
    }

    public function cancel(Request $request, string $id): JsonResponse
    {
        $requestModel = RequestModel::findOrFail($id);

        // Apenas gestor pode cancelar pedidos que não estão em "solicitado"
        $user = Auth::user();
        if ($requestModel->status !== 'solicitado' && ! $user->isGestor()) {
            return response()->json([
                'message' => 'Apenas gestores podem cancelar pedidos em andamento',
            ], 403);
        }

        // Se o pedido já foi enviado (stock foi baixado), precisamos reverter
        if ($requestModel->status === 'enviando' || $requestModel->status === 'recebido') {
            DB::transaction(function () use ($requestModel, $request, $user) {
                // Reverter stock
                $stockMovement = $requestModel->stockMovements()->where('movement_type', 'saida')->first();
                if ($stockMovement) {
                    $item = $requestModel->item;
                    $previousStock = $item->current_stock;
                    $newStock = $previousStock + $requestModel->quantity;

                    StockMovement::create([
                        'item_id' => $item->id,
                        'movement_type' => 'entrada',
                        'movement_reason' => 'devolucao',
                        'quantity' => $requestModel->quantity,
                        'previous_stock' => $previousStock,
                        'new_stock' => $newStock,
                        'request_id' => $requestModel->id,
                        'reference' => $requestModel->request_number,
                        'location' => $item->location,
                        'user_id' => Auth::id(),
                        'notes' => "Devolução por cancelamento do pedido {$requestModel->request_number}",
                        'movement_date' => now(),
                    ]);

                    $item->update(['current_stock' => $newStock]);
                }

                $requestModel->changeStatus('cancelado', $user, $request->notes ?? 'Pedido cancelado');
            });
        } else {
            $requestModel->changeStatus('cancelado', $user, $request->notes ?? 'Pedido cancelado');
        }

        return response()->json([
            'message' => 'Pedido cancelado',
            'data' => $requestModel->fresh(['item', 'requester', 'statusHistory']),
        ]);
    }
}
