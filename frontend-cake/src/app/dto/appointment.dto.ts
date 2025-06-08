export class AppointmentDTO {
    id?: number;
    patient_id?: number;
    doctor_id?: number;
    appointment_date?: string;
    appointment_time?: string;
    status?: string;
    reason?: string;
    notes?: string;
    patient?: any;
    doctor?: any;

    constructor(data: any = {}) {
        this.id = data.id;
        this.patient_id = data.patient_id;
        this.doctor_id = data.doctor_id;
        this.appointment_date = data.appointment_date;
        this.appointment_time = data.appointment_time;
        this.status = data.status;
        this.reason = data.reason;
        this.notes = data.notes;
        this.patient = data.patient;
        this.doctor = data.doctor;
    }
}
