<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Api\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private const ROLES = ['administracion', 'gestion', 'revision', 'consulta', 'decano'];
    private const ADMIN_ROLES = ['administracion', 'administrador', 'admin'];

    public function index()
    {
        return User::query()
            ->where('rol', 'revision')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'rol']);
    }

    public function manageIndex(Request $request)
    {
        $this->authorizeAdministrator($request);

        return User::query()
            ->where('id', '!=', $request->user()->id)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'rol', 'telefono']);
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeAdministrator($request);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'rol' => ['required', Rule::in(self::ROLES)],
            'telefono' => 'nullable|string|max:45',
        ]);

        if ($request->user()->is($user) && ! in_array($data['rol'], self::ADMIN_ROLES, true)) {
            return response()->json([
                'message' => 'No puedes quitarte el rol de administrador de tu propia cuenta.',
            ], 422);
        }

        $user->update($data);

        return $user->only(['id', 'name', 'email', 'rol', 'telefono']);
    }

    public function destroy(Request $request, User $user)
    {
        $this->authorizeAdministrator($request);

        if ($request->user()->is($user)) {
            return response()->json([
                'message' => 'No puedes eliminar la cuenta con la que iniciaste sesión.',
            ], 409);
        }

        if (DB::table('historial_estados')->where('user_id', $user->id)->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar este usuario porque figura en el historial de convenios.',
            ], 409);
        }

        $user->delete();

        return response()->noContent();
    }

    private function authorizeAdministrator(Request $request): void
    {
        $role = strtolower((string) $request->user()->rol);
        abort_unless(in_array($role, self::ADMIN_ROLES, true), 403, 'Solo Administración puede gestionar usuarios.');
    }
}
