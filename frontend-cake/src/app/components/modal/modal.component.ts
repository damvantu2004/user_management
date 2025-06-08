import { Component, Input, Output, EventEmitter, TemplateRef, ContentChild } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-modal',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './modal.component.html',
  styleUrl: './modal.component.css'
})
export class ModalComponent {
  @Input() isVisible: boolean = false;
  @Input() title: string = 'Thông báo';
  @Input() size: string = 'md';
  
  @Output() closeModal = new EventEmitter<void>();
  
  // Cho phép truyền nội dung từ bên ngoài
  @ContentChild('modalBody') modalBodyTemplate!: TemplateRef<any>;
  
  constructor() {}
  
  close() {
    this.isVisible = false;
    this.closeModal.emit();
  }
  // Ngăn chặn sự kiện click từ bên trong modal lan ra ngoài
  stopPropagation(event: Event) {
    event.stopPropagation();
  }
  
  // Trả về class cho kích thước modal
  getModalSizeClass(): string {
    switch(this.size) {
      case 'sm': return 'modal-sm';
      case 'lg': return 'modal-lg';
      case 'xl': return 'modal-xl';
      default: return '';
    }
  }
}