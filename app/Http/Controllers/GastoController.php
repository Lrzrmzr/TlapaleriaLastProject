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
        $gastos = Gasto::latest()->get();
        $user = Auth::user();
        return Inertia::render('Gastos/Index', [
            'gastos' => $gastos,
            'user' => $user,
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
        if ($user->role !== 'admin') {
            abort(403, 'Solo el administrador puede eliminar gastos');
        }
        $gasto->delete();
        return redirect()->back();
    }
}
