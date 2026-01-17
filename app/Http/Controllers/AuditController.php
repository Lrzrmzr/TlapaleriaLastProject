<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class AuditController extends Controller
{
    /**
     * Muestra el dashboard de auditoría con todos los logs
     */
    public function index(Request $request)
    {
        $query = Activity::with(['causer', 'subject'])
            ->latest();

        // Filtrar por búsqueda general (descripción personalizada o ID)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('id', $search)
                  ->orWhereHas('causer', function($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filtrar por usuario si se proporciona
        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->user_id)
                  ->where('causer_type', 'App\\Models\\User');
        }

        // Filtrar por modelo si se proporciona
        if ($request->filled('model')) {
            $query->where('subject_type', $request->model);
        }

        // Filtrar por fecha desde
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        // Filtrar por fecha hasta
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filtrar por descripción/acción (created, updated, deleted)
        if ($request->filled('action')) {
            $query->where('description', 'like', "%{$request->action}%");
        }

        // Filtrar por sucursal (si el log tiene properties con branch_id)
        if ($request->filled('branch_id')) {
            $query->where(function($q) use ($request) {
                $q->whereJsonContains('properties->attributes->branch_id', (int)$request->branch_id)
                  ->orWhereJsonContains('properties->old->branch_id', (int)$request->branch_id);
            });
        }

        // Paginación: por defecto 10 registros (últimos cambios)
        $perPage = $request->get('per_page', 10);
        $activities = $query->paginate($perPage)->through(function ($activity) {
            return [
                'id' => $activity->id,
                'log_name' => $activity->log_name,
                'description' => $activity->description,
                'subject_type' => $activity->subject_type,
                'subject_id' => $activity->subject_id,
                'causer_type' => $activity->causer_type,
                'causer_id' => $activity->causer_id,
                'causer_name' => $activity->causer ? $activity->causer->name : 'Sistema',
                'properties' => $activity->properties,
                'created_at' => $activity->created_at->format('Y-m-d H:i:s'),
                'created_at_human' => $activity->created_at->diffForHumans(),
                // Formato amigable del modelo
                'model_name' => $this->getModelName($activity->subject_type),
                // Traducción de la acción
                'action_translated' => $this->translateAction($activity->description),
            ];
        });

        // Obtener lista de usuarios para el filtro
        $users = \App\Models\User::select('id', 'name')->orderBy('name')->get();

        // Obtener lista de sucursales para el filtro (si es root)
        $currentUser = $request->user();
        $branches = $currentUser && $currentUser->isRoot()
            ? \App\Models\Branch::select('id', 'name')->orderBy('name')->get()
            : collect([]);

        // Modelos disponibles
        $models = [
            'App\\Models\\Product' => 'Productos',
            'App\\Models\\Sale' => 'Ventas',
            'App\\Models\\Purchase' => 'Compras',
            'App\\Models\\User' => 'Usuarios',
            'App\\Models\\Customer' => 'Clientes',
            'App\\Models\\Supplier' => 'Proveedores',
            'App\\Models\\BranchInventory' => 'Inventario',
        ];

        return Inertia::render('Auditoria/Index', [
            'activities' => $activities,
            'users' => $users,
            'branches' => $branches,
            'models' => $models,
            'filters' => [
                'search' => $request->search,
                'user_id' => $request->user_id,
                'model' => $request->model,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
                'action' => $request->action,
                'branch_id' => $request->branch_id,
                'per_page' => $perPage,
            ],
        ]);
    }

    /**
     * Convierte el nombre del modelo a un formato amigable
     */
    private function getModelName($modelType)
    {
        $models = [
            'App\\Models\\Product' => 'Producto',
            'App\\Models\\Sale' => 'Venta',
            'App\\Models\\Purchase' => 'Compra',
            'App\\Models\\User' => 'Usuario',
            'App\\Models\\Customer' => 'Cliente',
            'App\\Models\\Supplier' => 'Proveedor',
            'App\\Models\\BranchInventory' => 'Inventario',
            'App\\Models\\Branch' => 'Sucursal',
            'App\\Models\\Category' => 'Categoría',
        ];

        return $models[$modelType] ?? class_basename($modelType);
    }

    /**
     * Traduce la acción al español
     */
    private function translateAction($action)
    {
        $actions = [
            'created' => 'Creado',
            'updated' => 'Actualizado',
            'deleted' => 'Eliminado',
            'restored' => 'Restaurado',
        ];

        return $actions[$action] ?? ucfirst($action);
    }

    /**
     * Muestra los detalles de un log específico
     */
    public function show($id)
    {
        $activity = Activity::with(['causer', 'subject'])->findOrFail($id);

        return Inertia::render('Auditoria/Show', [
            'activity' => [
                'id' => $activity->id,
                'log_name' => $activity->log_name,
                'description' => $activity->description,
                'subject_type' => $activity->subject_type,
                'subject_id' => $activity->subject_id,
                'causer_type' => $activity->causer_type,
                'causer_id' => $activity->causer_id,
                'causer_name' => $activity->causer ? $activity->causer->name : 'Sistema',
                'properties' => $activity->properties,
                'created_at' => $activity->created_at->format('Y-m-d H:i:s'),
                'model_name' => $this->getModelName($activity->subject_type),
                'action_translated' => $this->translateAction($activity->description),
            ],
        ]);
    }
}
