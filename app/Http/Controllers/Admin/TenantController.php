<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class TenantController extends Controller
{
    /**
     * List all tenants (root only panel).
     */
    public function index()
    {
        $tenants = Tenant::withoutTenant()
            ->withCount(['users', 'branches'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($t) => [
                'id'             => $t->id,
                'name'           => $t->name,
                'rfc'            => $t->rfc,
                'email'          => $t->email,
                'phone'          => $t->phone,
                'status'         => $t->status,
                'plan'           => $t->plan,
                'trial_ends_at'  => $t->trial_ends_at?->format('d/m/Y'),
                'users_count'    => $t->users_count,
                'branches_count' => $t->branches_count,
                'created_at'     => $t->created_at->format('d/m/Y'),
            ]);

        return Inertia::render('Admin/Tenants/Index', [
            'tenants' => $tenants,
        ]);
    }

    /**
     * Create a new tenant.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id'             => 'required|string|max:100|unique:tenants,id|regex:/^[a-z0-9\-]+$/',
            'name'           => 'required|string|max:200',
            'rfc'            => 'nullable|string|max:13',
            'email'          => 'required|email|max:200',
            'phone'          => 'nullable|string|max:20',
            'plan'           => 'required|in:free,starter,pro,enterprise',
            'status'         => 'required|in:trial,active,suspended,cancelled',
            'trial_ends_at'  => 'nullable|date',
        ]);

        Tenant::create($validated);

        return redirect()->back()->with('success', 'Empresa creada exitosamente.');
    }

    /**
     * Update an existing tenant.
     */
    public function update(Request $request, string $id)
    {
        $tenant = Tenant::findOrFail($id);

        $validated = $request->validate([
            'name'           => 'required|string|max:200',
            'rfc'            => 'nullable|string|max:13',
            'email'          => 'required|email|max:200',
            'phone'          => 'nullable|string|max:20',
            'plan'           => 'required|in:free,starter,pro,enterprise',
            'status'         => 'required|in:trial,active,suspended,cancelled',
            'trial_ends_at'  => 'nullable|date',
        ]);

        $tenant->update($validated);

        return redirect()->back()->with('success', 'Empresa actualizada exitosamente.');
    }

    /**
     * Toggle suspended / active status for a tenant.
     */
    public function suspend(string $id)
    {
        $tenant = Tenant::findOrFail($id);

        $newStatus = $tenant->isSuspended() ? 'active' : 'suspended';
        $tenant->update(['status' => $newStatus]);

        $msg = $newStatus === 'suspended' ? 'suspendida' : 'reactivada';

        return redirect()->back()->with('success', "Empresa {$msg} exitosamente.");
    }

    /**
     * Impersonate the first admin user of a tenant.
     * Logs out current root session and logs in as that user.
     */
    public function impersonate(string $id)
    {
        $tenant = Tenant::findOrFail($id);

        // Find the first admin/root user of this tenant
        $user = User::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->whereIn('role', ['admin', 'root', 'manager'])
            ->orderByRaw("CASE role WHEN 'admin' THEN 1 WHEN 'manager' THEN 2 ELSE 3 END")
            ->first();

        if (!$user) {
            return redirect()->back()->withErrors(['error' => 'No se encontró un usuario administrador para esta empresa.']);
        }

        // Store original root user ID in session to allow returning
        session(['impersonating_tenant' => $tenant->id, 'original_user_id' => auth()->id()]);

        auth()->login($user);

        return redirect()->route('dashboard')->with('success', "Ingresaste como administrador de {$tenant->name}.");
    }

    /**
     * End impersonation and return to root session.
     */
    public function stopImpersonating()
    {
        $originalUserId = session('original_user_id');

        if (!$originalUserId) {
            return redirect()->route('dashboard');
        }

        $originalUser = User::withoutGlobalScopes()->findOrFail($originalUserId);

        session()->forget(['impersonating_tenant', 'original_user_id']);

        auth()->login($originalUser);

        return redirect()->route('admin.tenants.index')->with('success', 'Sesión de impersonación finalizada.');
    }
}
