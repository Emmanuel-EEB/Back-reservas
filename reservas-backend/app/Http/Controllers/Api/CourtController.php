<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Court;
use Illuminate\Http\Request;

class CourtController extends Controller
{
    public function index()
    {
        return response()->json(
            Court::where('is_active', true)->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|string',
            'price' => 'nullable|integer',
            'open_time' => 'nullable|date_format:H:i',
            'close_time' => 'nullable|date_format:H:i|after:open_time',
            'is_active' => 'nullable|boolean',
        ]);

        $court = Court::create($validated);

        return response()->json([
            'message' => 'Cancha creada correctamente',
            'data' => $court,
        ], 201);
    }

    public function show(string $id)
    {
        $court = Court::find($id);

        if (!$court) {
            return response()->json([
                'message' => 'Cancha no encontrada',
            ], 404);
        }

        return response()->json($court);
    }

    public function update(Request $request, string $id)
    {
        $court = Court::find($id);

        if (!$court) {
            return response()->json([
                'message' => 'Cancha no encontrada',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'type' => 'sometimes|string',
            'price' => 'sometimes|integer',
            'open_time' => 'sometimes|date_format:H:i',
            'close_time' => 'sometimes|date_format:H:i|after:open_time',
            'is_active' => 'sometimes|boolean',
        ]);

        $court->update($validated);

        return response()->json([
            'message' => 'Cancha actualizada correctamente',
            'data' => $court,
        ]);
    }

    public function destroy(string $id)
    {
        $court = Court::find($id);

        if (!$court) {
            return response()->json([
                'message' => 'Cancha no encontrada',
            ], 404);
        }

        $court->update([
            'is_active' => false,
        ]);

        return response()->json([
            'message' => 'Cancha desactivada correctamente',
        ]);
    }
}