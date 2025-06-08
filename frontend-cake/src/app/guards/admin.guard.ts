import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { AuthService } from '../services/auth.service';

export const adminGuard: CanActivateFn = (route, state) => {
  const authService = inject(AuthService);
  const router = inject(Router);

  if (authService.isAdmin()) {
    return true;
  }

  // Nếu đã đăng nhập nhưng không phải admin
  if (authService.getToken()) {
    router.navigate(['/forbidden']);
    return false;
  }

  // Nếu chưa đăng nhập
  router.navigate(['auth']);
  return false;
};