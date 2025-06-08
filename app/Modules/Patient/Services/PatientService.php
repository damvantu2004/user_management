<?php

namespace App\Modules\Patient\Services;

use App\Modules\Patient\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class PatientService
{
    public function getAllPatients()
    {
        return Patient::with('user')->get();
    }

    public function getPatientByUserId($userId)
    {
        return Patient::where('user_id', $userId)->with('user')->first();
    }

    public function getPatientById($id)
    {
        return Patient::with('user')->findOrFail($id);
    }

    public function updatePatientProfile($userId, array $data)
    {
        $patient = Patient::where('user_id', $userId)->first();
        
        if (!$patient) {
            $patient = Patient::create(array_merge($data, ['user_id' => $userId]));
        } else {
            $patient->update($data);
        }

        Log::info('Patient profile updated', ['user_id' => $userId]);
        return $patient->load('user');
    }
}
