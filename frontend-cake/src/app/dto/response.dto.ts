export class ResponseDTO<T>{
    data: T;
    message: string;
    status: string;
    error: T;
    constructor(response: any){
        this.data = response.data;
        this.message = response.message;
        this.status = response.status;
        this.error = response.error;
    }
}