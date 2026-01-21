<?php

namespace App\Http\Controllers\security;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('roles')->get();

        // Agregar información del rol al objeto de usuario para compatibilidad con el frontend
        $users = $users->map(function ($user) {
            $userArray = $user->toArray();
            $userArray['role'] = $user->roles->first(); // Primer rol del usuario
            $userArray['role_id'] = $user->roles->first()?->id; // ID del primer rol
            return $userArray;
        });

        return response()->json([
            'success' => true,
            'data'    => $users,
        ]);
    }

    public function store(Request $request)
    {
        $rules = [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role_id'  => 'required|exists:roles,id',
        ];

        $messages = [
            'name.required'     => 'El nombre es obligatorio.',
            'name.string'       => 'El nombre debe ser texto.',
            'name.max'          => 'El nombre no debe exceder 255 caracteres.',

            'email.required'    => 'El correo electrónico es obligatorio.',
            'email.email'       => 'El correo electrónico no es válido.',
            'email.unique'      => 'Ya existe un usuario con este correo electrónico.',

            'password.required' => 'La contraseña es obligatoria.',
            'password.string'   => 'La contraseña debe ser texto.',
            'password.min'      => 'La contraseña debe tener al menos 6 caracteres.',

            'role_id.required'  => 'Debe seleccionar un rol.',
            'role_id.exists'    => 'El rol seleccionado no existe.',
        ];

        $request->validate($rules, $messages);


        try {
            DB::beginTransaction();

            // Crear el usuario
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Asignar el rol
            $role = Role::find($request->role_id);
            if ($role) {
                $user->assignRole($role);
            }

            DB::commit();

            // Cargar el usuario con sus roles
            $user->load('roles');

            return response()->json([
                'success' => true,
                'message' => 'Usuario creado correctamente.',
                'data'    => $user,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el usuario.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id)
    {
        $user = User::with(['roles', 'offices'])->find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado.',
            ], 404);
        }

        // Agregar información del rol al objeto de usuario para compatibilidad con el frontend
        $userArray = $user->toArray();
        $userArray['role'] = $user->roles->first();
        $userArray['role_id'] = $user->roles->first()?->id;

        return response()->json([
            'success' => true,
            'data'    => $userArray,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado.',
            ], 404);
        }
        $rules = [
            'name'     => 'sometimes|required|string|max:255',
            'email'    => 'sometimes|required|email|unique:users,email,' . $id,
            'password' => 'sometimes|nullable|string|min:6',
        ];

        $messages = [
            // name
            'name.required' => 'El nombre es obligatorio.',
            'name.string'   => 'El nombre debe ser texto.',
            'name.max'      => 'El nombre no debe exceder 255 caracteres.',

            // email
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email'    => 'El correo electrónico no es válido.',
            'email.unique'   => 'Ya existe un usuario con este correo electrónico.',

            // password
            'password.string' => 'La contraseña debe ser texto.',
            'password.min'    => 'La contraseña debe tener al menos 6 caracteres.',
        ];

        $request->validate($rules, $messages);

        try {
            DB::beginTransaction();

            // Actualizar los datos básicos del usuario
            if ($request->has('name')) {
                $user->name = $request->name;
            }

            if ($request->has('email')) {
                $user->email = $request->email;
            }

            // Actualizar la contraseña solo si se proporciona
            if ($request->has('password') && !empty($request->password)) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            // Nota: Los roles se gestionan por separado en el método syncRoles

            DB::commit();

            // Cargar el usuario con sus roles
            $user->load('roles');

            // Agregar información del rol al objeto de usuario para compatibilidad con el frontend
            $userArray = $user->toArray();
            $userArray['role'] = $user->roles->first();
            $userArray['role_id'] = $user->roles->first()?->id;

            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado correctamente.',
                'data'    => $userArray,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el usuario.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function syncRoles(Request $request, string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado.',
            ], 404);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'role_ids' => 'required|array',
                'role_ids.*' => 'exists:roles,id',
            ],
            [
                'role_ids.required' => 'Debe proporcionar un array de roles (puede estar vacío).',
                'role_ids.array'    => 'Los roles deben ser un array.',
                'role_ids.*.exists' => 'Uno o más roles no existen.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            // Sincronizar roles (asignar y remover según el array proporcionado)
            $user->syncRoles($request->role_ids);

            // Cargar el usuario con sus roles
            $user->load('roles');

            return response()->json([
                'success' => true,
                'message' => 'Roles actualizados correctamente.',
                'data'    => $user,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar los roles.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function syncOffices(Request $request, string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado.',
            ], 404);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'office_ids' => 'required|array',
                'office_ids.*' => 'exists:wh_offices,id',
            ],
            [
                'office_ids.required' => 'Debe proporcionar un array de oficinas (puede estar vacío).',
                'office_ids.array'    => 'Las oficinas deben ser un array.',
                'office_ids.*.exists' => 'Uno o más oficinas no existen.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            // Sincronizar oficinas (asignar y remover según el array proporcionado)
            $user->offices()->sync($request->office_ids);

            // Cargar el usuario con sus oficinas
            $user->load('offices');

            return response()->json([
                'success' => true,
                'message' => 'Oficinas actualizadas correctamente.',
                'data'    => $user,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar las oficinas.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado.',
            ], 404);
        }

        try {
            // Eliminar roles asociados
            $user->roles()->detach();

            // Eliminar el usuario
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado correctamente.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el usuario.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function getAdministrativeTechnicians()
    {
        $users = User::whereHas('roles', function ($query) {
            $query->where('id', 2);
        })
            ->where('status', 1)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $users
        ]);
    }

    public function getAreaManagers()
    {
        $users = User::whereHas('roles', function ($query) {
            $query->where('id', 4);
        })
            ->where('status', 1)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $users
        ]);
    }


    /*
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name'     => 'required|string|max:255',
                'email'    => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
                'role_id'  => 'required|exists:roles,id',
            ],
            [
                'name.required'     => 'El nombre es obligatorio.',
                'email.required'    => 'El email es obligatorio.',
                'email.email'       => 'El email no es válido.',
                'email.unique'      => 'Ya existe un usuario con este email.',
                'password.required' => 'La contraseña es obligatoria.',
                'password.min'      => 'La contraseña debe tener al menos 6 caracteres.',
                'role_id.required'  => 'Debe seleccionar un rol.',
                'role_id.exists'    => 'El rol seleccionado no existe.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Crear el usuario
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Asignar el rol
            $role = Role::find($request->role_id);
            if ($role) {
                $user->assignRole($role);
            }

            DB::commit();

            // Cargar el usuario con sus roles
            $user->load('roles');

            return response()->json([
                'success' => true,
                'message' => 'Usuario creado correctamente.',
                'data'    => $user,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el usuario.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function show(string $id)
    {
        $user = User::with(['roles', 'offices'])->find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado.',
            ], 404);
        }

        // Agregar información del rol al objeto de usuario para compatibilidad con el frontend
        $userArray = $user->toArray();
        $userArray['role'] = $user->roles->first();
        $userArray['role_id'] = $user->roles->first()?->id;

        return response()->json([
            'success' => true,
            'data'    => $userArray,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado.',
            ], 404);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'name'     => 'sometimes|required|string|max:255',
                'email'    => 'sometimes|required|email|unique:users,email,' . $id,
                'password' => 'sometimes|nullable|string|min:6',
            ],
            [
                'name.required'     => 'El nombre es obligatorio.',
                'email.required'    => 'El email es obligatorio.',
                'email.email'       => 'El email no es válido.',
                'email.unique'      => 'Ya existe un usuario con este email.',
                'password.min'      => 'La contraseña debe tener al menos 6 caracteres.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Actualizar los datos básicos del usuario
            if ($request->has('name')) {
                $user->name = $request->name;
            }

            if ($request->has('email')) {
                $user->email = $request->email;
            }

            // Actualizar la contraseña solo si se proporciona
            if ($request->has('password') && !empty($request->password)) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            // Nota: Los roles se gestionan por separado en el método syncRoles

            DB::commit();

            // Cargar el usuario con sus roles
            $user->load('roles');

            // Agregar información del rol al objeto de usuario para compatibilidad con el frontend
            $userArray = $user->toArray();
            $userArray['role'] = $user->roles->first();
            $userArray['role_id'] = $user->roles->first()?->id;

            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado correctamente.',
                'data'    => $userArray,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el usuario.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function syncRoles(Request $request, string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado.',
            ], 404);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'role_ids' => 'required|array',
                'role_ids.*' => 'exists:roles,id',
            ],
            [
                'role_ids.required' => 'Debe proporcionar un array de roles (puede estar vacío).',
                'role_ids.array'    => 'Los roles deben ser un array.',
                'role_ids.*.exists' => 'Uno o más roles no existen.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            // Sincronizar roles (asignar y remover según el array proporcionado)
            $user->syncRoles($request->role_ids);

            // Cargar el usuario con sus roles
            $user->load('roles');

            return response()->json([
                'success' => true,
                'message' => 'Roles actualizados correctamente.',
                'data'    => $user,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar los roles.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function syncOffices(Request $request, string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado.',
            ], 404);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'office_ids' => 'required|array',
                'office_ids.*' => 'exists:wh_offices,id',
            ],
            [
                'office_ids.required' => 'Debe proporcionar un array de oficinas (puede estar vacío).',
                'office_ids.array'    => 'Las oficinas deben ser un array.',
                'office_ids.*.exists' => 'Uno o más oficinas no existen.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            // Sincronizar oficinas (asignar y remover según el array proporcionado)
            $user->offices()->sync($request->office_ids);

            // Cargar el usuario con sus oficinas
            $user->load('offices');

            return response()->json([
                'success' => true,
                'message' => 'Oficinas actualizadas correctamente.',
                'data'    => $user,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar las oficinas.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function destroy(string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado.',
            ], 404);
        }

        try {
            // Eliminar roles asociados
            $user->roles()->detach();

            // Eliminar el usuario
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado correctamente.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el usuario.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }*/
}
