<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Modules\Doctor\Models\Doctor;
use App\Modules\Patient\Models\Patient;
use App\Modules\Appointment\Models\Appointment;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tạo admin user cố định để test
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // ===== HOSPITAL DEMO DATA =====
        
        // 2. Tạo doctors với user accounts
        $doctorUsers = [
            [
                'name' => 'Dr. Nguyễn Văn A',
                'email' => 'doctor1@hospital.com',
                'password' => Hash::make('doctor123'),
                'phone' => '0901234567',
                'role' => 'doctor',
                'is_active' => true,
                'email_verified_at' => now(),
                'specialty' => 'Tim mạch',
                'qualification' => 'Thạc sĩ Y khoa',
                'experience_years' => 10,
                'consultation_fee' => 500000,
                'bio' => 'Chuyên khoa tim mạch với 10 năm kinh nghiệm',
                'is_available' => true
            ],
            [
                'name' => 'Dr. Trần Thị B',
                'email' => 'doctor2@hospital.com',
                'password' => Hash::make('doctor123'),
                'phone' => '0901234568',
                'role' => 'doctor',
                'is_active' => true,
                'email_verified_at' => now(),
                'specialty' => 'Nhi khoa',
                'qualification' => 'Tiến sĩ Y khoa',
                'experience_years' => 15,
                'consultation_fee' => 400000,
                'bio' => 'Chuyên gia nhi khoa hàng đầu',
                'is_available' => true
            ],
            [
                'name' => 'Dr. Lê Văn C',
                'email' => 'doctor3@hospital.com',
                'password' => Hash::make('doctor123'),
                'phone' => '0901234569',
                'role' => 'doctor',
                'is_active' => true,
                'email_verified_at' => now(),
                'specialty' => 'Da liễu',
                'qualification' => 'Bác sĩ CKI',
                'experience_years' => 8,
                'consultation_fee' => 300000,
                'bio' => 'Chuyên khoa da liễu và thẩm mỹ',
                'is_available' => true
            ]
        ];

        $doctors = [];
        foreach ($doctorUsers as $doctorData) {
            // Tạo user data (loại bỏ doctor fields)
            $userData = Arr::only($doctorData, ['name', 'email', 'password', 'phone', 'role', 'is_active', 'email_verified_at']);
            $user = User::create($userData);
            
            // Tạo doctor data
            $doctorFields = Arr::only($doctorData, ['specialty', 'qualification', 'experience_years', 'consultation_fee', 'bio', 'is_available']);
            $doctorFields['user_id'] = $user->id;
            $doctor = Doctor::create($doctorFields);
            
            $doctors[] = $doctor;
        }

        // 3. Tạo patients với user accounts
        $patientUsers = [
            [
                'name' => 'Hoàng Văn X',
                'email' => 'patient1@example.com',
                'password' => Hash::make('patient123'),
                'phone' => '0987654321',
                'role' => 'patient',
                'is_active' => true,
                'email_verified_at' => now(),
                'date_of_birth' => '1990-05-15',
                'gender' => 'male',
                'address' => '123 Nguyễn Văn Cừ, Quận 1, TP.HCM',
                'emergency_contact' => '0987654322'
            ],
            [
                'name' => 'Nguyễn Thị Y',
                'email' => 'patient2@example.com',
                'password' => Hash::make('patient123'),
                'phone' => '0987654323',
                'role' => 'patient',
                'is_active' => true,
                'email_verified_at' => now(),
                'date_of_birth' => '1985-12-20',
                'gender' => 'female',
                'address' => '456 Lê Lợi, Quận 3, TP.HCM',
                'emergency_contact' => '0987654324'
            ],
            [
                'name' => 'Trần Văn Z',
                'email' => 'patient3@example.com', 
                'password' => Hash::make('patient123'),
                'phone' => '0987654325',
                'role' => 'patient',
                'is_active' => true,
                'email_verified_at' => now(),
                'date_of_birth' => '1992-08-10',
                'gender' => 'male',
                'address' => '789 Võ Văn Tần, Quận 10, TP.HCM',
                'emergency_contact' => '0987654326'
            ]
        ];

        $patients = [];
        foreach ($patientUsers as $patientData) {
            // Tạo user data (loại bỏ patient fields)
            $userData = Arr::only($patientData, ['name', 'email', 'password', 'phone', 'role', 'is_active', 'email_verified_at']);
            $user = User::create($userData);
            
            // Tạo patient data
            $patientFields = Arr::only($patientData, ['date_of_birth', 'gender', 'address', 'emergency_contact']);
            $patientFields['user_id'] = $user->id;
            $patient = Patient::create($patientFields);
            
            $patients[] = $patient;
        }

        // 4. Tạo sample appointments
        $appointments = [
            [
                'patient_id' => $patients[0]->id,
                'doctor_id' => $doctors[0]->id,
                'appointment_date' => now()->addDays(1)->format('Y-m-d'),
                'appointment_time' => '09:00',
                'status' => 'pending',
                'reason' => 'Khám tim định kỳ'
            ],
            [
                'patient_id' => $patients[1]->id,
                'doctor_id' => $doctors[1]->id,
                'appointment_date' => now()->addDays(2)->format('Y-m-d'),
                'appointment_time' => '14:00',
                'status' => 'confirmed',
                'reason' => 'Khám nhi khoa cho con',
                'notes' => 'Đã xác nhận lịch hẹn'
            ],
            [
                'patient_id' => $patients[2]->id,
                'doctor_id' => $doctors[2]->id,
                'appointment_date' => now()->subDays(1)->format('Y-m-d'),
                'appointment_time' => '10:30',
                'status' => 'completed',
                'reason' => 'Điều trị mụn trứng cá',
                'notes' => 'Đã hoàn thành khám, kê đơn thuốc'
            ],
            [
                'patient_id' => $patients[0]->id,
                'doctor_id' => $doctors[1]->id,
                'appointment_date' => now()->addDays(5)->format('Y-m-d'),
                'appointment_time' => '15:30',
                'status' => 'pending',
                'reason' => 'Tư vấn sức khỏe tổng quát'
            ]
        ];

        foreach ($appointments as $appointmentData) {
            Appointment::create($appointmentData);
        }

        // 5. Tạo token xác thực email và reset password (giữ nguyên logic cũ)
        $unverifiedUser = User::factory()->unverified()->create([
            'name' => 'Unverified User',
            'email' => 'unverified@example.com',
            'password' => Hash::make('password123'),
        ]);

        DB::table('email_verification_tokens')->insert([
            'email' => 'unverified@example.com',
            'token' => 'test-verification-token',
            'created_at' => now(),
        ]);

        $resetPasswordUser = User::factory()->create([
            'name' => 'Reset Password User',
            'email' => 'reset@example.com',
            'password' => Hash::make('password123'),
        ]);

        DB::table('password_reset_tokens')->insert([
            'email' => 'reset@example.com',
            'token' => Hash::make('test-reset-token'),
            'created_at' => now(),
        ]);

        echo "\n=== HOSPITAL DEMO DATA CREATED ===\n";
        echo "Admin: admin@example.com / admin123\n";
        echo "Doctors: doctor1@hospital.com, doctor2@hospital.com, doctor3@hospital.com / doctor123\n";
        echo "Patients: patient1@example.com, patient2@example.com, patient3@example.com / patient123\n";
        echo "=================================\n";
    }
}


