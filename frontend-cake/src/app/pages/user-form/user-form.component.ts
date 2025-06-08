import { Component, inject } from '@angular/core';
import { LoadingComponent } from "../../components/loading/loading.component";
import { UserService } from '../../services/user.service';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { UserDTO } from '../../dto/user.dto';
import { FormsModule, NgForm } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { ModalComponent } from "../../components/modal/modal.component";
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-user-form',
  imports: [LoadingComponent, RouterLink, FormsModule, CommonModule, ModalComponent],
  templateUrl: './user-form.component.html',
  styleUrl: './user-form.component.css'
})
export class UserFormComponent {
  success = {
    isEditMode: false,
    modalTitle: true,
    showModal: false,
    message: []
  }

  model: UserDTO = new UserDTO({});
  formattedCreatedAt: string = '';
  formattedUpdatedAt: string = '';
  disablePassword = true;
  constructor(
    private userService: UserService,
    private router: Router,
    private route: ActivatedRoute,
    private authService: AuthService
  ) {}
  ngOnInit(){
    this.route.params.subscribe(params => {
      if(params['id']){
        console.log("as")
        this.success.isEditMode = true;
        this.userService.getById(params['id']).subscribe({
          next: (response) => {
            this.model = response.data;
            this.formattedCreatedAt = this.userService.formatDateTime(new Date(response.data.created_at));
            this.formattedUpdatedAt = this.userService.formatDateTime(new Date(response.data.updated_at));
            console.log(this.formattedCreatedAt,this.formattedUpdatedAt)

          }
        })
      }
    })
  }

  onEdit(form: NgForm){
    console.log(form.value)
    if(form.submitted){
      console.log(this.success.isEditMode)
      if(this.success.isEditMode){
        this.updateUser(this.model);
      }else{

        this.storeUser(this.model);
      }
    }
  }

  storeUser(user: UserDTO){
    
    this.userService.post(user).subscribe({
      next: (response) => {
        this.success.showModal=true;
        this.success.modalTitle=true;
        setTimeout(()=>{
          this.router.navigate(['/dashboard/users']);
        },3000)
      },
      error: (errorResponse) => {
        this.success.showModal = true;
        this.success.modalTitle = false;
        this.success.message = Object.values(errorResponse.error.errors)
      }
    })
  }
  updateUser(user: UserDTO){
    this.userService.put(user).subscribe({
      next: (response) => {
        this.success.showModal=true;
        this.success.modalTitle=true;
        this.authService.setCurrentUser(response.data);
        console.log(response)
        setTimeout(()=>{
          this.router.navigate(['/dashboard/users']);
        },3000)
      },
      error: (errorResponse) => {
        console.log(errorResponse.error.errors);
        this.success.showModal = true;
        this.success.modalTitle = false;
        this.success.message = Object.values(errorResponse.error.errors)
      }
    })
  }

  onClose(){
    this.success.showModal = false;
  }
  disablePass(){
    this.disablePassword = !this.disablePassword;
    this.model.password=undefined;
  }
}
