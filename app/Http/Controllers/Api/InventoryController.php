<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Inventory::query()->with(['responsible', 'inventoryItems']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $inventories = $query->latest('start_date')->paginate(15);

        return response()->json($inventories);
    }

    public function store(Request $request): JsonResponse
    {
        if (! Auth::user()->canManageStock()) {
            return response()->json(['message' => 'Sem permissão'], 403);
        }

        $validated = $request->validate([
            'location' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $validated['responsible_id'] = Auth::id();
        $validated['status'] = 'em_andamento';

        $inventory = Inventory::create($validated);

        return response()->json([
            'message' => 'Inventário criado com sucesso',
            'data' => $inventory,
        ], 201);
    }

    public function show(Inventory $inventory): JsonResponse
    {
        $inventory->load(['responsible', 'inventoryItems.item', 'inventoryItems.countedBy']);

        return response()->json($inventory);
    }

    public function update(Request $request, Inventory $inventory): JsonResponse
    {
        if (! Auth::user()->canManageStock()) {
            return response()->json(['message' => 'Sem permissão'], 403);
        }

        if ($inventory->status !== 'em_andamento') {
            return response()->json(['message' => 'Apenas inventários em andamento podem ser editados'], 422);
        }

        $validated = $request->validate([
            'location' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $inventory->update($validated);

        return response()->json([
            'message' => 'Inventário atualizado',
            'data' => $inventory,
        ]);
    }

    public function destroy(Inventory $inventory): JsonResponse
    {
        if (! Auth::user()->isGestor()) {
            return response()->json(['message' => 'Sem permissão'], 403);
        }

        if ($inventory->status === 'concluido') {
            return response()->json(['message' => 'Inventários concluídos não podem ser deletados'], 422);
        }

        $inventory->delete();

        return response()->json(['message' => 'Inventário deletado']);
    }
}
