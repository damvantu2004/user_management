import { Injectable } from '@angular/core';
import { AuthService } from './auth.service';

export interface NavigationItem {
  label: string;
  url: string;
  icon: string;
  roles?: string[];
  children?: NavigationItem[];
  divider?: boolean;
}

@Injectable({
  providedIn: 'root'
})
export class NavigationService {

  constructor(private authService: AuthService) {}

  getNavigationItems(): NavigationItem[] {
    const user = this.authService.getCurrentUser();
    if (!user) return [];

    const allItems: NavigationItem[] = [
      // Admin Navigation
      {
        label: 'Trang chủ Admin',
        url: '/dashboard',
        icon: 'fas fa-tachometer-alt',
        roles: ['admin']
      },
      {
        label: 'Quản lý người dùng',
        url: '/dashboard/users',
        icon: 'fas fa-users',
        roles: ['admin']
      },
      {
        label: 'Thêm người dùng',
        url: '/dashboard/users/add',
        icon: 'fas fa-user-plus',
        roles: ['admin']
      },

      // Patient Navigation
      {
        label: 'Trang chủ',
        url: '/patient-dashboard',
        icon: 'fas fa-home',
        roles: ['patient']
      },
      {
        label: 'Đặt lịch khám',
        url: '/book-appointment',
        icon: 'fas fa-calendar-plus',
        roles: ['patient']
      },
      {
        label: 'Lịch hẹn của tôi',
        url: '/my-appointments',
        icon: 'fas fa-calendar-alt',
        roles: ['patient']
      },
      {
        label: '',
        url: '',
        icon: '',
        divider: true,
        roles: ['patient']
      },
      {
        label: 'Thông tin cá nhân',
        url: '/patient-profile',
        icon: 'fas fa-user-edit',
        roles: ['patient']
      },

      // Doctor Navigation
      {
        label: 'Trang chủ',
        url: '/doctor-dashboard',
        icon: 'fas fa-stethoscope',
        roles: ['doctor']
      },
      {
        label: 'Quản lý lịch hẹn',
        url: '/doctor-appointments',
        icon: 'fas fa-calendar-check',
        roles: ['doctor']
      },
      {
        label: 'Bệnh nhân của tôi',
        url: '/my-patients',
        icon: 'fas fa-user-friends',
        roles: ['doctor']
      },
      {
        label: '',
        url: '',
        icon: '',
        divider: true,
        roles: ['doctor']
      },
      {
        label: 'Hồ sơ bác sĩ',
        url: '/doctor-profile',
        icon: 'fas fa-user-md',
        roles: ['doctor']
      }
    ];

    // Filter by user role
    return allItems.filter(item => 
      !item.roles || item.roles.includes(user.role)
    );
  }

  getRoleBasedTitle(): string {
    const user = this.authService.getCurrentUser();
    if (!user) return 'Hospital Management';

    switch (user.role) {
      case 'admin': return 'Admin Dashboard';
      case 'doctor': return 'Doctor Portal';
      case 'patient': return 'Patient Portal';
      default: return 'Hospital Management';
    }
  }

  getRoleBasedColor(): string {
    const user = this.authService.getCurrentUser();
    if (!user) return 'primary';

    switch (user.role) {
      case 'admin': return 'danger';
      case 'doctor': return 'success';
      case 'patient': return 'info';
      default: return 'primary';
    }
  }

  getRoleIcon(): string {
    const user = this.authService.getCurrentUser();
    if (!user) return 'fas fa-user';

    switch (user.role) {
      case 'admin': return 'fas fa-user-shield';
      case 'doctor': return 'fas fa-user-md';
      case 'patient': return 'fas fa-user-injured';
      default: return 'fas fa-user';
    }
  }

  getRoleName(): string {
    const user = this.authService.getCurrentUser();
    if (!user) return 'Người dùng';

    switch (user.role) {
      case 'admin': return 'Quản trị viên';
      case 'doctor': return 'Bác sĩ';
      case 'patient': return 'Bệnh nhân';
      default: return 'Người dùng';
    }
  }

  // Get quick actions based on role
  getQuickActions(): NavigationItem[] {
    const user = this.authService.getCurrentUser();
    if (!user) return [];

    const actions: NavigationItem[] = [];

    switch (user.role) {
      case 'admin':
        actions.push(
          { label: 'Thêm người dùng', url: '/dashboard/users/add', icon: 'fas fa-user-plus' },
          { label: 'Danh sách users', url: '/dashboard/users', icon: 'fas fa-list' }
        );
        break;
      
      case 'patient':
        actions.push(
          { label: 'Đặt lịch khám', url: '/book-appointment', icon: 'fas fa-calendar-plus' },
          { label: 'Xem lịch hẹn', url: '/my-appointments', icon: 'fas fa-calendar-alt' },
          { label: 'Cập nhật hồ sơ', url: '/patient-profile', icon: 'fas fa-user-edit' }
        );
        break;
      
      case 'doctor':
        actions.push(
          { label: 'Xem lịch hẹn', url: '/doctor-appointments', icon: 'fas fa-calendar-check' },
          { label: 'Danh sách bệnh nhân', url: '/my-patients', icon: 'fas fa-users' },
          { label: 'Cập nhật hồ sơ', url: '/doctor-profile', icon: 'fas fa-user-md' }
        );
        break;
    }

    return actions;
  }
}
