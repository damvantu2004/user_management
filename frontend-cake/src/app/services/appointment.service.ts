import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { API_CONFIG } from '../constants/global.constants';
import { AppointmentDTO } from '../dto/appointment.dto';
import { AuthService } from './auth.service';

@Injectable({
  providedIn: 'root'
})
export class AppointmentService {
  private url = `${API_CONFIG.BASE_URL}`;

  constructor(
    private http: HttpClient,
    private authService: AuthService
  ) {}

  private getHeaders(): HttpHeaders {
    const token = this.authService.getToken();
    return new HttpHeaders({
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    });
  }

  // Lấy danh sách lịch hẹn (filtered theo role)
  getMyAppointments(): Observable<any> {
    return this.http.get<any>(
      `${this.url}${API_CONFIG.ENDPOINTS.APPOINTMENTS.BASE}`,
      { headers: this.getHeaders() }
    );
  }

  // Lấy chi tiết một lịch hẹn
  getAppointmentById(id: number): Observable<any> {
    return this.http.get<any>(
      `${this.url}${API_CONFIG.ENDPOINTS.APPOINTMENTS.BASE}/${id}`,
      { headers: this.getHeaders() }
    );
  }

  // Patient đặt lịch hẹn mới
  createAppointment(appointmentData: AppointmentDTO): Observable<any> {
    return this.http.post<any>(
      `${this.url}${API_CONFIG.ENDPOINTS.APPOINTMENTS.BASE}`,
      {
        doctor_id: appointmentData.doctor_id,
        appointment_date: appointmentData.appointment_date,
        appointment_time: appointmentData.appointment_time,
        reason: appointmentData.reason
      },
      { headers: this.getHeaders() }
    );
  }

  // Doctor cập nhật lịch hẹn (confirm, complete, cancel)
  updateAppointmentStatus(id: number, status: string, notes?: string): Observable<any> {
    return this.http.put<any>(
      `${this.url}${API_CONFIG.ENDPOINTS.APPOINTMENTS.BASE}/${id}`,
      {
        status: status,
        notes: notes
      },
      { headers: this.getHeaders() }
    );
  }

  // Patient hủy lịch hẹn
  cancelAppointment(id: number): Observable<any> {
    return this.updateAppointmentStatus(id, 'cancelled', 'Bệnh nhân hủy lịch hẹn');
  }

  // Doctor xác nhận lịch hẹn
  confirmAppointment(id: number, notes?: string): Observable<any> {
    return this.updateAppointmentStatus(id, 'confirmed', notes);
  }

  // Doctor hoàn thành lịch hẹn
  completeAppointment(id: number, notes?: string): Observable<any> {
    return this.updateAppointmentStatus(id, 'completed', notes);
  }

  // Lấy lịch hẹn theo trạng thái
  getAppointmentsByStatus(status: string): Observable<any> {
    return this.http.get<any>(
      `${this.url}${API_CONFIG.ENDPOINTS.APPOINTMENTS.BASE}?status=${status}`,
      { headers: this.getHeaders() }
    );
  }

  // Lấy lịch hẹn theo ngày
  getAppointmentsByDate(date: string): Observable<any> {
    return this.http.get<any>(
      `${this.url}${API_CONFIG.ENDPOINTS.APPOINTMENTS.BASE}?date=${date}`,
      { headers: this.getHeaders() }
    );
  }

  // Alias for createAppointment (for component compatibility)
  bookAppointment(appointmentData: any): Observable<any> {
    return this.createAppointment(appointmentData);
  }

  // Doctor get appointments  
  getDoctorAppointments(): Observable<any> {
    return this.http.get<any>(
      `${this.url}/api/doctors/appointments`,
      { headers: this.getHeaders() }
    );
  }
}
