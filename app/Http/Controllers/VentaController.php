<?php
namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $productos = Product::all();
        $user = Auth::user();

        return Inertia::render('Ventas/Index', [
            'ventas' => $ventas,
            'productos' => $productos,
            'user' => $user,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'descripcion' => 'required|string|max:255',
            'total' => 'required|numeric|min:0',
        ]);

        $totalVenta = $request->total;

        // Crear la venta principal con el total
        $venta = Sale::create([
            'user_id' => Auth::id(),
            'customer_id' => null, // Cliente general
            'total' => $totalVenta,
            'status' => 'completed'
        ]);

        // Crear el detalle de venta LIBRE
        SaleDetail::create([
            'sale_id' => $venta->id,
            'product_id' => null, // NULL para ventas libres
            'tipo_venta' => 'libre', // Indicar que es venta libre
            'descripcion' => $request->descripcion,
            'quantity' => 1, // Cantidad por defecto
            'price' => $request->total, // El total es el precio
            'subtotal' => $request->total,
            'total' => $request->total
        ]);

        return redirect()->back()->with('success', 'Venta registrada correctamente');
    }
}
