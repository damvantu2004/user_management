export class PatientDTO {
    id?: number;
    user_id?: number;
    date_of_birth?: string;
    gender?: string;
    address?: string;
    emergency_contact?: string;
    user?: any;

    constructor(data: any = {}) {
        this.id = data.id;
        this.user_id = data.user_id;
        this.date_of_birth = data.date_of_birth;
        this.gender = data.gender;
        this.address = data.address;
        this.emergency_contact = data.emergency_contact;
        this.user = data.user;
    }
}
