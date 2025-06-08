import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { API_CONFIG } from '../constants/global.constants';
import { DoctorDTO } from '../dto/doctor.dto';
import { AuthService } from './auth.service';

@Injectable({
    providedIn: 'root'
})
export class DoctorService {
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

    getDoctors(): Observable<any> {
        return this.http.get<any>(this.url + API_CONFIG.ENDPOINTS.DOCTORS.BASE);
    }

    getDoctorById(id: number): Observable<any> {
        return this.http.get<any>(this.url + API_CONFIG.ENDPOINTS.DOCTORS.BASE + `/${id}`);
    }

    getAvailableDoctors(): Observable<any> {
        return this.http.get<any>(this.url + API_CONFIG.ENDPOINTS.DOCTORS.AVAILABLE);
    }

    getAvailableDoctorsAuth(): Observable<any> {
        return this.http.get<any>(
            `${this.url}${API_CONFIG.ENDPOINTS.DOCTORS.AVAILABLE}`,
            { headers: this.getHeaders() }
        );
    }

    getDoctorsBySpecialty(specialty: string): Observable<any> {
        return this.http.get<any>(
            `${this.url}${API_CONFIG.ENDPOINTS.DOCTORS.BASE}?specialty=${specialty}`,
            {
                headers: new HttpHeaders({
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                })
            }
        );
    }

    getMyProfile(): Observable<any> {
        return this.http.get<any>(
            `${this.url}/api/doctors/me`,
            { headers: this.getHeaders() }
        );
    }

    updateMyProfile(doctorData: DoctorDTO): Observable<any> {
        return this.http.put<any>(
            `${this.url}/api/doctors/me`,
            doctorData,
            { headers: this.getHeaders() }
        );
    }

    getSpecialties(): Observable<any> {
        return this.http.get<any>(
            `${this.url}/api/specialties`,
            {
                headers: new HttpHeaders({
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                })
            }
        );
    }

    // Alias for updateMyProfile (for component compatibility)
    updateProfile(doctorData: any): Observable<any> {
        return this.updateMyProfile(doctorData);
    }

    // Update schedule
    updateSchedule(scheduleData: any): Observable<any> {
        return this.http.put<any>(
            `${this.url}/api/doctors/schedule`,
            scheduleData,
            { headers: this.getHeaders() }
        );
    }

    // Change password
    changePassword(passwordData: any): Observable<any> {
        return this.http.post<any>(
            `${this.url}/api/doctors/change-password`,
            passwordData,
            { headers: this.getHeaders() }
        );
    }
}
