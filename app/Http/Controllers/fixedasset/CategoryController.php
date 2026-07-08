<?php

namespace App\Http\Controllers\fixedasset;

use App\Http\Controllers\Controller;
use App\Models\fixedasset\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::select('id', 'name', 'code', 'useful_life', 'fa_specific_id')
            ->with([
                'specific:id,code,name',
                'responsibles:id,name,lastname',
            ])
            ->where('is_active', true)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ], 200);
    }

    public function show(string $id)
    {
        $category = Category::select('id', 'name', 'code', 'useful_life', 'fa_specific_id')
            ->with([
                'specific:id,code,name',
                'responsibles:id,name,lastname',
            ])
            ->where('is_active', true)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $category,
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $this->validatedPayload($request);

        $category = new Category();
        $category->name = $data['name'];
        $category->code = $data['code'];
        $category->useful_life = $data['useful_life'] ?? null;
        $category->fa_specific_id = $data['fa_specific_id'];
        $category->is_active = true;
        $category->save();

        $this->syncResponsibles($category, $data['employee_ids'] ?? []);
        $category->load(['specific:id,code,name', 'responsibles:id,name,lastname']);

        return response()->json([
            'success' => true,
            'message' => 'Categoría creada correctamente.',
            'data' => $category,
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $data = $this->validatedPayload($request);

        $category = Category::findOrFail($id);
        $category->name = $data['name'];
        $category->code = $data['code'];
        $category->useful_life = $data['useful_life'] ?? null;
        $category->fa_specific_id = $data['fa_specific_id'];
        $category->save();

        $this->syncResponsibles($category, $data['employee_ids'] ?? []);
        $category->load(['specific:id,code,name', 'responsibles:id,name,lastname']);

        return response()->json([
            'success' => true,
            'message' => 'Categoría actualizada correctamente.',
            'data' => $category,
        ], 200);
    }

    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);
        $category->is_active = false;
        $category->save();

        return response()->json([
            'success' => true,
            'message' => 'Categoría deshabilitada correctamente.',
        ], 200);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedPayload(Request $request): array
    {
        return $request->validate([
            'name' => [
                'required',
                Rule::unique('fa_categories')->where('fa_specific_id', $request->fa_specific_id)
                    ->ignore($request->route('id')),
            ],
            'code' => [
                'required',
                Rule::unique('fa_categories')->where('fa_specific_id', $request->fa_specific_id)
                    ->ignore($request->route('id')),
            ],
            'useful_life' => 'nullable|integer|min:0',
            'fa_specific_id' => 'required|exists:fa_specifics,id',
            'employee_ids' => 'nullable|array',
            'employee_ids.*' => 'integer|exists:adm_employees,id',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique'   => 'Ya existe una categoría con este nombre en el específico seleccionado.',
            'code.required' => 'El código es obligatorio.',
            'code.unique'   => 'Ya existe una categoría con este código en el específico seleccionado.',
            'useful_life.integer' => 'La vida útil debe ser un número entero.',
            'useful_life.min' => 'La vida útil no puede ser negativa.',
            'fa_specific_id.required' => 'El específico es obligatorio.',
            'fa_specific_id.exists'   => 'El específico no existe.',
            'employee_ids.array' => 'Los responsables deben enviarse como una lista.',
            'employee_ids.*.integer' => 'Cada responsable debe ser un identificador válido.',
            'employee_ids.*.exists' => 'Uno o más responsables seleccionados no existen.',
        ]);
    }

    /**
     * @param  array<int, int|string>  $employeeIds
     */
    private function syncResponsibles(Category $category, array $employeeIds): void
    {
        $ids = collect($employeeIds)
            ->filter(fn ($id) => filled($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $category->responsibles()->sync($ids);
    }
}
