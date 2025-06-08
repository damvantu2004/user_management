import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { RouterModule, Router } from '@angular/router';
import { AppointmentService } from '../../services/appointment.service';
import { DoctorService } from '../../services/doctor.service';
import { LoadingComponent } from '../../components/loading/loading.component';

@Component({
  selector: 'app-book-appointment',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, RouterModule, LoadingComponent],
  templateUrl: './book-appointment.component.html',
  styleUrl: './book-appointment.component.css'
})
export class BookAppointmentComponent implements OnInit {
  appointmentForm: FormGroup;
  doctors: any[] = [];
  selectedDoctor: any = null;
  availableTimeSlots: string[] = [];
  loading: boolean = false;
  submitting: boolean = false;
  minDate: string = '';
  maxDate: string = '';
  message: string = '';
  messageType: 'success' | 'error' = 'success';

  timeSlots = [
    '08:00', '08:30', '09:00', '09:30', '10:00', '10:30',
    '11:00', '11:30', '13:00', '13:30', '14:00', '14:30',
    '15:00', '15:30', '16:00', '16:30', '17:00', '17:30'
  ];

  constructor(
    private fb: FormBuilder,
    private appointmentService: AppointmentService,
    private doctorService: DoctorService,
    private router: Router
  ) {
    this.appointmentForm = this.fb.group({
      doctor_id: ['', Validators.required],
      appointment_date: ['', Validators.required],
      appointment_time: ['', Validators.required],
      reason: ['', [Validators.required, Validators.minLength(10)]]
    });
  }

  ngOnInit(): void {
    this.setDateLimits();
    this.loadDoctors();
    this.setupFormSubscriptions();
  }

  setDateLimits(): void {
    const today = new Date();
    const maxDate = new Date();
    maxDate.setMonth(today.getMonth() + 3); // 3 months from now

    this.minDate = this.formatDateForInput(today);
    this.maxDate = this.formatDateForInput(maxDate);
  }

  formatDateForInput(date: Date): string {
    return date.toISOString().split('T')[0];
  }

  loadDoctors(): void {
    this.loading = true;
    this.doctorService.getAvailableDoctors().subscribe({
      next: (response) => {
        this.doctors = response.data;
        this.loading = false;
      },
      error: (error) => {
        console.error('Error loading doctors:', error);
        this.showMessage('Không thể tải danh sách bác sĩ', 'error');
        this.loading = false;
      }
    });
  }

  setupFormSubscriptions(): void {
    // When doctor changes, reset time slot
    this.appointmentForm.get('doctor_id')?.valueChanges.subscribe(doctorId => {
      if (doctorId) {
        this.selectedDoctor = this.doctors.find(d => d.id == doctorId);
        this.appointmentForm.patchValue({ appointment_time: '' });
        this.checkAvailableTimeSlots();
      }
    });

    // When date changes, check available time slots
    this.appointmentForm.get('appointment_date')?.valueChanges.subscribe(() => {
      this.appointmentForm.patchValue({ appointment_time: '' });
      this.checkAvailableTimeSlots();
    });
  }

  checkAvailableTimeSlots(): void {
    const doctorId = this.appointmentForm.get('doctor_id')?.value;
    const date = this.appointmentForm.get('appointment_date')?.value;

    if (doctorId && date) {
      // For now, use all time slots as available
      // In real app, call API to check doctor's availability
      this.availableTimeSlots = [...this.timeSlots];
    }
  }

  isTimeSlotAvailable(time: string): boolean {
    return this.availableTimeSlots.includes(time);
  }

  onSubmit(): void {
    if (this.appointmentForm.valid) {
      this.submitting = true;
      const formData = this.appointmentForm.value;

      this.appointmentService.bookAppointment(formData).subscribe({
        next: (response: any) => {
          this.showMessage('Đặt lịch khám thành công!', 'success');
          setTimeout(() => {
            this.router.navigate(['/patient-dashboard']);
          }, 2000);
        },
        error: (error: any) => {
          console.error('Error booking appointment:', error);
          const message = error.error?.message || 'Có lỗi xảy ra khi đặt lịch';
          this.showMessage(message, 'error');
        },
        complete: () => {
          this.submitting = false;
        }
      });
    } else {
      this.markFormGroupTouched();
    }
  }

  markFormGroupTouched(): void {
    Object.keys(this.appointmentForm.controls).forEach(key => {
      const control = this.appointmentForm.get(key);
      control?.markAsTouched();
    });
  }

  showMessage(message: string, type: 'success' | 'error'): void {
    this.message = message;
    this.messageType = type;
    setTimeout(() => {
      this.message = '';
    }, 5000);
  }

  getFieldError(fieldName: string): string {
    const field = this.appointmentForm.get(fieldName);
    if (field?.touched && field?.errors) {
      if (field.errors['required']) {
        return `${this.getFieldLabel(fieldName)} là bắt buộc`;
      }
      if (field.errors['minlength']) {
        return `${this.getFieldLabel(fieldName)} phải có ít nhất ${field.errors['minlength'].requiredLength} ký tự`;
      }
    }
    return '';
  }

  getFieldLabel(fieldName: string): string {
    const labels: { [key: string]: string } = {
      'doctor_id': 'Bác sĩ',
      'appointment_date': 'Ngày khám',
      'appointment_time': 'Giờ khám',
      'reason': 'Lý do khám'
    };
    return labels[fieldName] || fieldName;
  }

  isFieldInvalid(fieldName: string): boolean {
    const field = this.appointmentForm.get(fieldName);
    return !!(field?.invalid && field?.touched);
  }
}
