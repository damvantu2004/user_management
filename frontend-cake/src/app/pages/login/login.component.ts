import { Component, ViewChild, OnInit, AfterViewInit, NgZone, ChangeDetectorRef } from '@angular/core';
import { FormsModule, NgForm } from '@angular/forms';
import { loginDto } from '../../dto/login.dto';
import { CommonModule } from '@angular/common';
import { LoadingComponent } from "../../components/loading/loading.component";
import { AuthService } from '../../services/auth.service';
import { Router, RouterLink } from '@angular/router';

// Declare global type for window to use grecaptcha
declare global {
  interface Window {
    grecaptcha: any;
    onloadCallback: any;
  }
}

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink, LoadingComponent],
  templateUrl: './login.component.html',
  styleUrl: './login.component.css'
})
export class LoginComponent implements OnInit, AfterViewInit {
    @ViewChild('login') loginForm: any;
    model = {
      email: '',
      password: '',
      remember: false,
      captcha: 'temp-disabled' // Tạm thời bypass captcha
    };
    modelRegister = {
      name: '',
      email: '',
      phone: '',
      password: '',
      password_confirmation: '',
      role: 'patient', // Mặc định patient
      captcha: 'temp-disabled' // Tạm thời bypass captcha
    };
    message = {
      status: false,
      message: '',
      clicked: false
    };
    activeTab: string = 'login';
    loading: boolean = false;
    captchaError: boolean = false;
    
    // Site key for reCAPTCHA v2 Checkbox
    siteKey = '6LfweSkrAAAAAIY0vALyQjfGCMHAsHLyKE27oEXz';
    
    // Store reCAPTCHA widget IDs
    loginCaptchaId: number | null = null;
    registerCaptchaId: number | null = null;

    constructor(
      private authService: AuthService, 
      private router: Router,
      private zone: NgZone,
      private cdr: ChangeDetectorRef
    ){
      this.message = {
        status: false,
        message: '',
        clicked: false,
      }
    }

    ngOnInit() {
      // Define global callback for reCAPTCHA to call when loaded
      window.onloadCallback = () => {
        this.zone.run(() => {
          console.log('reCAPTCHA loaded and ready to render');
          this.renderCaptchas();
        });
      };
      
      // Add reCAPTCHA script with onload callback
      this.loadRecaptchaScript();
    }
    
    ngAfterViewInit() {
      // Ensure reCAPTCHA is rendered after view is initialized
      setTimeout(() => {
        if (window.grecaptcha && window.grecaptcha.render) {
          this.renderCaptchas();
        }
      }, 1000);
    }

    // Load reCAPTCHA script with callback
    loadRecaptchaScript() {
      // Check if script already exists
      const existingScript = document.querySelector('script[src*="recaptcha/api.js"]');
      if (existingScript) {
        existingScript.remove(); // Remove old script if exists
      }
      
      // Create new script
      const script = document.createElement('script');
      script.src = `https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit`;
      script.async = true;
      script.defer = true;
      document.head.appendChild(script);
      
      console.log('reCAPTCHA script added to page');
    }

    // Render captcha widgets
    renderCaptchas() {
      try {
        if (!window.grecaptcha || !window.grecaptcha.render) {
          console.error('grecaptcha is not loaded yet');
          return;
        }

        const loginCaptchaElement = document.getElementById('login-captcha');
        const registerCaptchaElement = document.getElementById('register-captcha');
        
        if (this.activeTab === 'login' && loginCaptchaElement) {
          // Check if widget has already been rendered
          if (loginCaptchaElement.childNodes.length === 0) {
            console.log('Rendering login captcha');
            try {
              this.loginCaptchaId = window.grecaptcha.render('login-captcha', {
                'sitekey': this.siteKey,
                'callback': (response: string) => {
                  this.zone.run(() => {
                    this.model.captcha = response;
                    console.log('Captcha resolved for login');
                  });
                }
              });
            } catch (e) {
              if (!(e instanceof Error) || !e.toString().includes('already been rendered')) {
                console.error('Error rendering login captcha:', e);
              }
            }
          }
        } else if (this.activeTab === 'register' && registerCaptchaElement) {
          // Check if widget has already been rendered
          if (registerCaptchaElement.childNodes.length === 0) {
            console.log('Rendering register captcha');
            try {
              this.registerCaptchaId = window.grecaptcha.render('register-captcha', {
                'sitekey': this.siteKey,
                'callback': (response: string) => {
                  this.zone.run(() => {
                    this.modelRegister.captcha = response;
                    console.log('Captcha resolved for register');
                  });
                }
              });
            } catch (e) {
              if (!(e instanceof Error) || !e.toString().includes('already been rendered')) {
                console.error('Error rendering register captcha:', e);
              }
            }
          }
        }
      } catch (error) {
        console.error('Error in renderCaptchas:', error);
      }
    }

