<?php

namespace App\Http\Controllers\warehouse;

use App\Http\Controllers\Controller;
use App\Models\warehouse\Office;
use App\Models\warehouse\SupplyRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SupplyRequestController extends Controller
{
    public function index()
    {
        $supplyRequests = SupplyRequest::with('status')->with('requester')->with('immediateBoss')->get();

        return response()->json([
            'success' => true,
            'data'    => $supplyRequests,
        ]);
    }

    public function getBoss(String $officeId)
    {
        $office = Office::findOrFail($officeId);

        $bosses = $office->users()
            ->whereHas('roles', function ($query) {
                $query->where('id', 4);
            })
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $bosses,
        ]);
    }


    public function store(Request $request)
    {
        $rules = [
            'date'              => 'required|date',
            'office_id'      => 'required|exists:wh_offices,id',
            'immediate_boss_id' => 'required|exists:users,id',
            'requester_id'      => 'required|exists:users,id',
            'observation'       => 'nullable|string|max:1000',
        ];
        $messages = [
            // Mensajes directos para campos requeridos
            'date.required'              => 'La fecha de solicitud es obligatoria.',
            'office_id.required'      => 'La oficina solicitante es obligatoria.',
            'immediate_boss_id.required' => 'El jefe inmediato es obligatorio.',
            'requester_id.required'      => 'El ID del solicitante es obligatorio.',
            'observation.required'       => 'La observación es obligatoria.', // Aunque 'observation' es nullable, la regla 'required' aquí aplicaría si la hubieras puesto. La mantengo por si la regla de negocio cambia.

            // Mensajes directos para reglas de formato y existencia
            'date.date'                  => 'La fecha de solicitud debe tener un formato de fecha válido.',

            'office_id.exists'        => 'La oficina seleccionada no es válida.',
            'immediate_boss_id.exists'   => 'El jefe inmediato seleccionado no existe.',
            'requester_id.exists'        => 'El usuario solicitante no existe.',
            'observation.string'         => 'La observación debe ser texto.',
        ];

        $request->validate($rules, $messages);

        $requesterId = $request->input('requester_id');

        try {
            $supplyRequest = new SupplyRequest();

            $supplyRequest->date = $request->input('date');
            $supplyRequest->observation = $request->input('observation');
            $supplyRequest->requester_id = $requesterId;
            $supplyRequest->immediate_boss_id = $request->input('immediate_boss_id');
            $supplyRequest->office_id = $request->input('office_id');
            $supplyRequest->status_id = 1;

            $supplyRequest->save();

            $supplyRequest->load('status', 'requester');

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de insumos creada correctamente.',
                'data'    => $supplyRequest,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la solicitud.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function show(string $id)
    {
        $supplyRequest = SupplyRequest::with('status')->with('requester')->with('office')->with('immediateBoss')->find($id);

        return response()->json([
            'success' => true,
            'data'    => $supplyRequest,
        ]);
    }


    public function approve(string $id)
    {
        try {
            $supplyRequest = SupplyRequest::findOrFail($id);

            if ($supplyRequest->status_id !== 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden aprobar solicitudes que están en estado Pendiente.',
                ], 403); // HTTP_FORBIDDEN
            }

            $supplyRequest->status_id = 2;
            $supplyRequest->save();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de insumos aprobada correctamente. Estado: Aprobado.',
                'data' => $supplyRequest,
            ], 200); // HTTP_OK

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'La Solicitud de Insumos (ID: ' . $id . ') no fue encontrada.',
            ], 404); // HTTP_NOT_FOUND
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al aprobar la solicitud: ' . $e->getMessage(),
            ], 500); // HTTP_INTERNAL_SERVER_ERROR
        }
    }


    public function finalize(string $id)
    {
        try {
            $supplyRequest = SupplyRequest::findOrFail($id);

            if ($supplyRequest->status_id !== 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden aprobar solicitudes que están en estado Pendiente.',
                ], 403); // HTTP_FORBIDDEN
            }

            $supplyRequest->status_id = 3;
            $supplyRequest->save();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de insumos aprobada correctamente. Estado: Aprobado.',
                'data' => $supplyRequest,
            ], 200); // HTTP_OK

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'La Solicitud de Insumos (ID: ' . $id . ') no fue encontrada.',
            ], 404); // HTTP_NOT_FOUND
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al aprobar la solicitud: ' . $e->getMessage(),
            ], 500); // HTTP_INTERNAL_SERVER_ERROR
        }
    }


    public function update(Request $request, string $id)
    {
        //
    }


    public function destroy(string $id)
    {
        //
    }
}
