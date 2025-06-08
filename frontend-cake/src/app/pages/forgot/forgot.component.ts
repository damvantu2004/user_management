import { Component } from '@angular/core';
import { FormsModule, NgForm } from '@angular/forms';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { loginDto } from '../../dto/login.dto';
import { CommonModule } from '@angular/common';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-forgot',
  imports: [FormsModule, CommonModule, RouterLink],
  templateUrl: './forgot.component.html',
  styleUrl: './forgot.component.css'
})
export class ForgotComponent {
    model: loginDto;
    success = {
      status: true,
      hidden: true,
      loading: false
    }
    constructor(private routerA: ActivatedRoute, 
      private router: Router,
      private authService: AuthService){
      this.model = new loginDto({
        token: this.routerA.snapshot.queryParams['token'],
        email: this.routerA.snapshot.queryParams['email']
      })
    }

    onReset(form: NgForm){
      console.log(this.model)

      this.authService.resetPassword(this.model).subscribe({
        next: (response) => {
          console.log(response);
          this.success.status = true;
          this.success.loading = false;
          this.success.hidden = false;

          setTimeout(()=>{
            this.router.navigate(['/auth']);
          },4000)
        },
        error: (errorResponse) => {
          console.log(errorResponse);
          this.success.status = false;
          this.success.loading = false;
          this.success.hidden = false;
        }
      })
      this.success.loading=true;
    }

    passwordMatching(){      
      return this.model.password === this.model.password_confirmation;
    }
}
