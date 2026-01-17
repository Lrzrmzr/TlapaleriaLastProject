<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gasto;
use Illuminate\Http\Request;

class GastoApiController extends Controller
{
    /**
     * Listar gastos
     */
    public function index(Request $request)
    {
        $query = Gasto::with('user')->latest();

        // Filtro por fecha
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $perPage = $request->get('per_page', 50);
        $gastos = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $gastos->items(),
            'pagination' => [
                'total' => $gastos->total(),
                'per_page' => $gastos->perPage(),
                'current_page' => $gastos->currentPage(),
                'last_page' => $gastos->lastPage(),
            ],
            'stats' => [
                'total' => $gastos->sum('total'),
            ]
        ]);
    }

    /**
     * Crear un nuevo gasto
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'descripcion' => 'required|string|max:255',
            'total' => 'required|numeric|min:0',
        ]);

        $validated['user_id'] = $request->user()->id;

        $gasto = Gasto::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Gasto registrado exitosamente',
            'data' => $gasto->load('user')
        ], 201);
    }

    /**
     * Obtener un gasto específico
     */
    public function show($id)
    {
        $gasto = Gasto::with('user')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $gasto
        ]);
    }

    /**
     * Actualizar un gasto
     */
    public function update(Request $request, $id)
    {
        $gasto = Gasto::findOrFail($id);

        $validated = $request->validate([
            'descripcion' => 'sometimes|required|string|max:255',
            'total' => 'sometimes|required|numeric|min:0',
        ]);

        $gasto->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Gasto actualizado exitosamente',
            'data' => $gasto->load('user')
        ]);
    }

    /**
     * Eliminar un gasto
     */
    public function destroy($id)
    {
        $user = request()->user();
        $userRole = $user->roles->first();

        if (!$userRole || ($userRole->name !== 'administrador' && $userRole->name !== 'root')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para eliminar gastos'
            ], 403);
        }

        $gasto = Gasto::findOrFail($id);
        $gasto->delete();

        return response()->json([
            'success' => true,
            'message' => 'Gasto eliminado exitosamente'
        ]);
    }
}
