<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'email'    => 'required|email',
                'password' => 'required|string',
            ],
            [
                'email.required'    => 'El correo electrónico es obligatorio.',
                'email.email'       => 'El correo electrónico no es válido.',
                'password.required' => 'La contraseña es obligatoria.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = User::with([
            'organizationalUnits' => function ($query) {
                $query->select('fa_organizational_units.id', 'fa_organizational_units.name');
            },
            'employee:id,user_id',
        ])->where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales incorrectas.',
            ], 401);
        }

        // Eliminar tokens previos
        $user->tokens()->delete();

        // Crear token Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token'   => $token,
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'employee_id' => $user->employee?->id,
                'organizational_units' => $user->organizationalUnits,
                'roles' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ],
        ]);
    }

    public function signout(Request $request)
    {
        return $request;
    }
}
