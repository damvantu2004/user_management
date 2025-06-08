<?php

namespace App\Modules\Doctor\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Modules\Doctor\Services\DoctorService;

class DoctorController extends Controller
{
    protected $doctorService;

    public function __construct(DoctorService $doctorService)
    {
        $this->doctorService = $doctorService;
    }

    public function index()
    {
        $doctors = $this->doctorService->getAvailableDoctors();
        return $this->successResponse($doctors, 'Bác sĩ đã được kích hoạt');
    }

    public function show($id)
    {
        $doctor = $this->doctorService->getDoctorById($id);
        return $this->successResponse($doctor, 'Chi tiết bác sĩ');
    }

    public function available()
    {
        $doctors = $this->doctorService->getAvailableDoctors();
        return $this->successResponse($doctors, 'Bác sĩ đã được kích hoạt');
    }
}
