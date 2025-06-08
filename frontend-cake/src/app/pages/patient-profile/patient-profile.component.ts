import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { PatientService } from '../../services/patient.service';
import { AuthService } from '../../services/auth.service';
import { LoadingComponent } from '../../components/loading/loading.component';
import { SidebarComponent } from '../../components/sidebar/sidebar.component';

@Component({
  selector: 'app-patient-profile',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, RouterModule, LoadingComponent, SidebarComponent],
  templateUrl: './patient-profile.component.html',
  styleUrl: './patient-profile.component.css'
})
export class PatientProfileComponent implements OnInit {
  profileForm: FormGroup;
  passwordForm: FormGroup;
  currentUser: any = null;
  patientProfile: any = null;
  loading: boolean = false;
  saving: boolean = false;
  message: string = '';
  messageType: 'success' | 'error' = 'success';
  activeTab: 'profile' | 'password' = 'profile';

  bloodTypes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
  genders = [
    { value: 'male', label: 'Nam' },
    { value: 'female', label: 'Nữ' },
    { value: 'other', label: 'Khác' }
  ];

  constructor(
    private fb: FormBuilder,
    private patientService: PatientService,
    private authService: AuthService
  ) {
    this.profileForm = this.fb.group({
      name: ['', [Validators.required, Validators.minLength(2)]],
      email: ['', [Validators.required, Validators.email]],
      phone: ['', [Validators.required, Validators.pattern(/^[0-9]{10,11}$/)]],
      date_of_birth: [''],
      gender: [''],
      blood_type: [''],
      address: [''],
      emergency_contact: [''],
      emergency_phone: ['', [Validators.pattern(/^[0-9]{10,11}$/)]],
      medical_history: [''],
      allergies: [''],
      current_medications: ['']
    });

    this.passwordForm = this.fb.group({
      current_password: ['', Validators.required],
      new_password: ['', [Validators.required, Validators.minLength(6)]],
      confirm_password: ['', Validators.required]
    }, {
      validators: this.passwordMatchValidator
    });
  }

  ngOnInit(): void {
    this.currentUser = this.authService.getCurrentUser();
    this.loadProfile();
  }

  loadProfile(): void {
    this.loading = true;
    this.patientService.getMyProfile().subscribe({
      next: (response: any) => {
        this.patientProfile = response.data;
        this.populateForm();
        this.loading = false;
      },
      error: (error: any) => {
        console.error('Error loading profile:', error);
        this.showMessage('Không thể tải thông tin profile', 'error');
        this.loading = false;
      }
    });
  }

  populateForm(): void {
    if (this.patientProfile) {
      this.profileForm.patchValue({
        name: this.currentUser?.name || '',
        email: this.currentUser?.email || '',
        phone: this.currentUser?.phone || '',
        date_of_birth: this.patientProfile.date_of_birth || '',
        gender: this.patientProfile.gender || '',
        blood_type: this.patientProfile.blood_type || '',
        address: this.patientProfile.address || '',
        emergency_contact: this.patientProfile.emergency_contact || '',
        emergency_phone: this.patientProfile.emergency_phone || '',
        medical_history: this.patientProfile.medical_history || '',
        allergies: this.patientProfile.allergies || '',
        current_medications: this.patientProfile.current_medications || ''
      });
    }
  }

  onSubmitProfile(): void {
    if (this.profileForm.valid) {
      this.saving = true;
      const formData = this.profileForm.value;

      this.patientService.updateProfile(formData).subscribe({
        next: (response: any) => {
          this.showMessage('Cập nhật thông tin thành công!', 'success');
          // Update current user info
          this.authService.setCurrentUser({
            ...this.currentUser,
            name: formData.name,
            email: formData.email,
            phone: formData.phone
          });
          this.saving = false;
        },
        error: (error: any) => {
          console.error('Error updating profile:', error);
          const message = error.error?.message || 'Có lỗi xảy ra khi cập nhật';
          this.showMessage(message, 'error');
          this.saving = false;
        }
      });
    } else {
      this.markFormGroupTouched(this.profileForm);
    }
  }

  onSubmitPassword(): void {
    if (this.passwordForm.valid) {
      this.saving = true;
      const formData = this.passwordForm.value;

      this.patientService.changePassword(formData).subscribe({
        next: (response: any) => {
          this.showMessage('Đổi mật khẩu thành công!', 'success');
          this.passwordForm.reset();
          this.saving = false;
        },
        error: (error: any) => {
          console.error('Error changing password:', error);
          const message = error.error?.message || 'Có lỗi xảy ra khi đổi mật khẩu';
          this.showMessage(message, 'error');
          this.saving = false;
        }
      });
    } else {
      this.markFormGroupTouched(this.passwordForm);
    }
  }

  passwordMatchValidator(form: FormGroup) {
    const newPassword = form.get('new_password');
    const confirmPassword = form.get('confirm_password');
    
    if (newPassword && confirmPassword && newPassword.value !== confirmPassword.value) {
      confirmPassword.setErrors({ passwordMismatch: true });
      return { passwordMismatch: true };
    }
    
    return null;
  }

  markFormGroupTouched(formGroup: FormGroup): void {
    Object.keys(formGroup.controls).forEach(key => {
      const control = formGroup.get(key);
      control?.markAsTouched();
    });
  }

  showMessage(message: string, type: 'success' | 'error'): void {
    this.message = message;
    this.messageType = type;
    setTimeout(() => {
      this.message = '';
    }, 5000);
  }

  getFieldError(fieldName: string, form: FormGroup = this.profileForm): string {
    const field = form.get(fieldName);
    if (field?.touched && field?.errors) {
      if (field.errors['required']) {
        return `${this.getFieldLabel(fieldName)} là bắt buộc`;
      }
      if (field.errors['email']) {
        return 'Email không hợp lệ';
      }
      if (field.errors['minlength']) {
        return `${this.getFieldLabel(fieldName)} phải có ít nhất ${field.errors['minlength'].requiredLength} ký tự`;
      }
      if (field.errors['pattern']) {
        return `${this.getFieldLabel(fieldName)} không đúng định dạng`;
      }
      if (field.errors['passwordMismatch']) {
        return 'Mật khẩu xác nhận không khớp';
      }
    }
    return '';
  }

  getFieldLabel(fieldName: string): string {
    const labels: { [key: string]: string } = {
      'name': 'Họ tên',
      'email': 'Email',
      'phone': 'Số điện thoại',
      'current_password': 'Mật khẩu hiện tại',
      'new_password': 'Mật khẩu mới',
      'confirm_password': 'Xác nhận mật khẩu'
    };
    return labels[fieldName] || fieldName;
  }

  isFieldInvalid(fieldName: string, form: FormGroup = this.profileForm): boolean {
    const field = form.get(fieldName);
    return !!(field?.invalid && field?.touched);
  }

  setActiveTab(tab: 'profile' | 'password'): void {
    this.activeTab = tab;
    this.message = ''; // Clear messages when switching tabs
  }

  calculateAge(): number {
    if (!this.profileForm.get('date_of_birth')?.value) return 0;
    
    const birthDate = new Date(this.profileForm.get('date_of_birth')?.value);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
      age--;
    }
    
    return age;
  }
}
