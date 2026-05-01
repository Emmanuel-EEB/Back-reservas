<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $query = Reservation::with(['user', 'court'])
            ->latest();

        if ($request->user()->role !== 'admin') {
            $query->where('user_id', $request->user()->id);
        }

        return response()->json($query->get());
    }

    public function updateStatus(Request $request, Reservation $reservation)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'No tienes permiso para cambiar el estado de reservas.'
            ], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled',
        ]);

        $reservation->update([
            'status' => $validated['status'],
        ]);

        return response()->json([
            'message' => 'Estado actualizado correctamente',
            'data' => $reservation->load(['user', 'court']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'court_id' => 'required|exists:courts,id',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'status' => 'nullable|string',
        ]);

        $exists = Reservation::where('court_id', $validated['court_id'])
            ->where('date', $validated['date'])
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($validated) {
                $query->where('start_time', '<', $validated['end_time'])
                    ->where('end_time', '>', $validated['start_time']);
            })
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Este horario ya está reservado para esta cancha.'
            ], 422);
        }

        $reservation = Reservation::create([
            'user_id' => $request->user()->id,
            'court_id' => $validated['court_id'],
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'status' => $validated['status'] ?? 'pending',
        ]);

        return response()->json([
            'message' => 'Reserva creada correctamente',
            'data' => $reservation->load(['user', 'court'])
        ], 201);
    }

    public function show(string $id)
    {
        $reservation = Reservation::with(['user', 'court'])->find($id);

        if (!$reservation) {
            return response()->json([
                'message' => 'Reserva no encontrada'
            ], 404);
        }

        return response()->json($reservation);
    }

    public function update(Request $request, string $id)
    {
        $reservation = Reservation::find($id);

        if (!$reservation) {
            return response()->json([
                'message' => 'Reserva no encontrada'
            ], 404);
        }

        $validated = $request->validate([
            'court_id' => 'sometimes|exists:courts,id',
            'date' => 'sometimes|date',
            'start_time' => 'sometimes|date_format:H:i',
            'end_time' => 'sometimes|date_format:H:i|after:start_time',
            'status' => 'sometimes|string',
        ]);

        $courtId = $validated['court_id'] ?? $reservation->court_id;
        $date = $validated['date'] ?? $reservation->date;
        $startTime = $validated['start_time'] ?? $reservation->start_time;
        $endTime = $validated['end_time'] ?? $reservation->end_time;

        $exists = Reservation::where('court_id', $courtId)
            ->where('date', $date)
            ->where('id', '!=', $reservation->id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime);
            })
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Este horario ya está reservado para esta cancha.'
            ], 422);
        }

        $reservation->update($validated);

        return response()->json([
            'message' => 'Reserva actualizada correctamente',
            'data' => $reservation->load(['user', 'court'])
        ]);
    }

    public function destroy(Request $request, string $id)
    {
        $reservation = Reservation::find($id);

        if (!$reservation) {
            return response()->json([
                'message' => 'Reserva no encontrada'
            ], 404);
        }

        if (
            $request->user()->role !== 'admin' &&
            $reservation->user_id !== $request->user()->id
        ) {
            return response()->json([
                'message' => 'No tienes permiso para cancelar esta reserva'
            ], 403);
        }

        $reservation->update([
            'status' => 'cancelled'
        ]);

        return response()->json([
            'message' => 'Reserva cancelada correctamente',
            'data' => $reservation->load(['user', 'court'])
        ]);
    }
}