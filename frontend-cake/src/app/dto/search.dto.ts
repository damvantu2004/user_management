export class searchDTO{
    name: string
    is_active: boolean
    role: string
    constructor(user: any){
        this.name = user.search;
        this.is_active = user.is_active;
        this.role = user.role;
    }

}