<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ItemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Item::query()->with(['stockMovements' => function ($q) {
            $q->latest('movement_date')->limit(5);
        }]);

        // Filtros
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->boolean('low_stock')) {
            $query->lowStock();
        }

        // Ordenação
        $sortField = $request->get('sort_field', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortField, $sortOrder);

        $items = $query->paginate($request->get('per_page', 15));

        return response()->json($items);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:items,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:20',
            'category' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:100',
            'min_stock' => 'nullable|numeric|min:0',
            'max_stock' => 'nullable|numeric|min:0',
            'current_stock' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Validar max_stock > min_stock
        if ($request->filled('max_stock') && $request->filled('min_stock')) {
            if ($request->max_stock <= $request->min_stock) {
                return response()->json([
                    'message' => 'Stock máximo deve ser maior que stock mínimo',
                ], 422);
            }
        }

        $item = Item::create($request->all());

        return response()->json([
            'message' => 'Item criado com sucesso',
            'data' => $item,
        ], 201);
    }

    public function show(Item $item): JsonResponse
    {
        $item->load(['stockMovements' => function ($q) {
            $q->with('user')->latest('movement_date')->limit(10);
        }]);

        return response()->json($item);
    }

    public function update(Request $request, Item $item): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:items,code,'.$item->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:20',
            'category' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:100',
            'min_stock' => 'nullable|numeric|min:0',
            'max_stock' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Validar max_stock > min_stock
        if ($request->filled('max_stock') && $request->filled('min_stock')) {
            if ($request->max_stock <= $request->min_stock) {
                return response()->json([
                    'message' => 'Stock máximo deve ser maior que stock mínimo',
                ], 422);
            }
        }

        // current_stock não pode ser atualizado diretamente, apenas através de movimentações
        $data = $request->except('current_stock');
        $item->update($data);

        return response()->json([
            'message' => 'Item atualizado com sucesso',
            'data' => $item->fresh(),
        ]);
    }

    public function destroy(Item $item): JsonResponse
    {
        // Não permitir deletar itens com movimentações, apenas inativar
        if ($item->stockMovements()->exists()) {
            return response()->json([
                'message' => 'Não é possível deletar item com movimentações. Desative-o ao invés disso.',
            ], 422);
        }

        $item->delete();

        return response()->json([
            'message' => 'Item deletado com sucesso',
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $search = $request->get('q', '');

        $items = Item::query()
            ->active()
            ->search($search)
            ->select('id', 'code', 'name', 'current_stock', 'unit')
            ->limit(10)
            ->get();

        return response()->json($items);
    }

    public function lowStock(): JsonResponse
    {
        $items = Item::query()
            ->active()
            ->lowStock()
            ->with('stockMovements')
            ->get();

        return response()->json($items);
    }

    public function movements(Item $item): JsonResponse
    {
        $movements = $item->stockMovements()
            ->with(['user', 'request'])
            ->latest('movement_date')
            ->paginate(15);

        return response()->json($movements);
    }
}
