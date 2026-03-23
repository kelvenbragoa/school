<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Request as RequestModel;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Estatísticas de pedidos
        $requestStats = [
            'solicitado' => RequestModel::byStatus('solicitado')->count(),
            'preparando' => RequestModel::byStatus('preparando')->count(),
            'enviando' => RequestModel::byStatus('enviando')->count(),
            'recebido' => RequestModel::byStatus('recebido')->count(),
            'entregue' => RequestModel::byStatus('entregue')->count(),
            'total' => RequestModel::count(),
        ];

        // Se for solicitante, mostrar apenas próprias estatísticas
        if (! $user->canManageStock()) {
            $requestStats = [
                'solicitado' => RequestModel::byStatus('solicitado')->byRequester($user->id)->count(),
                'preparando' => RequestModel::byStatus('preparando')->byRequester($user->id)->count(),
                'enviando' => RequestModel::byStatus('enviando')->byRequester($user->id)->count(),
                'recebido' => RequestModel::byStatus('recebido')->byRequester($user->id)->count(),
                'entregue' => RequestModel::byStatus('entregue')->byRequester($user->id)->count(),
                'total' => RequestModel::byRequester($user->id)->count(),
            ];
        }

        // Itens com stock baixo
        $lowStockCount = Item::active()->lowStock()->count();

        // Movimentações do dia/semana/mês
        $today = now()->startOfDay();
        $weekAgo = now()->subWeek();
        $monthAgo = now()->subMonth();

        $movementsStats = [
            'today' => StockMovement::where('movement_date', '>=', $today)->count(),
            'week' => StockMovement::where('movement_date', '>=', $weekAgo)->count(),
            'month' => StockMovement::where('movement_date', '>=', $monthAgo)->count(),
        ];

        // Total de itens
        $itemsStats = [
            'total' => Item::active()->count(),
            'low_stock' => $lowStockCount,
        ];

        return response()->json([
            'requests' => $requestStats,
            'items_total' => $itemsStats['total'],
            'items_low_stock' => $lowStockCount,
            'movements' => $movementsStats,
        ]);
    }

    public function alerts(Request $request): JsonResponse
    {
        // Itens com stock abaixo do mínimo
        $lowStockItems = Item::query()
            ->active()
            ->lowStock()
            ->select('id', 'code', 'name', 'current_stock', 'min_stock', 'unit')
            ->orderBy('current_stock', 'asc')
            ->limit(10)
            ->get();

        // Pedidos aguardando ação (para usuários do armazém)
        $pendingRequests = [];
        if (Auth::user()->canManageStock()) {
            $pendingRequests = RequestModel::query()
                ->whereIn('status', ['solicitado', 'preparando'])
                ->with(['item', 'requester'])
                ->latest('requested_at')
                ->limit(5)
                ->get();
        }

        return response()->json([
            'low_stock_items' => $lowStockItems,
            'pending_requests' => $pendingRequests,
        ]);
    }

    public function recentRequests(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = RequestModel::query()->with(['item', 'requester']);

        // Se não for armazém, mostrar apenas próprias solicitações
        if (! $user->canManageStock()) {
            $query->byRequester($user->id);
        }

        $recentRequests = $query
            ->latest('requested_at')
            ->limit($request->get('limit', 10))
            ->get();

        return response()->json($recentRequests);
    }

    public function topItems(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month'); // day, week, month, year
        $limit = $request->get('limit', 10);

        $dateFrom = match ($period) {
            'day' => now()->startOfDay(),
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'year' => now()->subYear(),
            default => now()->subMonth(),
        };

        // Usar requests ao invés de stock_movements para ser mais preciso
        $topItems = DB::table('requests')
            ->select(
                'item_id',
                DB::raw('SUM(quantity) as total_requested')
            )
            ->where('requested_at', '>=', $dateFrom)
            ->whereNotIn('status', ['cancelado'])
            ->groupBy('item_id')
            ->orderBy('total_requested', 'desc')
            ->limit($limit)
            ->get();

        // Buscar informações dos itens
        $itemIds = $topItems->pluck('item_id');
        $items = Item::whereIn('id', $itemIds)->get()->keyBy('id');

        $result = $topItems->map(function ($row) use ($items) {
            $item = $items[$row->item_id] ?? null;

            if (! $item) {
                return null;
            }

            return [
                'id' => $item->id,
                'code' => $item->code,
                'name' => $item->name,
                'unit' => $item->unit,
                'total_requested' => $row->total_requested,
            ];
        })->filter(fn ($item) => $item !== null);

        return response()->json($result->values());
    }
}
