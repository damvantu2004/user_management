<?php

namespace App\Modules\Appointment\Services;

use App\Modules\Appointment\Models\Appointment;
use Illuminate\Support\Facades\Log;

class AppointmentService
{
    public function getAppointmentsByRole($user)
    {
        if ($user->role === 'patient') {
            $patient = $user->patient;
            return $patient ? $patient->appointments()->with(['doctor.user'])->get() : collect();
        }
        
        if ($user->role === 'doctor') {
            $doctor = $user->doctor;
            return $doctor ? $doctor->appointments()->with(['patient.user'])->get() : collect();
        }

        // Admin case
        return Appointment::with(['patient.user', 'doctor.user'])->get();
    }

    public function createAppointment(array $data)
    {
        Log::info('Creating appointment', $data);
        return Appointment::create($data);
    }

    public function updateAppointmentStatus($id, $status, $notes = null)
    {
        $appointment = Appointment::findOrFail($id);
        
        $updateData = ['status' => $status];
        if ($notes) {
            $updateData['notes'] = $notes;
        }
        
        $appointment->update($updateData);
        
        Log::info('Appointment status updated', ['id' => $id, 'status' => $status]);
        return $appointment->load(['patient.user', 'doctor.user']);
    }

    public function getAppointmentById($id)
    {
        return Appointment::with(['patient.user', 'doctor.user'])->findOrFail($id);
    }
}
