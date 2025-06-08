import { Component, Input, SimpleChanges } from '@angular/core';
import { PaginationComponent } from "../../components/pagination/pagination.component";
import { Observable } from 'rxjs';
import { UserService } from '../../services/user.service';
import { UserDTO } from '../../dto/user.dto';
import { LoadingComponent } from '../../components/loading/loading.component';
import { PaginationResponse } from '../../dto/pagination.dto';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { ModalComponent } from '../../components/modal/modal.component';
import { CommonModule } from '@angular/common';
import { FormsModule, NgForm } from '@angular/forms';
import { searchDTO } from '../../dto/search.dto';

@Component({
  selector: 'app-user',
  imports: [PaginationComponent, LoadingComponent, RouterLink, ModalComponent, CommonModule, FormsModule],
  templateUrl: './user.component.html',
  styleUrl: './user.component.css'
})
export class UserComponent {
  users: PaginationResponse<UserDTO> = {
    data: [],
    current_page: 1,
    last_page: 0
  }
  disablePagination = false;
  loading = true;
  showModal = false;
  selectedUser: UserDTO = new UserDTO({});
  searchParam = new searchDTO({
    search: '',
    role: '',
    is_active: ''
  })

  success_delete = {
    modalTitle: true,
    showModal: false,
    message: []
  }
  constructor(private userService: UserService, private activeRoute: ActivatedRoute, private router: Router){
  }

  ngOnInit(): void{
      this.loadUsers(1);
  } 
  ngOnChanges(): void{
    // this.loadUsers();
    console.log('123')
  }
  loadUsers(page: number, searchParam?: searchDTO){
    this.userService.get(page, searchParam).subscribe({
      next: (response) => {
        this.users = response;
        this.users.data = this.userService.getUserByPage(page || 1);
      },
      error: (error) => {
        console.error('Có lỗi xảy ra', error);
      },
      complete: () => {
        this.loading = false;
        this.disablePagination = false;
      }
    })
  }

  deleteUser(id: number) {
    if (confirm('Bạn có chắc chắn muốn xóa người dùng này?')) {
      this.userService.delete(id).subscribe({
        next: (response) => {
          this.loadUsers(1);
          this.success_delete.modalTitle = true;
          this.success_delete.showModal = true;
          console.log(response.message)

        },
        error: (error) => {
          console.error('Lỗi khi xóa người dùng:', error);
          this.success_delete.modalTitle = false;
          this.success_delete.showModal = true;
          this.success_delete.message = Object.values(error.error.errors)
        }
      });
    }
  }
  getCurrentPage(e: number){
    this.users.current_page = e;
    this.users.data = this.userService.getUserByPage(e);
    // this.loadUsers();
    // this.disablePagination = true;
  }
  onDetail(id: number){
    this.selectedUser = this.userService.getUserInfo(id);
    this.showModal = true;
  }
  onClose(){
    this.showModal = false;
    this.success_delete.showModal = false;
    console.log("12")
    this.selectedUser = new UserDTO({});
  }
  onSearch(form: NgForm){
    console.log(this.searchParam)
    this.router.navigate(['/dashboard/users'], {
      queryParams: this.searchParam
    });
    
    this.loadUsers(1, this.searchParam);;
  }


}
