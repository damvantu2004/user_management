export const API_CONFIG = {
    BASE_URL: 'http://localhost:8000',
    ENDPOINTS: {
        AUTH: {
            LOGIN: '/api/login',
            REGISTER: '/api/register',
            LOGOUT: '/api/logout',
            FORGOT_PASSWORD: '/api/password/forgot',
            RESET_PASSWORD: '/api/password/reset',
            ME: '/api/me'
        },
        USERS: {
            BASE: '/api/users',
            PROFILE: '/api/users/profile'
        },
        DOCTORS: {
            BASE: '/api/doctors',
            AVAILABLE: '/api/doctors/available',
            ME: '/api/doctors/me',
            SPECIALTIES: '/api/specialties'
        },
        PATIENTS: {
            BASE: '/api/patients',
            ME: '/api/patients/me'
        },
        APPOINTMENTS: {
            BASE: '/api/appointments',
            BY_STATUS: '/api/appointments/status',
            BY_DATE: '/api/appointments/date'
        }
    }
};

export const APP_CONFIG = {
    PAGE_SIZE: 10,
    DATE_FORMAT: 'dd/MM/yyyy',
    TIME_FORMAT: 'HH:mm',
    CURRENCY: 'VND',
    LANGUAGES: ['vi', 'en'],
    ROLES: ['admin', 'doctor', 'patient'],
    APPOINTMENT_STATUSES: ['pending', 'confirmed', 'completed', 'cancelled'],
    GENDERS: ['male', 'female', 'other']
};

export const AUTH_CONFIG = {
    TOKEN_KEY: 'access_token',
    REFRESH_TOKEN_KEY: 'refresh_token',
    TOKEN_EXPIRES_IN: 3600,
    ROLES: {
        ADMIN: 'admin',
        DOCTOR: 'doctor', 
        PATIENT: 'patient'
    }
};
