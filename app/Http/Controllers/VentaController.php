<?php
namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class VentaController extends Controller
{
    public function index()
    {
        $hoy = Carbon::today();

        // Obtener ventas del día con sus relaciones
        $ventas = Sale::with(['saleDetails.product', 'customer', 'user'])
            ->whereDate('created_at', $hoy)
            ->latest()
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'total' => $sale->total,
                    'customer' => $sale->customer ? $sale->customer->name : 'Cliente general',
                    'user' => $sale->user->name,
                    'created_at' => $sale->created_at->format('H:i'),
                    'details' => $sale->saleDetails->map(function ($detail) {
                        return [
                            'tipo_venta' => $detail->tipo_venta,
                            'descripcion' => $detail->tipo_venta === 'libre'
                                ? $detail->descripcion
                                : ($detail->product ? $detail->product->name : 'N/A'),
                            'quantity' => $detail->quantity,
                            'price' => $detail->price,
                            'subtotal' => $detail->subtotal,
                        ];
                    })
                ];
            });

        // Obtener productos con su stock
        $productos = Product::with('inventory')->get()->map(function ($product) {
            $inventory = $product->inventory->first();
            return [
                'id' => $product->id,
                'name' => $product->name,
                'barcode' => $product->barcode,
                'price' => $product->price,
                'stock' => $inventory ? $inventory->stock : 0,
            ];
        });

        $user = Auth::user();

        return Inertia::render('Ventas/Index', [
            'ventas' => $ventas,
            'productos' => $productos,
            'user' => $user,
            'ventasLibresHabilitadas' => SystemSetting::ventasLibresHabilitadas(),
        ]);
    }

    public function store(Request $request)
    {
        // Detectar tipo de venta
        if ($request->has('items')) {
            return $this->storeCatalogoVenta($request);
        } else {
            return $this->storeLibreVenta($request);
        }
    }

    private function storeLibreVenta(Request $request)
    {
        $request->validate([
            'descripcion' => 'required|string|max:255',
            'total' => 'required|numeric|min:0',
        ]);

        $totalVenta = $request->total;

        // Crear la venta principal con el total
        $venta = Sale::create([
            'user_id' => Auth::id(),
            'customer_id' => null,
            'total' => $totalVenta,
            'status' => 'completed'
        ]);

        // Crear el detalle de venta LIBRE
        SaleDetail::create([
            'sale_id' => $venta->id,
            'product_id' => null,
            'tipo_venta' => 'libre',
            'descripcion' => $request->descripcion,
            'quantity' => 1,
            'price' => $request->total,
            'subtotal' => $request->total,
            'total' => $request->total
        ]);

        return redirect()->back()->with('success', 'Venta libre registrada correctamente');
    }

    private function storeCatalogoVenta(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.subtotal' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Calcular total
            $totalVenta = collect($request->items)->sum('subtotal');

            // Crear la venta principal
            $venta = Sale::create([
                'user_id' => Auth::id(),
                'customer_id' => null,
                'total' => $totalVenta,
                'status' => 'completed'
            ]);

            // Procesar cada item del carrito
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);

                // Verificar stock disponible
                $inventory = Inventory::where('product_id', $product->id)->first();

                if (!$inventory) {
                    throw new \Exception("El producto {$product->name} no tiene inventario registrado");
                }

                if ($inventory->stock < $item['quantity']) {
                    throw new \Exception("Stock insuficiente para {$product->name}. Disponible: {$inventory->stock}");
                }

                // Crear detalle de venta
                SaleDetail::create([
                    'sale_id' => $venta->id,
                    'product_id' => $product->id,
                    'tipo_venta' => 'catalogo',
                    'descripcion' => $product->name,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                    'total' => $item['subtotal']
                ]);

                // Actualizar inventario (restar stock)
                $inventory->stock -= $item['quantity'];
                $inventory->save();
            }

            DB::commit();
            return redirect()->back()->with('success', 'Venta de catálogo registrada correctamente. Inventario actualizado.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
