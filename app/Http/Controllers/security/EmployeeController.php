<?php

namespace App\Http\Controllers\security;

use App\Http\Controllers\Controller;
use App\Models\AdmGender;
use App\Models\AdmMaritalStatus;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function formOptions()
    {
        $genders = AdmGender::query()
            ->where('active', true)
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get(['id', 'name']);

        $maritalStatuses = AdmMaritalStatus::query()
            ->where('active', true)
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get(['id', 'name']);

        $users = User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'lastname', 'email']);

        return response()->json([
            'success' => true,
            'data' => [
                'genders' => $genders,
                'marital_statuses' => $maritalStatuses,
                'users' => $users,
            ],
        ]);
    }

    public function index()
    {
        $employees = Employee::query()
            ->with([
                'user:id,name,lastname,email',
                'gender:id,name',
                'maritalStatus:id,name',
            ])
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $employees,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedPayload($request);

        $employee = Employee::create($data);

        $employee->load(['user', 'gender', 'maritalStatus']);

        return response()->json([
            'success' => true,
            'message' => 'Empleado creado correctamente.',
            'data' => $employee,
        ], 201);
    }

    public function show(string $id)
    {
        $employee = Employee::query()
            ->with(['user', 'gender', 'maritalStatus'])
            ->find($id);

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Empleado no encontrado.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $employee,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $employee = Employee::query()->find($id);

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Empleado no encontrado.',
            ], 404);
        }

        $data = $this->validatedPayload($request, (int) $employee->id);

        $employee->update($data);
        $employee->load(['user', 'gender', 'maritalStatus']);

        return response()->json([
            'success' => true,
            'message' => 'Empleado actualizado correctamente.',
            'data' => $employee,
        ]);
    }

    public function destroy(string $id)
    {
        $employee = Employee::query()->find($id);

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Empleado no encontrado.',
            ], 404);
        }

        $employee->delete();

        return response()->json([
            'success' => true,
            'message' => 'Empleado eliminado correctamente.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedPayload(Request $request, ?int $ignoreEmployeeId = null): array
    {
        $emailRule = Rule::unique('adm_employees', 'email');
        $emailPersonalRule = Rule::unique('adm_employees', 'email_personal');
        if ($ignoreEmployeeId !== null) {
            $emailRule = $emailRule->ignore($ignoreEmployeeId);
            $emailPersonalRule = $emailPersonalRule->ignore($ignoreEmployeeId);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', $emailRule],
            'email_personal' => ['nullable', 'email', 'max:255', $emailPersonalRule],
            'phone' => 'nullable|string|max:50',
            'phone_personal' => 'nullable|string|max:50',
            'photo_name' => 'nullable|string|max:255',
            'photo_route' => 'nullable|string|max:500',
            'photo_route_sm' => 'nullable|string|max:500',
            'birthday' => 'nullable|date',
            'marking_required' => 'nullable|boolean',
            'status' => 'required|integer|min:0|max:32767',
            'active' => 'nullable|boolean',
            'user_id' => 'nullable|integer|exists:users,id',
            'adm_gender_id' => 'nullable|integer|exists:adm_genders,id',
            'adm_marital_status_id' => 'nullable|integer|exists:adm_marital_statuses,id',
            'remote_mark' => 'nullable|boolean',
            'external' => 'nullable|boolean',
            'viatic' => 'nullable|boolean',
            'children' => 'nullable|boolean',
            'unsubscribe_justification' => 'nullable|string',
            'vehicle' => 'nullable|boolean',
            'adhonorem' => 'nullable|boolean',
            'parking' => 'nullable|boolean',
            'disabled' => 'nullable|boolean',
        ]);

        if (array_key_exists('email_personal', $validated) && $validated['email_personal'] === '') {
            $validated['email_personal'] = null;
        }

        $defaults = [
            'marking_required' => true,
            'active' => true,
            'remote_mark' => false,
            'external' => false,
            'viatic' => false,
            'children' => false,
            'vehicle' => false,
            'adhonorem' => false,
            'parking' => false,
            'disabled' => false,
        ];

        foreach ($defaults as $key => $default) {
            if (!array_key_exists($key, $validated)) {
                $validated[$key] = $default;
            }
        }

        return $validated;
    }
}
