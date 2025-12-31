<?php

namespace App\Http\Controllers\warehouse;

use App\Http\Controllers\Controller;
use App\Models\warehouse\SupplyReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SupplyReturnController extends Controller
{
    public function index()
    {
        $supplyReturns = SupplyReturn::with([
            'returnedBy:id,name,lastname',
            'office:id,name',
            'immediateSupervisor:id,name,lastname',
            'receivedBy:id,name,lastname'
        ])->get();

        return response()->json([
            'success' => true,
            'data'    => $supplyReturns,
        ]);
    }

    public function store(Request $request)
    {
        $rules = [
            'return_date'            => 'required|date',
            'returned_by_id'         => 'required|exists:users,id',
            'wh_office_id'           => 'required|exists:wh_offices,id',
            'immediate_supervisor_id' => 'required|exists:users,id',
            'received_by_id'         => 'required|exists:users,id',
            'phone_extension'        => 'nullable|string|max:10',
            'general_observations'   => 'nullable|string|max:1000',
        ];

        $messages = [
            'return_date.required'             => 'La fecha de devolución es obligatoria.',
            'return_date.date'                 => 'La fecha de devolución debe tener un formato de fecha válido.',

            'returned_by_id.required'          => 'El usuario que devuelve el suministro es obligatorio.',
            'wh_office_id.required'            => 'La oficina a la que pertenece la devolución es obligatoria.',
            'immediate_supervisor_id.required' => 'El supervisor inmediato es obligatorio.',
            'received_by_id.required'          => 'El usuario que recibe la devolución es obligatorio.',

            'returned_by_id.exists'            => 'El usuario que devuelve no existe en el sistema.',
            'wh_office_id.exists'              => 'La oficina seleccionada no es válida.',
            'immediate_supervisor_id.exists'   => 'El supervisor inmediato seleccionado no existe.',
            'received_by_id.exists'            => 'El usuario receptor seleccionado no existe.',

            'phone_extension.string'           => 'La extensión telefónica debe ser texto.',
            'phone_extension.max'              => 'La extensión telefónica no debe exceder los 10 caracteres.',
            'general_observations.string'      => 'Las observaciones generales deben ser texto.',
            'general_observations.max'         => 'Las observaciones generales no deben exceder los 1000 caracteres.',
        ];

        $request->validate($rules, $messages);

        try {
            $supplyReturn = new SupplyReturn();

            $supplyReturn->return_date = $request->input('return_date');
            $supplyReturn->returned_by_id = $request->input('returned_by_id');
            $supplyReturn->wh_office_id = $request->input('wh_office_id');
            $supplyReturn->immediate_supervisor_id = $request->input('immediate_supervisor_id');
            $supplyReturn->received_by_id = $request->input('received_by_id');

            $supplyReturn->phone_extension = $request->input('phone_extension');
            $supplyReturn->general_observations = $request->input('general_observations');

            $supplyReturn->save();


            return response()->json([
                'success' => true,
                'message' => 'Devolución de suministros registrada correctamente.',
                'data'    => $supplyReturn,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la devolución de suministros.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id)
    {
        $supplyReturn = SupplyReturn::with([
            'returnedBy:id,name,lastname',
            'office:id,name',
            'immediateSupervisor:id,name,lastname',
            'receivedBy:id,name,lastname'
        ])->find($id);

        if (!$supplyReturn) {
            return response()->json([
                'success' => false,
                'message' => 'Devolución de suministros no encontrada.',
                'data'    => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $supplyReturn,
        ]);
    }




    public function update(Request $request, string $id)
    {
        $supplyReturn = SupplyReturn::find($id);

        if (!$supplyReturn) {
            return response()->json([
                'success' => false,
                'message' => 'Devolución de suministros no encontrada para actualizar.',
                'data'    => null,
            ], 404);
        }

        $rules = [
            'return_date'            => 'required|date',
            'returned_by_id'         => 'required|exists:users,id',
            'wh_office_id'           => 'required|exists:wh_offices,id',
            'immediate_supervisor_id' => 'required|exists:users,id',
            'received_by_id'         => 'required|exists:users,id',
            'phone_extension'        => 'nullable|string|max:10',
            'general_observations'   => 'nullable|string|max:1000',
        ];

        $messages = [
            'return_date.required'             => 'La fecha de devolución es obligatoria.',
            'return_date.date'                 => 'La fecha de devolución debe tener un formato de fecha válido.',

            'returned_by_id.required'          => 'El usuario que devuelve el suministro es obligatorio.',
            'wh_office_id.required'            => 'La oficina a la que pertenece la devolución es obligatoria.',
            'immediate_supervisor_id.required' => 'El supervisor inmediato es obligatorio.',
            'received_by_id.required'          => 'El usuario que recibe la devolución es obligatorio.',

            'returned_by_id.exists'            => 'El usuario que devuelve no existe en el sistema.',
            'wh_office_id.exists'              => 'La oficina seleccionada no es válida.',
            'immediate_supervisor_id.exists'   => 'El supervisor inmediato seleccionado no existe.',
            'received_by_id.exists'            => 'El usuario receptor seleccionado no existe.',

            'phone_extension.string'           => 'La extensión telefónica debe ser texto.',
            'phone_extension.max'              => 'La extensión telefónica no debe exceder los 10 caracteres.',
            'general_observations.string'      => 'Las observaciones generales deben ser texto.',
            'general_observations.max'         => 'Las observaciones generales no deben exceder los 1000 caracteres.',
        ];

        $request->validate($rules, $messages);

        try {
            $supplyReturn->return_date = $request->input('return_date');
            $supplyReturn->returned_by_id = $request->input('returned_by_id');
            $supplyReturn->wh_office_id = $request->input('wh_office_id');
            $supplyReturn->immediate_supervisor_id = $request->input('immediate_supervisor_id');
            $supplyReturn->received_by_id = $request->input('received_by_id');

            $supplyReturn->phone_extension = $request->input('phone_extension');
            $supplyReturn->general_observations = $request->input('general_observations');

            $supplyReturn->save();

            return response()->json([
                'success' => true,
                'message' => 'Devolución de suministros actualizada correctamente.',
                'data'    => $supplyReturn,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la devolución de suministros.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        $supplyReturn = SupplyReturn::find($id);

        if (!$supplyReturn) {
            return response()->json([
                'success' => false,
                'message' => 'Devolución de suministros no encontrada para eliminar.',
                'data'    => null,
            ], 404);
        }

        try {
            $supplyReturn->delete();

            return response()->json([
                'success' => true,
                'message' => 'Devolución de suministros eliminada correctamente.',
                'data'    => $supplyReturn,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la devolución de suministros.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
