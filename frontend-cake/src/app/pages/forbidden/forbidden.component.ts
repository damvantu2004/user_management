import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-forbidden',
  standalone: true,
  imports: [],
  templateUrl: './forbidden.component.html',
  styleUrl: './forbidden.component.css'
})
export class ForbiddenComponent {
  constructor(private router: Router) {}

  goBack() {
    window.history.back();
  }

  goHome() {
    this.router.navigate(['/']);
  }
}
