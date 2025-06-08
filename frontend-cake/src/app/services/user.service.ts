import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';
import { API_CONFIG } from '../constants/global.constants';
import { UserDTO } from '../dto/user.dto';
import { Observable, map } from 'rxjs';
import { loginDto } from '../dto/login.dto';
import { AuthService } from './auth.service';
import { PaginationResponse } from '../dto/pagination.dto';
import { ResponseDTO } from '../dto/response.dto';
import { searchDTO } from '../dto/search.dto';


@Injectable({
  providedIn: 'root'
})
export class UserService {
  private url = `${API_CONFIG.BASE_URL}`
    
  constructor(private http: HttpClient, private authService: AuthService) { }
  users: UserDTO[] = [];

  get(page?: number, searchParam?: searchDTO): Observable<PaginationResponse<UserDTO>>{
    let result: PaginationResponse<UserDTO> = {
      data: [],
      current_page: 1,
      last_page: 0
    }
    let params = new HttpParams();
    params = params.append('page',page || 1);
    if(searchParam){
      params = params.append('search',searchParam.name);
      params = params.append('role', searchParam.role);
      params = params.append('is_active', searchParam.is_active);
    }

    // pipe đón response và trả về kết quả cho next
    return this.http.get<any>(this.url + `${API_CONFIG.ENDPOINTS.USERS.BASE}`,{params: params}).pipe(
      map(response => {
        this.users = response.data;
        result.data = response.data;
        result.last_page = this.calcTotalPages(response.meta.total);
        return result;
      })
    )
  }
  post(user: UserDTO): Observable<ResponseDTO<UserDTO>>{
    return this.http.post<any>(this.url + `${API_CONFIG.ENDPOINTS.USERS.BASE}`,{
      email: user.email,
      name: user.name,
      password: user.password,
      is_active: user.is_active,
      role: user.role,
      email_verified_at: user.email_verified_at
    });
  }

  put(user: UserDTO): Observable<ResponseDTO<UserDTO>>{
    return this.http.put<any>(this.url + `${API_CONFIG.ENDPOINTS.USERS.BASE}/${user.id}`,{
      email: user.email,
      name: user.name,
      password: user.password,
      is_active: user.is_active,
      role: user.role
    });
  }
  getById(id: number): Observable<ResponseDTO<UserDTO>>{
    return this.http.get<ResponseDTO<UserDTO>>(this.url + `${API_CONFIG.ENDPOINTS.USERS.BASE}/${id}`)
  }
  delete(id: number): Observable<any> {
    return this.http.delete(`${this.url}${API_CONFIG.ENDPOINTS.USERS.BASE}/${id}`);
  }
  toListUserDTO(data: any[]){
    return data.map(user => new UserDTO(user))
  }

  formatDateTime(date: Date): string {
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    const seconds = String(date.getSeconds()).padStart(2, '0');
    
    return `${day}/${month}/${year} ${hours}:${minutes}:${seconds}`;
  }
  calcTotalPages(usersQuantity: number): number{
    return Math.ceil(usersQuantity / 5);
  }
  getUsers(){
    return this.users;
  }
  getUserInfo(id: number): UserDTO{
    return this.users.find(user => user.id === id) || new UserDTO({});
  }
  getUserByPage(page: number){
    return this.users.slice((page - 1) * 5, page * 5);
  }
}


