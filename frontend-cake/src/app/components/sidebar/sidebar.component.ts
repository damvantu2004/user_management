import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink, RouterLinkActive } from '@angular/router';
import { AuthService } from '../../services/auth.service';
import { NavigationService, NavigationItem } from '../../services/navigation.service';

@Component({
  selector: 'app-sidebar',
  standalone: true,
  imports: [CommonModule, RouterLink, RouterLinkActive],
  templateUrl: './sidebar.component.html',
  styleUrl: './sidebar.component.css'
})
export class SidebarComponent implements OnInit {
  isShow = false;
  navigationItems: NavigationItem[] = [];
  quickActions: NavigationItem[] = [];
  currentUser: any = null;
  roleTitle: string = '';
  roleColor: string = 'primary';
  roleIcon: string = 'fas fa-user';
  roleName: string = '';

  constructor(
    private authService: AuthService,
    private navigationService: NavigationService
  ) {}

  ngOnInit(): void {
    this.currentUser = this.authService.getCurrentUser();
    this.navigationItems = this.navigationService.getNavigationItems();
    this.quickActions = this.navigationService.getQuickActions();
    this.roleTitle = this.navigationService.getRoleBasedTitle();
    this.roleColor = this.navigationService.getRoleBasedColor();
    this.roleIcon = this.navigationService.getRoleIcon();
    this.roleName = this.navigationService.getRoleName();
  }

  toggleSidebar(): void {
    this.isShow = !this.isShow;
  }

  logout(): void {
    this.authService.logout().subscribe({
      next: () => {
        // Navigate to home after logout
        window.location.href = '/';
      },
      error: (error) => {
        console.error('Logout error:', error);
        // Force logout on error
        localStorage.clear();
        window.location.href = '/';
      }
    });
  }

  getDashboardUrl(): string {
    return this.authService.getDashboardUrl();
  }
}
