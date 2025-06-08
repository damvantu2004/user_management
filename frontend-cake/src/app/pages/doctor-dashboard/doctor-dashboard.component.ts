import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { AppointmentService } from '../../services/appointment.service';
import { DoctorService } from '../../services/doctor.service';
import { PatientService } from '../../services/patient.service';
import { AuthService } from '../../services/auth.service';
import { LoadingComponent } from '../../components/loading/loading.component';
import { SidebarComponent } from '../../components/sidebar/sidebar.component';

@Component({
  selector: 'app-doctor-dashboard',
  standalone: true,
  imports: [CommonModule, RouterModule, LoadingComponent, SidebarComponent],
  templateUrl: './doctor-dashboard.component.html',
  styleUrl: './doctor-dashboard.component.css'
})
export class DoctorDashboardComponent implements OnInit {
  currentUser: any = null;
  doctorProfile: any = null;
  todayAppointments: any[] = [];
  pendingAppointments: any[] = [];
  recentPatients: any[] = [];
  loading: boolean = true;
  today: string = '';
  
  stats = {
    totalPatients: 0,
    todayAppointments: 0,
    pendingAppointments: 0,
    completedAppointments: 0
  };

  constructor(
    private appointmentService: AppointmentService,
    private doctorService: DoctorService,
    private patientService: PatientService,
    private authService: AuthService
  ) {}

  ngOnInit(): void {
    this.currentUser = this.authService.getCurrentUser();
    this.today = new Date().toISOString().split('T')[0];
    this.loadDashboardData();
  }

  loadDashboardData(): void {
    this.loading = true;
    
    // Load doctor profile
    this.doctorService.getMyProfile().subscribe({
      next: (response) => {
        this.doctorProfile = response.data;
      },
      error: (error) => {
        console.error('Error loading doctor profile:', error);
      }
    });

    // Load appointments
    this.appointmentService.getDoctorAppointments().subscribe({
      next: (response: any) => {
        const appointments = response.data;
        this.processAppointments(appointments);
        this.loading = false;
      },
      error: (error: any) => {
        console.error('Error loading appointments:', error);
        this.loading = false;
      }
    });

    // Load recent patients
    this.patientService.getMyPatients().subscribe({
      next: (response: any) => {
        this.recentPatients = response.data.slice(0, 5);
        this.stats.totalPatients = response.data.length;
      },
      error: (error: any) => {
        console.error('Error loading patients:', error);
      }
    });
  }

  processAppointments(appointments: any[]): void {
    const today = new Date().toISOString().split('T')[0];
    
    // Today's appointments
    this.todayAppointments = appointments
      .filter(apt => apt.appointment_date === today)
      .sort((a, b) => a.appointment_time.localeCompare(b.appointment_time));

    // Pending appointments
    this.pendingAppointments = appointments
      .filter(apt => apt.status === 'pending')
      .sort((a, b) => new Date(a.appointment_date).getTime() - new Date(b.appointment_date).getTime())
      .slice(0, 5);

    // Calculate stats
    this.stats.todayAppointments = this.todayAppointments.length;
    this.stats.pendingAppointments = appointments.filter(apt => apt.status === 'pending').length;
    this.stats.completedAppointments = appointments.filter(apt => apt.status === 'completed').length;
  }

  confirmAppointment(appointmentId: number): void {
    this.appointmentService.confirmAppointment(appointmentId).subscribe({
      next: (response) => {
        // Refresh data after confirming
        this.loadDashboardData();
        // Show success message (you might want to add a toast service)
      },
      error: (error) => {
        console.error('Error confirming appointment:', error);
      }
    });
  }

  completeAppointment(appointmentId: number): void {
    this.appointmentService.completeAppointment(appointmentId).subscribe({
      next: (response) => {
        // Refresh data after completing
        this.loadDashboardData();
      },
      error: (error) => {
        console.error('Error completing appointment:', error);
      }
    });
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

  getTimeStatus(appointmentTime: string): 'upcoming' | 'current' | 'past' {
    const now = new Date();
    const currentTime = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
    const appointmentTimeOnly = appointmentTime.substring(0, 5);
    
    if (appointmentTimeOnly > currentTime) return 'upcoming';
    if (appointmentTimeOnly === currentTime) return 'current';
    return 'past';
  }

  getTimeStatusClass(timeStatus: string): string {
    switch (timeStatus) {
      case 'current': return 'border-warning bg-warning bg-opacity-10';
      case 'past': return 'border-secondary bg-light';
      default: return 'border-primary';
    }
  }
}
