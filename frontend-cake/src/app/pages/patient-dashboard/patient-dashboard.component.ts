import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { AppointmentService } from '../../services/appointment.service';
import { PatientService } from '../../services/patient.service';
import { DoctorService } from '../../services/doctor.service';
import { AuthService } from '../../services/auth.service';
import { LoadingComponent } from '../../components/loading/loading.component';
import { SidebarComponent } from '../../components/sidebar/sidebar.component';

@Component({
  selector: 'app-patient-dashboard',
  standalone: true,
  imports: [CommonModule, RouterModule, LoadingComponent, SidebarComponent],
  templateUrl: './patient-dashboard.component.html',
  styleUrl: './patient-dashboard.component.css'
})
export class PatientDashboardComponent implements OnInit {
  currentUser: any = null;
  patientProfile: any = null;
  upcomingAppointments: any[] = [];
  recentAppointments: any[] = [];
  availableDoctors: any[] = [];
  loading: boolean = true;
  stats = {
    totalAppointments: 0,
    pendingAppointments: 0,
    completedAppointments: 0
  };

  constructor(
    private appointmentService: AppointmentService,
    private patientService: PatientService,
    private doctorService: DoctorService,
    private authService: AuthService
  ) {}

  ngOnInit(): void {
    this.currentUser = this.authService.getCurrentUser();
    this.loadDashboardData();
  }

  loadDashboardData(): void {
    this.loading = true;
    
    // Load patient profile
    this.patientService.getMyProfile().subscribe({
      next: (response) => {
        this.patientProfile = response.data;
      },
      error: (error) => {
        console.error('Error loading patient profile:', error);
      }
    });

    // Load appointments
    this.appointmentService.getMyAppointments().subscribe({
      next: (response) => {
        const appointments = response.data;
        this.processAppointments(appointments);
        this.loading = false;
      },
      error: (error) => {
        console.error('Error loading appointments:', error);
        this.loading = false;
      }
    });

    // Load available doctors
    this.doctorService.getAvailableDoctors().subscribe({
      next: (response) => {
        this.availableDoctors = response.data.slice(0, 6); // Top 6 doctors
      },
      error: (error) => {
        console.error('Error loading doctors:', error);
      }
    });
  }

  processAppointments(appointments: any[]): void {
    const now = new Date();
    
    this.upcomingAppointments = appointments
      .filter(apt => new Date(apt.appointment_date) >= now && apt.status !== 'cancelled')
      .sort((a, b) => new Date(a.appointment_date).getTime() - new Date(b.appointment_date).getTime())
      .slice(0, 3);

    this.recentAppointments = appointments
      .filter(apt => apt.status === 'completed')
      .sort((a, b) => new Date(b.appointment_date).getTime() - new Date(a.appointment_date).getTime())
      .slice(0, 3);

    // Calculate stats
    this.stats.totalAppointments = appointments.length;
    this.stats.pendingAppointments = appointments.filter(apt => apt.status === 'pending').length;
    this.stats.completedAppointments = appointments.filter(apt => apt.status === 'completed').length;
  }

  formatDate(date: string): string {
    return new Date(date).toLocaleDateString('vi-VN');
  }

  formatTime(time: string): string {
    return time.substring(0, 5); // HH:MM
  }

  getStatusBadgeClass(status: string): string {
    switch (status) {
      case 'pending': return 'badge bg-warning';
      case 'confirmed': return 'badge bg-info';
      case 'completed': return 'badge bg-success';
      case 'cancelled': return 'badge bg-danger';
      default: return 'badge bg-secondary';
    }
  }

  getStatusText(status: string): string {
    switch (status) {
      case 'pending': return 'Chờ xác nhận';
      case 'confirmed': return 'Đã xác nhận';
      case 'completed': return 'Hoàn thành';
      case 'cancelled': return 'Đã hủy';
      default: return status;
    }
  }
}
