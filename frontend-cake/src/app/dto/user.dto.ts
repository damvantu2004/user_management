export class UserDTO{
    id: number
    name: string
    email: string
    is_active: boolean
    created_at: Date 
    updated_at: Date 
    email_verified_at: string
    role: string
    password: string | undefined
    constructor(user: any){
        this.id = user.id;
        this.name = user.name;
        this.email = user.email;
        this.is_active = user.is_active;
        this.created_at = user.created_at;
        this.updated_at = user.updated_at;
        this.role = user.role;
        this.password = user.password;
        this.email_verified_at = new Date().toISOString();
    }

}