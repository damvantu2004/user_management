export class DoctorDTO {
    id?: number;
    user_id?: number;
    specialty?: string;
    qualification?: string;
    experience_years?: number;
    consultation_fee?: number;
    bio?: string;
    is_available?: boolean;
    user?: any;

    constructor(data: any = {}) {
        this.id = data.id;
        this.user_id = data.user_id;
        this.specialty = data.specialty;
        this.qualification = data.qualification;
        this.experience_years = data.experience_years;
        this.consultation_fee = data.consultation_fee;
        this.bio = data.bio;
        this.is_available = data.is_available;
        this.user = data.user;
    }
}
