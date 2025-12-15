<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplierController extends Controller
{
    public function index()
    {
        $proveedores = Supplier::withCount('productsSupplied')->get()->map(function ($supplier) {
            return [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'contact_name' => $supplier->contact_name,
                'phone' => $supplier->phone,
                'email' => $supplier->email,
                'address' => $supplier->address,
                'products_count' => $supplier->products_supplied_count,
                'created_at' => $supplier->created_at ? $supplier->created_at->format('d/m/Y') : 'N/A',
            ];
        });

        $stats = [
            'total' => Supplier::count(),
            'withProducts' => Supplier::has('productsSupplied')->count(),
            'withoutProducts' => Supplier::doesntHave('productsSupplied')->count(),
        ];

        return Inertia::render('Proveedores/Index', [
            'proveedores' => $proveedores,
            'stats' => $stats,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
        ]);

        Supplier::create([
            'name' => $request->name,
            'contact_name' => $request->contact_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
        ]);

        return redirect()->back()->with('success', 'Proveedor creado exitosamente');
    }

    public function update(Request $request, Supplier $proveedor)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
        ]);

        $proveedor->update([
            'name' => $request->name,
            'contact_name' => $request->contact_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
        ]);

        return redirect()->back()->with('success', 'Proveedor actualizado exitosamente');
    }

    public function destroy(Supplier $proveedor)
    {
        // Verificar si tiene productos asignados
        if ($proveedor->productsSupplied()->exists()) {
            return redirect()->back()->withErrors(['error' => 'No se puede eliminar un proveedor que tiene productos asignados.']);
        }

        $proveedor->delete();
        return redirect()->back()->with('success', 'Proveedor eliminado exitosamente');
    }
}
