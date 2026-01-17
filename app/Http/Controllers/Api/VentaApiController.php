<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Models\VentaItem;
use App\Models\BranchInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VentaApiController extends Controller
{
    /**
     * Listar ventas
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Venta::with(['user', 'branch', 'items.product'])->latest();

        // Si no es root, solo ve ventas de su sucursal
        if (!$user->isRoot() && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filtro por fecha
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $perPage = $request->get('per_page', 50);
        $ventas = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $ventas->items(),
            'pagination' => [
                'total' => $ventas->total(),
                'per_page' => $ventas->perPage(),
                'current_page' => $ventas->currentPage(),
                'last_page' => $ventas->lastPage(),
            ],
            'stats' => [
                'total_ventas' => $ventas->sum('total'),
                'total_utilidad' => $ventas->sum('utilidad'),
            ]
        ]);
    }

    /**
     * Obtener una venta específica
     */
    public function show($id)
    {
        $venta = Venta::with(['user', 'branch', 'items.product'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $venta
        ]);
    }

    /**
     * Crear una nueva venta
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.precio_venta' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Calcular totales
            $subtotal = 0;
            $costo_total = 0;

            foreach ($validated['items'] as $item) {
                $inventory = BranchInventory::where('product_id', $item['product_id'])
                    ->where('branch_id', $validated['branch_id'])
                    ->first();

                if (!$inventory) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "El producto {$item['product_id']} no tiene inventario en esta sucursal"
                    ], 400);
                }

                if ($inventory->stock < $item['quantity']) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Stock insuficiente para el producto {$inventory->product->name}"
                    ], 400);
                }

                $subtotal += $item['precio_venta'] * $item['quantity'];
                $costo_total += $inventory->product->costo * $item['quantity'];
            }

            $utilidad = $subtotal - $costo_total;

            // Crear la venta
            $venta = Venta::create([
                'branch_id' => $validated['branch_id'],
                'user_id' => $request->user()->id,
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'utilidad' => $utilidad,
            ]);

            // Crear los items y actualizar inventario
            foreach ($validated['items'] as $item) {
                $inventory = BranchInventory::where('product_id', $item['product_id'])
                    ->where('branch_id', $validated['branch_id'])
                    ->first();

                VentaItem::create([
                    'venta_id' => $venta->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'precio_costo' => $inventory->product->costo,
                    'precio_venta' => $item['precio_venta'],
                    'subtotal' => $item['precio_venta'] * $item['quantity'],
                    'utilidad' => ($item['precio_venta'] - $inventory->product->costo) * $item['quantity'],
                ]);

                // Actualizar stock
                $inventory->decrement('stock', $item['quantity']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta registrada exitosamente',
                'data' => $venta->load(['items.product', 'user', 'branch'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la venta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar una venta
     */
    public function destroy($id)
    {
        $user = request()->user();
        $userRole = $user->roles->first();

        if (!$userRole || ($userRole->name !== 'administrador' && $userRole->name !== 'root')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para eliminar ventas'
            ], 403);
        }

        DB::beginTransaction();
        try {
            $venta = Venta::with('items')->findOrFail($id);

            // Restaurar el stock
            foreach ($venta->items as $item) {
                $inventory = BranchInventory::where('product_id', $item->product_id)
                    ->where('branch_id', $venta->branch_id)
                    ->first();

                if ($inventory) {
                    $inventory->increment('stock', $item->quantity);
                }
            }

            // Eliminar items y venta
            $venta->items()->delete();
            $venta->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la venta: ' . $e->getMessage()
            ], 500);
        }
    }
}
