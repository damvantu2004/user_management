import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { API_CONFIG } from '../constants/global.constants';
import { PatientDTO } from '../dto/patient.dto';
import { AuthService } from './auth.service';

@Injectable({
  providedIn: 'root'
})
export class PatientService {
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

  // Patient xem profile của mình
  getMyProfile(): Observable<any> {
    return this.http.get<any>(
      `${this.url}${API_CONFIG.ENDPOINTS.PATIENTS.ME}`,
      { headers: this.getHeaders() }
    );
  }

  // Patient cập nhật profile của mình
  updateMyProfile(patientData: PatientDTO): Observable<any> {
    return this.http.put<any>(
      `${this.url}${API_CONFIG.ENDPOINTS.PATIENTS.ME}`,
      patientData,
      { headers: this.getHeaders() }
    );
  }

  // Doctor xem danh sách tất cả bệnh nhân
  getAllPatients(): Observable<any> {
    return this.http.get<any>(
      `${this.url}${API_CONFIG.ENDPOINTS.PATIENTS.BASE}`,
      { headers: this.getHeaders() }
    );
  }

  // Doctor xem chi tiết một bệnh nhân
  getPatientById(id: number): Observable<any> {
    return this.http.get<any>(
      `${this.url}${API_CONFIG.ENDPOINTS.PATIENTS.BASE}/${id}`,
      { headers: this.getHeaders() }
    );
  }

  // Tìm kiếm bệnh nhân (cho doctor/admin)
  searchPatients(searchTerm: string): Observable<any> {
    return this.http.get<any>(
      `${this.url}${API_CONFIG.ENDPOINTS.PATIENTS.BASE}?search=${searchTerm}`,
      { headers: this.getHeaders() }
    );
  }

  // Alias for updateMyProfile (for component compatibility)
  updateProfile(patientData: any): Observable<any> {
    return this.updateMyProfile(patientData);
  }

  // Change password
  changePassword(passwordData: any): Observable<any> {
    return this.http.post<any>(
      `${this.url}/api/patients/change-password`,
      passwordData,
      { headers: this.getHeaders() }
    );
  }

  // Doctor xem danh sách bệnh nhân của mình
  getMyPatients(): Observable<any> {
    return this.http.get<any>(
      `${this.url}/api/doctors/my-patients`,
      { headers: this.getHeaders() }
    );
  }
}
