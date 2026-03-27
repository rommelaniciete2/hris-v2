export type Role = {
    id: number;
    name: string;
    slug: string;
};

export type User = {
    id: number;
    name: string;
    email: string;
    role_id?: number | null;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type CapabilityMap = {
    employees: boolean;
    attendance: boolean;
    leave: boolean;
    payroll: boolean;
    reports: boolean;
    recruitment: boolean;
    performance: boolean;
    documents: boolean;
};

export type Auth = {
    user: User;
    role?: Role | null;
    permissions: string[];
    employee_id?: number | null;
    capabilities: CapabilityMap;
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};
