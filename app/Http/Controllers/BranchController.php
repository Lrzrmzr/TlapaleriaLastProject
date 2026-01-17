<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BranchInventory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class BranchController extends Controller
{
    /**
     * Display a listing of branches
     */
    public function index(Request $request)
    {
        $branches = Branch::withCount(['users', 'inventory'])
            ->orderBy('is_main', 'desc')
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($branch) {
                return [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'code' => $branch->code,
                    'address' => $branch->address,
                    'phone' => $branch->phone,
                    'email' => $branch->email,
                    'manager_name' => $branch->manager_name,
                    'active' => $branch->active,
                    'is_main' => $branch->is_main,
                    'notes' => $branch->notes,
                    'users_count' => $branch->users_count,
                    'inventory_count' => $branch->inventory_count,
                    'total_stock' => $branch->total_stock,
                    'created_at' => $branch->created_at->format('d/m/Y'),
                ];
            });

        if ($request->expectsJson()) {
            return response()->json($branches);
        }

        return Inertia::render('Sucursales/Index', [
            'branches' => $branches,
        ]);
    }

    /**
     * Store a newly created branch
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:branches,code',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'manager_name' => 'nullable|string|max:100',
            'active' => 'boolean',
            'is_main' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        // Si no se proporciona código, generarlo automáticamente
        if (empty($validated['code'])) {
            $validated['code'] = Str::upper(Str::slug($validated['name'], ''));
        }

        // Solo puede haber una sucursal principal
        if ($validated['is_main'] ?? false) {
            Branch::where('is_main', true)->update(['is_main' => false]);
        }

        $branch = Branch::create($validated);

        return redirect()->back()->with('success', 'Sucursal creada exitosamente');
    }

    /**
     * Update the specified branch
     */
    public function update(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:branches,code,' . $id,
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'manager_name' => 'nullable|string|max:100',
            'active' => 'boolean',
            'is_main' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        // Solo puede haber una sucursal principal
        if ($validated['is_main'] ?? false) {
            Branch::where('id', '!=', $id)->where('is_main', true)->update(['is_main' => false]);
        }

        $branch->update($validated);

        return redirect()->back()->with('success', 'Sucursal actualizada exitosamente');
    }

    /**
     * Soft delete the specified branch (cambiar status)
     */
    public function destroy($id)
    {
        $branch = Branch::findOrFail($id);

        // No permitir eliminar la sucursal principal
        if ($branch->is_main) {
            return redirect()->back()->withErrors(['error' => 'No se puede eliminar la sucursal principal']);
        }

        // Verificar si tiene usuarios activos
        if ($branch->users()->active()->count() > 0) {
            return redirect()->back()->withErrors(['error' => 'No se puede desactivar una sucursal con usuarios activos. Por favor, reasigne los usuarios primero.']);
        }

        // Cambiar status a inactivo en lugar de eliminar
        $branch->update(['active' => false]);

        // Soft delete
        $branch->delete();

        return redirect()->back()->with('success', 'Sucursal desactivada exitosamente');
    }

    /**
     * Restore a soft deleted branch
     */
    public function restore($id)
    {
        $branch = Branch::withTrashed()->findOrFail($id);
        $branch->restore();
        $branch->update(['active' => true]);

        return redirect()->back()->with('success', 'Sucursal restaurada exitosamente');
    }

    /**
     * Toggle branch active status
     */
    public function toggleStatus($id)
    {
        $branch = Branch::findOrFail($id);

        if ($branch->is_main && $branch->active) {
            return redirect()->back()->withErrors(['error' => 'No se puede desactivar la sucursal principal']);
        }

        $branch->update(['active' => !$branch->active]);

        $status = $branch->active ? 'activada' : 'desactivada';
        return redirect()->back()->with('success', "Sucursal {$status} exitosamente");
    }

    /**
     * Get active branches for selectors
     */
    public function active()
    {
        $branches = Branch::active()
            ->orderBy('is_main', 'desc')
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($branch) {
                return [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'code' => $branch->code,
                    'is_main' => $branch->is_main,
                ];
            });

        return response()->json($branches);
    }

    /**
     * Get branch inventory
     */
    public function inventory($id)
    {
        $branch = Branch::findOrFail($id);

        $inventory = $branch->inventory()
            ->with('product')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'stock' => $item->stock,
                    'min_stock' => $item->min_stock,
                    'max_stock' => $item->max_stock,
                    'cost' => $item->cost,
                    'active' => $item->active,
                    'is_low_stock' => $item->isLowStock(),
                    'is_out_of_stock' => $item->isOutOfStock(),
                ];
            });

        return response()->json([
            'branch' => [
                'id' => $branch->id,
                'name' => $branch->name,
                'code' => $branch->code,
            ],
            'inventory' => $inventory,
        ]);
    }

    /**
     * Get branch statistics
     */
    public function statistics($id)
    {
        $branch = Branch::withCount(['users', 'inventory', 'sales', 'purchases'])
            ->findOrFail($id);

        $stats = [
            'users_count' => $branch->users_count,
            'inventory_count' => $branch->inventory_count,
            'sales_count' => $branch->sales_count,
            'purchases_count' => $branch->purchases_count,
            'total_stock' => $branch->total_stock,
            'total_inventory_value' => $branch->total_inventory_value,
        ];

        return response()->json($stats);
    }
}
