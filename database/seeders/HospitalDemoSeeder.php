<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Modules\Doctor\Models\Doctor;
use App\Modules\Patient\Models\Patient;
use App\Modules\Appointment\Models\Appointment;
use Illuminate\Support\Facades\Hash;

class HospitalDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo 2 bác sĩ
        $doctor1 = User::create([
            'name' => 'Dr. Nguyễn Văn A',
            'email' => 'doctor1@example.com',
            'phone' => '0901234567',
            'password' => Hash::make('password'),
            'role' => 'doctor',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        Doctor::create([
            'user_id' => $doctor1->id,
            'specialty' => 'Nội khoa',
            'qualification' => 'Tiến sĩ Y khoa',
            'experience_years' => 10,
            'consultation_fee' => 200000,
            'bio' => 'Bác sĩ chuyên khoa nội với 10 năm kinh nghiệm',
            'is_available' => true,
        ]);

        $doctor2 = User::create([
            'name' => 'Dr. Trần Thị B',
            'email' => 'doctor2@example.com',
            'phone' => '0907654321',
            'password' => Hash::make('password'),
            'role' => 'doctor',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        Doctor::create([
            'user_id' => $doctor2->id,
            'specialty' => 'Tim mạch',
            'qualification' => 'Thạc sĩ Y khoa',
            'experience_years' => 8,
            'consultation_fee' => 300000,
            'bio' => 'Bác sĩ chuyên khoa tim mạch',
            'is_available' => true,
        ]);

        // Tạo 3 bệnh nhân
        $patient1 = User::create([
            'name' => 'Lê Văn C',
            'email' => 'patient1@example.com',
            'phone' => '0912345678',
            'password' => Hash::make('password'),
            'role' => 'patient',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        Patient::create([
            'user_id' => $patient1->id,
            'date_of_birth' => '1990-01-15',
            'gender' => 'male',
            'address' => '123 Đường ABC, Quận 1, TP.HCM',
            'emergency_contact' => '0987654321',
        ]);

        $patient2 = User::create([
            'name' => 'Phạm Thị D',
            'email' => 'patient2@example.com',
            'phone' => '0923456789',
            'password' => Hash::make('password'),
            'role' => 'patient',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        Patient::create([
            'user_id' => $patient2->id,
            'date_of_birth' => '1985-06-20',
            'gender' => 'female',
            'address' => '456 Đường XYZ, Quận 2, TP.HCM',
            'emergency_contact' => '0976543210',
        ]);

        // Tạo vài appointment để test
        Appointment::create([
            'patient_id' => 1,
            'doctor_id' => 1,
            'appointment_date' => now()->addDays(1)->format('Y-m-d'),
            'appointment_time' => '09:00',
            'status' => 'pending',
            'reason' => 'Khám tổng quát',
        ]);

        Appointment::create([
            'patient_id' => 2,
            'doctor_id' => 2,
            'appointment_date' => now()->addDays(2)->format('Y-m-d'),
            'appointment_time' => '14:00',
            'status' => 'confirmed',
            'reason' => 'Khám tim mạch',
        ]);
    }
}
