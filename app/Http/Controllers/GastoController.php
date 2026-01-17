<?php
namespace App\Http\Controllers;

use App\Models\Gasto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class GastoController extends Controller
{
    public function index()
    {
        $gastos = Gasto::with('user')
            ->latest()
            ->get()
            ->map(function ($gasto) {
                return [
                    'id' => $gasto->id,
                    'descripcion' => $gasto->descripcion,
                    'total' => $gasto->total,
                    'created_at' => $gasto->created_at->toISOString(),
                    'user' => $gasto->user ? [
                        'id' => $gasto->user->id,
                        'name' => $gasto->user->name,
                    ] : null,
                ];
            });

        $user = Auth::user();

        return Inertia::render('Gastos/Index', [
            'gastos' => $gastos,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->roles->first()?->name ?? null,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'descripcion' => 'required|string|max:255',
            'total' => 'required|numeric',
        ]);
        Gasto::create([
            'descripcion' => $request->descripcion,
            'total' => $request->total,
            'user_id' => Auth::id(),
        ]);
        return redirect()->back();
    }

    public function destroy(Gasto $gasto)
    {
        $user = Auth::user();
        $userRole = $user->roles->first();

        if (!$userRole || ($userRole->name !== 'administrador' && $userRole->name !== 'root')) {
            abort(403, 'Solo el administrador puede eliminar gastos');
        }

        $gasto->delete();
        return redirect()->back();
    }
}
