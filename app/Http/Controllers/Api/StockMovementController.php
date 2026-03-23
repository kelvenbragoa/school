<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StockMovementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = StockMovement::query()->with(['item', 'user', 'request']);

        // Filtros
        if ($request->filled('item_id')) {
            $query->byItem($request->item_id);
        }

        if ($request->filled('movement_type')) {
            $query->byType($request->movement_type);
        }

        if ($request->filled('date_from')) {
            $query->where('movement_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('movement_date', '<=', $request->date_to);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->dateRange($request->date_from, $request->date_to);
        }

        // Ordenação
        $sortField = $request->get('sort_field', 'movement_date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortField, $sortOrder);

        $movements = $query->paginate($request->get('per_page', 15));

        return response()->json($movements);
    }

    public function store(Request $request): JsonResponse
    {
        // Verificar permissão
        if (! Auth::user()->canManageStock()) {
            return response()->json(['message' => 'Sem permissão'], 403);
        }

        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:items,id',
            'movement_type' => 'required|in:entrada,saida,ajuste',
            'movement_reason' => 'required|in:compra,devolucao,pedido,consumo_interno,inventario,outro',
            'quantity' => 'required|numeric|min:0.01',
            'reference' => 'nullable|string|max:100',
            'supplier' => 'nullable|string|max:255',
            'destination' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $item = Item::find($request->item_id);

        $movement = null;
        DB::transaction(function () use ($request, $item, &$movement) {
            $previousStock = $item->current_stock;

            // Calcular novo stock baseado no tipo de movimentação
            if ($request->movement_type === 'entrada') {
                $newStock = $previousStock + $request->quantity;
            } else { // saida ou ajuste
                $newStock = $previousStock - $request->quantity;

                // Validar stock negativo (apenas avisar, não bloquear)
                if ($newStock < 0) {
                    // Permitir, mas registrar no notes
                    $request->merge([
                        'notes' => ($request->notes ?? '').' [AVISO: Stock ficou negativo]',
                    ]);
                }
            }

            // Criar movimentação
            $movement = StockMovement::create([
                'item_id' => $item->id,
                'movement_type' => $request->movement_type,
                'movement_reason' => $request->movement_reason,
                'quantity' => $request->quantity,
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'reference' => $request->reference,
                'supplier' => $request->supplier,
                'destination' => $request->destination,
                'location' => $request->location ?? $item->location,
                'user_id' => Auth::id(),
                'notes' => $request->notes,
                'movement_date' => now(),
            ]);

            // Atualizar stock do item
            $item->update(['current_stock' => $newStock]);
        });

        return response()->json([
            'message' => 'Movimentação registrada com sucesso',
            'data' => $movement->load(['item', 'user']),
        ], 201);
    }

    public function show(StockMovement $stockMovement): JsonResponse
    {
        $stockMovement->load(['item', 'user', 'request', 'inventory']);

        return response()->json($stockMovement);
    }

    public function entry(Request $request): JsonResponse
    {
        // Atalho para criar entrada
        $request->merge(['movement_type' => 'entrada']);

        return $this->store($request);
    }

    public function exit(Request $request): JsonResponse
    {
        // Atalho para criar saída
        $request->merge(['movement_type' => 'saida']);

        return $this->store($request);
    }
}
