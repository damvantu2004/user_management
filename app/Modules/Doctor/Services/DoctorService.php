<?php

namespace App\Modules\Doctor\Services;

use App\Modules\Doctor\Models\Doctor;
use Illuminate\Support\Facades\Log;

class DoctorService
{
    public function getAllDoctors()
    {
        return Doctor::with('user')->where('is_available', true)->get();
    }

    public function getDoctorById($id)
    {
        return Doctor::with('user')->findOrFail($id);
    }

    public function getDoctorByUserId($userId)
    {
        return Doctor::where('user_id', $userId)->with('user')->first();
    }

    public function getAvailableDoctors()
    {
        return Doctor::with('user')
            ->where('is_available', true)
            ->get();
    }
}
