export type MetricCard = {
    label: string;
    value: number | string;
    hint?: string;
};

export type EmployeeListItem = {
    id: number;
    employee_number: string;
    full_name: string;
    email?: string | null;
    role?: string | null;
    department?: string | null;
    position?: string | null;
    manager?: string | null;
    employment_status: string;
    base_salary: number | string;
};

export type AttendanceLog = {
    id: number;
    work_date: string;
    clock_in_at?: string | null;
    clock_out_at?: string | null;
    status: string;
    late_minutes: number;
    undertime_minutes: number;
    overtime_minutes: number;
};

export type LeaveBalanceSummary = {
    id: number;
    leave_type: string;
    allocated_days: number | string;
    used_days: number | string;
    remaining_days: number | string;
};

export type PayrollSummary = {
    id: number;
    employee: string;
    period_start: string;
    period_end: string;
    pay_date?: string;
    status: string;
    gross_pay: number | string;
    total_deductions: number | string;
    net_pay: number | string;
};
