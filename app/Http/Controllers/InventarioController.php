<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventarioController extends Controller
{
    public function index()
    {
        $inventarios = Inventory::with('product')->get()->map(function ($inventario) {
            return [
                'id' => $inventario->id,
                'product_id' => $inventario->product_id,
                'product_name' => $inventario->product->name ?? 'Producto eliminado',
                'product_price' => $inventario->product->price ?? 0,
                'quantity' => $inventario->quantity ?? 0,
                'min_stock' => $inventario->min_stock ?? 0,
                'status' => $this->getStockStatus($inventario->quantity ?? 0, $inventario->min_stock ?? 0),
                'updated_at' => $inventario->updated_at->format('d/m/Y H:i'),
            ];
        });

        // Productos sin inventario
        $productosSinInventario = Product::whereDoesntHave('inventory')->get()->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
            ];
        });

        // Estadísticas
        $totalProductos = Product::count();
        $productosConInventario = Inventory::count();
        $productosStockBajo = $inventarios->where('status', 'bajo')->count();
        $productosAgotados = $inventarios->where('status', 'agotado')->count();

        return Inertia::render('Inventario/Index', [
            'inventarios' => $inventarios,
            'productosSinInventario' => $productosSinInventario,
            'stats' => [
                'total' => $totalProductos,
                'conInventario' => $productosConInventario,
                'stockBajo' => $productosStockBajo,
                'agotados' => $productosAgotados,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id|unique:inventories,product_id',
            'quantity' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
        ]);

        Inventory::create([
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'min_stock' => $request->min_stock,
        ]);

        return redirect()->back();
    }

    public function update(Request $request, Inventory $inventario)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
        ]);

        $inventario->update([
            'quantity' => $request->quantity,
            'min_stock' => $request->min_stock,
        ]);

        return redirect()->back();
    }

    public function ajustarStock(Request $request, Inventory $inventario)
    {
        $request->validate([
            'cantidad' => 'required|integer',
            'tipo' => 'required|in:entrada,salida',
        ]);

        $nuevaCantidad = $inventario->quantity;

        if ($request->tipo === 'entrada') {
            $nuevaCantidad += $request->cantidad;
        } else {
            $nuevaCantidad -= $request->cantidad;
            if ($nuevaCantidad < 0) {
                $nuevaCantidad = 0;
            }
        }

        $inventario->update(['quantity' => $nuevaCantidad]);

        return redirect()->back();
    }

    public function destroy(Inventory $inventario)
    {
        $inventario->delete();
        return redirect()->back();
    }

    private function getStockStatus($quantity, $minStock)
    {
        if ($quantity === 0) {
            return 'agotado';
        } elseif ($quantity <= 2) {
            return 'critico';
        } elseif ($quantity <= $minStock) {
            return 'bajo';
        }
        return 'normal';
    }
}
