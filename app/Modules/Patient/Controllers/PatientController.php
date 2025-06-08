<?php

namespace App\Modules\Patient\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Patient\Services\PatientService;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    protected $patientService;

    public function __construct(PatientService $patientService)
    {
        $this->patientService = $patientService;
    }

    public function profile(Request $request)
    {
        $patient = $this->patientService->getPatientByUserId($request->user()->id);
        return $this->successResponse($patient, 'Patient profile retrieved');
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string',
            'emergency_contact' => 'nullable|string'
        ]);

        $patient = $this->patientService->updatePatientProfile($request->user()->id, $validated);
        return $this->successResponse($patient, 'Profile updated successfully');
    }

    public function index()
    {
        $patients = $this->patientService->getAllPatients();
        return $this->successResponse($patients, 'Patients list retrieved');
    }

    public function show($id)
    {
        $patient = $this->patientService->getPatientById($id);
        return $this->successResponse($patient, 'Patient details retrieved');
    }
}
