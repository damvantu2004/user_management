import { CommonModule } from '@angular/common';
import { Component, EventEmitter, Input, Output } from '@angular/core';

@Component({
  selector: 'app-pagination',
  imports: [CommonModule],
  templateUrl: './pagination.component.html',
  styleUrl: './pagination.component.css'
})
export class PaginationComponent {
  @Input() currentPage = 1;
  @Input() totalPages = 1
  @Input() disabled = false;
  @Output() pageChange = new EventEmitter<number>();
  
  numberPage: number[] = [];
  
  ngOnInit() {
    this.generatePagesNumber();
  }

  ngOnChanges() {
    this.generatePagesNumber();
  }

  onPageChange(page: number) {
    if (page !== this.currentPage && page > 0 && page <= this.totalPages) {
      this.currentPage = page;
      this.pageChange.emit(page);
      this.generatePagesNumber();
    }
  }

  generatePagesNumber() {

    this.numberPage = [];
    let startPage: number = 1;
    let endPage: number = Math.min(this.totalPages, 10);

    
    if(this.totalPages >10){
      if(this.currentPage < 3){
        startPage = 1;
        endPage = Math.min(this.totalPages, 10)
      }else if(this.currentPage + 8 <= this.totalPages){
        startPage = this.currentPage-1;
        endPage = this.currentPage+8
      }else{
        startPage = this.totalPages - 9;
        endPage = this.totalPages
        console.log(123)
      }
    }
  
    for (let i = startPage; i <= endPage; i++) {
      this.numberPage.push(i);
    }
  }
}