    // Reset captcha for current tab
    resetCaptcha() {
      try {
        if (window.grecaptcha) {
          if (this.activeTab === 'login' && this.loginCaptchaId !== null) {
            window.grecaptcha.reset(this.loginCaptchaId);
            this.model.captcha = 'temp-disabled';
          } else if (this.activeTab === 'register' && this.registerCaptchaId !== null) {
            window.grecaptcha.reset(this.registerCaptchaId);
            this.modelRegister.captcha = 'temp-disabled';
          }
        }
      } catch (error) {
        console.error('Error resetting captcha:', error);
      }
    }
    onLogin(form: any): void {
      if (form.valid) {
        this.loading = true;
        this.resetMessages();
        
        const loginData = new loginDto({
          email: this.model.email,
          password: this.model.password,
          remember: this.model.remember,
          captcha: this.model.captcha
        });

        this.authService.login(loginData).subscribe({
          next: (response) => {
            this.loading = false;
            this.message = {
              status: false,
              message: 'Đăng nhập thành công!',
              clicked: true
            };
            
            // Role-based redirect
            const user = this.authService.getCurrentUser();
            console.log('User role:', user.role); // Debug
            
            setTimeout(() => {
              if (user.role === 'admin') {
                this.router.navigate(['/dashboard']);
              } else if (user.role === 'doctor') {
                this.router.navigate(['/doctor-dashboard']); // Sẽ tạo ở Phase 3
              } else if (user.role === 'patient') {
                this.router.navigate(['/patient-dashboard']); // Sẽ tạo ở Phase 3
              } else {
              this.router.navigate(['/']);
            }
            }, 1000); // Delay 1s để user thấy message
          },
          error: (error) => {
            this.loading = false;
            this.message = {
              status: true,
              message: error.error?.message || 'Đăng nhập thất bại!',
              clicked: false
            };
          }
        });
      }
    }
    onRegister(form: NgForm){
      this.captchaError = false;
      
      if(form.valid && this.passwordMatching()){
        // Check captcha
        if(!this.modelRegister.captcha){
          this.captchaError = true;
          this.message.status = true;
          this.message.message = 'Vui lòng xác nhận bạn không phải là robot';
          return;
        }
        
        // Continue with form submission
        this.loading = true;
        this.resetMessages();
        this.authService.register(new loginDto(this.modelRegister)).subscribe({
          next: (response) => {
            console.log(response.data);
            this.loading = false;
            this.message.clicked = true;
            this.message.status = false;
            this.message.message = "Đăng kí thành công vui lòng xác thực email";
          },
          error: (errorResponse) => {
            let errors = errorResponse.error.errors;
            this.message.status = true;
            this.message.message = errors?.email?.[0] || 'Đăng ký thất bại';
            this.loading = false;
            
            // Reset reCAPTCHA on error
            this.resetCaptcha();
          },
          complete: () => {
            setTimeout(()=>{
              this.switchTab('login')
            },4000)
          }
        });
      }
    }
    switchTab(tab: string){
      this.message.status = false;
      this.message.message = '';
      this.message.clicked = false;
      this.captchaError = false;
      this.activeTab = (tab === 'login' ? 'login': 'register');
      
      // Re-render captchas after switching tabs
      setTimeout(() => {
        this.renderCaptchas();
      }, 100);
      
      this.cdr.detectChanges();
    }
    passwordMatching(){      
      return this.modelRegister.password === this.modelRegister.password_confirmation;
    }
    resetMessages(): void {
      this.message = {
        status: false,
        message: '',
        clicked: false
      };
    }
}
