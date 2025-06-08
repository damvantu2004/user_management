import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { FormsModule, NgForm } from '@angular/forms';
import { loginDto } from '../../dto/login.dto';
import { RouterLink } from '@angular/router';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-send-email',
  imports: [CommonModule, FormsModule, RouterLink],
  templateUrl: './send-email.component.html',
  styleUrl: './send-email.component.css'
})
export class SendEmailComponent {
    model: loginDto;
    success = {
      status: true,
      hidden: true,
      loading: false
    }
    constructor(private authService: AuthService){
      this.model = new loginDto({});
    }
    
    onSendEmail(form: NgForm){
      console.log(this.model);


      this.authService.sendEmailToResetPassword(this.model).subscribe({
        next: (response) => {
          console.log(response);
          this.success.status = true;
          this.success.loading = false;
          this.success.hidden = false;

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

    
}
