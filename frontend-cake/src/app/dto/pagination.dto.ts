export interface PaginationResponse<T>{
    data: T[],
    current_page: number,
    last_page: number
} 