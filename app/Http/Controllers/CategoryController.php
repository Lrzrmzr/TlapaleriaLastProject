<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     */
    public function index(Request $request)
    {
        // Si es una petición API, retornar JSON
        if ($request->expectsJson()) {
            $query = Category::query();

            // Filtro: Solo activas
            if ($request->has('active')) {
                $query->where('active', $request->boolean('active'));
            }

            // Búsqueda por nombre
            if ($request->has('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            // Ordenar
            $query->ordered();

            // Incluir conteo de productos si se solicita
            if ($request->boolean('with_products_count')) {
                $query->withCount('products');
            }

            // Paginación o todo
            if ($request->boolean('all')) {
                $categories = $query->get();
            } else {
                $categories = $query->paginate($request->get('per_page', 15));
            }

            return response()->json($categories);
        }

        // Para la vista web con Inertia
        $categories = Category::withCount('products')
            ->ordered()
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'icon' => $category->icon,
                    'color' => $category->color,
                    'active' => $category->active,
                    'order' => $category->order,
                    'products_count' => $category->products_count,
                    'created_at' => $category->created_at->format('d/m/Y'),
                ];
            });

        return Inertia::render('Categorias/Index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
            'slug' => 'nullable|string|max:100|unique:categories,slug',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/', // Validar formato hex
            'active' => 'boolean',
            'order' => 'integer|min:0',
        ]);

        // Generar slug si no se proporciona
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category = Category::create($validated);

        return redirect()->route('categorias.index')
            ->with('success', 'Categoría creada exitosamente');
    }

    /**
     * Display the specified category.
     */
    public function show($id)
    {
        $category = Category::with('products')->findOrFail($id);

        return response()->json($category);
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'name')->ignore($category->id)
            ],
            'slug' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('categories', 'slug')->ignore($category->id)
            ],
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'active' => 'boolean',
            'order' => 'integer|min:0',
        ]);

        // Regenerar slug si cambió el nombre
        if (isset($validated['name']) && $validated['name'] !== $category->name) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category->update($validated);

        return redirect()->route('categorias.index')
            ->with('success', 'Categoría actualizada exitosamente');
    }

    /**
     * Remove the specified category.
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        // Verificar si tiene productos asociados
        $productsCount = $category->products()->count();

        if ($productsCount > 0) {
            return redirect()->route('categorias.index')
                ->with('error', "No se puede eliminar la categoría '{$category->name}' porque tiene {$productsCount} producto(s) asociado(s).");
        }

        $category->delete();

        return redirect()->route('categorias.index')
            ->with('success', 'Categoría eliminada exitosamente');
    }

    /**
     * Obtener todas las categorías activas (para selectores)
     */
    public function active()
    {
        $categories = Category::active()
            ->ordered()
            ->select('id', 'name', 'slug', 'icon', 'color')
            ->get();

        return response()->json($categories);
    }

    /**
     * Reordenar categorías
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'categories' => 'required|array',
            'categories.*.id' => 'required|exists:categories,id',
            'categories.*.order' => 'required|integer|min:0',
        ]);

        foreach ($validated['categories'] as $item) {
            Category::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json([
            'message' => 'Categorías reordenadas exitosamente'
        ]);
    }
}
