<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import DocumentController from '@/actions/App/Http/Controllers/Hris/DocumentController';
import EmployeeController from '@/actions/App/Http/Controllers/Hris/EmployeeController';
import Heading from '@/components/Heading.vue';
import HrisSelectField from '@/components/HrisSelectField.vue';
import HrisStatCard from '@/components/HrisStatCard.vue';
import HrisStatusBadge from '@/components/HrisStatusBadge.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { formatHrisAmount } from '@/lib/hris';
import { show as documentShow } from '@/routes/hris/documents';
import { index as employeesIndex } from '@/routes/hris/employees';
import { show as payrollShow } from '@/routes/hris/payroll';

const props = defineProps<{
    employee: Record<string, unknown>;
    roles: Array<Record<string, unknown>>;
    departments: Array<Record<string, unknown>>;
    positions: Array<Record<string, unknown>>;
    employeeOptions: Array<Record<string, unknown>>;
    attendanceLocations: Array<Record<string, unknown>>;
    workSchedules: Array<Record<string, unknown>>;
    documents: Array<Record<string, unknown>>;
    attendanceLogs: Array<Record<string, unknown>>;
    payrolls: Array<Record<string, unknown>>;
    leaveBalances: Array<Record<string, unknown>>;
    leaveHistory: Array<Record<string, unknown>>;
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Employees', href: employeesIndex() }],
    },
});

const page = usePage();
const canManage = computed(() => page.props.auth.capabilities.employees);

function toSelectValue(value: unknown): string | undefined {
    if (value === null || value === undefined) {
        return undefined;
    }

    return String(value);
}

function toOptionalSelectValue(value: unknown): string {
    return toSelectValue(value) ?? '';
}

const employmentStatusOptions = [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
];
const employmentTypeOptions = [
    { value: 'regular', label: 'Regular' },
    { value: 'probationary', label: 'Probationary' },
    { value: 'contractual', label: 'Contractual' },
];
</script>

<template>
    <Head :title="String(props.employee.full_name)" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <section class="grid gap-4 md:grid-cols-3">
            <Card class="border-border/70 shadow-sm md:col-span-2">
                <CardHeader class="gap-6">
                    <div
                        class="flex flex-wrap items-start justify-between gap-3"
                    >
                        <Heading
                            :title="String(props.employee.full_name)"
                            description="Simple employee profile with quick access to the records HR uses most."
                        />
                        <HrisStatusBadge
                            :status="props.employee.employment_status"
                        />
                    </div>
                </CardHeader>

                <CardContent class="grid gap-4 md:grid-cols-2">
                    <HrisStatCard
                        title="Employee number"
                        :value="
                            String(props.employee.employee_number ?? 'Not set')
                        "
                        :description="
                            String(
                                props.employee.email ??
                                    'No email address on file',
                            )
                        "
                        class="border-transparent bg-muted/40 shadow-none"
                        value-class="text-lg"
                    />
                    <HrisStatCard
                        title="Job details"
                        :value="
                            String(
                                props.employee.position ??
                                    'Unassigned position',
                            )
                        "
                        :description="
                            String(
                                props.employee.department ??
                                    'Unassigned department',
                            )
                        "
                        class="border-transparent bg-muted/40 shadow-none"
                        value-class="text-lg"
                    />
                </CardContent>
            </Card>

            <Card class="border-border/70 shadow-sm">
                <CardHeader>
                    <Heading
                        variant="small"
                        title="Work Setup"
                        description="Assigned site and schedule."
                    />
                </CardHeader>

                <CardContent class="space-y-4 text-sm">
                    <div>
                        <p class="text-muted-foreground">Attendance site</p>
                        <p class="mt-1 font-medium">
                            {{
                                props.employee.attendance_location ?? 'Not set'
                            }}
                        </p>
                    </div>
                    <div>
                        <p class="text-muted-foreground">Work schedule</p>
                        <p class="mt-1 font-medium">
                            {{ props.employee.work_schedule ?? 'Not set' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-muted-foreground">Employment</p>
                        <div class="mt-1 flex flex-wrap items-center gap-2">
                            <HrisStatusBadge
                                :status="props.employee.employment_status"
                            />
                            <HrisStatusBadge
                                :status="props.employee.employment_type"
                            />
                        </div>
                    </div>
                    <div>
                        <p class="text-muted-foreground">Base salary</p>
                        <p class="mt-1 font-medium">
                            {{ props.employee.base_salary }}
                        </p>
                    </div>
                </CardContent>
            </Card>
        </section>

        <section class="grid gap-4 xl:grid-cols-[1.1fr_0.9fr]">
            <Card class="border-border/70 shadow-sm">
                <CardHeader>
                    <Heading
                        variant="small"
                        :title="
                            canManage ? 'Update Profile' : 'Profile Details'
                        "
                        :description="
                            canManage
                                ? 'Keep employee records current without leaving the profile.'
                                : 'Contact and profile details assigned to this employee.'
                        "
                    />
                </CardHeader>

                <CardContent>
                    <Form
                        v-if="canManage"
                        v-bind="
                            EmployeeController.update.form(
                                Number(props.employee.id),
                            )
                        "
                        class="space-y-4"
                        #default="{ errors, processing }"
                    >
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="employee_number"
                                    >Employee number</Label
                                >
                                <Input
                                    id="employee_number"
                                    name="employee_number"
                                    :default-value="
                                        String(
                                            props.employee.employee_number ??
                                                '',
                                        )
                                    "
                                />
                                <InputError :message="errors.employee_number" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="email">Email</Label>
                                <Input
                                    id="email"
                                    name="email"
                                    type="email"
                                    :default-value="
                                        String(props.employee.email ?? '')
                                    "
                                />
                                <InputError :message="errors.email" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="first_name">First name</Label>
                                <Input
                                    id="first_name"
                                    name="first_name"
                                    :default-value="
                                        String(props.employee.first_name ?? '')
                                    "
                                />
                            </div>
                            <div class="grid gap-2">
                                <Label for="last_name">Last name</Label>
                                <Input
                                    id="last_name"
                                    name="last_name"
                                    :default-value="
                                        String(props.employee.last_name ?? '')
                                    "
                                />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="role_id">Role</Label>
                                <HrisSelectField
                                    id="role_id"
                                    name="role_id"
                                    placeholder="Select role"
                                    :options="props.roles"
                                    option-value="id"
                                    option-label="name"
                                    :default-value="
                                        toSelectValue(props.employee.role_id)
                                    "
                                />
                            </div>
                            <div class="grid gap-2">
                                <Label for="hire_date">Hire date</Label>
                                <Input
                                    id="hire_date"
                                    name="hire_date"
                                    type="date"
                                    :default-value="
                                        String(props.employee.hire_date ?? '')
                                    "
                                />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="department_id">Department</Label>
                                <HrisSelectField
                                    id="department_id"
                                    name="department_id"
                                    empty-label="Optional"
                                    :options="props.departments"
                                    option-value="id"
                                    option-label="name"
                                    :default-value="
                                        toOptionalSelectValue(
                                            props.employee.department_id,
                                        )
                                    "
                                />
                            </div>
                            <div class="grid gap-2">
                                <Label for="position_id">Position</Label>
                                <HrisSelectField
                                    id="position_id"
                                    name="position_id"
                                    empty-label="Optional"
                                    :options="props.positions"
                                    option-value="id"
                                    option-label="name"
                                    :default-value="
                                        toOptionalSelectValue(
                                            props.employee.position_id,
                                        )
                                    "
                                />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="attendance_location_id"
                                    >Attendance site</Label
                                >
                                <HrisSelectField
                                    id="attendance_location_id"
                                    name="attendance_location_id"
                                    placeholder="Select attendance site"
                                    :options="props.attendanceLocations"
                                    option-value="id"
                                    option-label="name"
                                    :default-value="
                                        toSelectValue(
                                            props.employee
                                                .attendance_location_id,
                                        )
                                    "
                                />
                            </div>
                            <div class="grid gap-2">
                                <Label for="work_schedule_id"
                                    >Work schedule</Label
                                >
                                <HrisSelectField
                                    id="work_schedule_id"
                                    name="work_schedule_id"
                                    placeholder="Select work schedule"
                                    :options="props.workSchedules"
                                    option-value="id"
                                    option-label="name"
                                    :default-value="
                                        toSelectValue(
                                            props.employee.work_schedule_id,
                                        )
                                    "
                                />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            <div class="grid gap-2">
                                <Label for="employment_status">Status</Label>
                                <HrisSelectField
                                    id="employment_status"
                                    name="employment_status"
                                    :options="employmentStatusOptions"
                                    :default-value="
                                        toSelectValue(
                                            props.employee.employment_status,
                                        )
                                    "
                                />
                            </div>
                            <div class="grid gap-2">
                                <Label for="employment_type">Type</Label>
                                <HrisSelectField
                                    id="employment_type"
                                    name="employment_type"
                                    :options="employmentTypeOptions"
                                    :default-value="
                                        toSelectValue(
                                            props.employee.employment_type,
                                        )
                                    "
                                />
                            </div>
                            <div class="grid gap-2">
                                <Label for="base_salary">Base salary</Label>
                                <Input
                                    id="base_salary"
                                    name="base_salary"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    :default-value="
                                        String(props.employee.base_salary ?? '')
                                    "
                                />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="phone">Phone</Label>
                                <Input
                                    id="phone"
                                    name="phone"
                                    :default-value="
                                        String(props.employee.phone ?? '')
                                    "
                                />
                            </div>
                            <div class="grid gap-2">
                                <Label for="address">Address</Label>
                                <Input
                                    id="address"
                                    name="address"
                                    :default-value="
                                        String(props.employee.address ?? '')
                                    "
                                />
                            </div>
                        </div>

                        <Button :disabled="processing">
                            {{ processing ? 'Saving...' : 'Save changes' }}
                        </Button>
                    </Form>

                    <div v-else class="grid gap-4 text-sm md:grid-cols-2">
                        <Card class="border-border/60 shadow-none">
                            <CardHeader class="gap-1.5">
                                <CardDescription>Phone</CardDescription>
                                <CardTitle class="text-base">{{
                                    props.employee.phone ?? 'Not provided'
                                }}</CardTitle>
                            </CardHeader>
                        </Card>
                        <Card class="border-border/60 shadow-none">
                            <CardHeader class="gap-1.5">
                                <CardDescription>Address</CardDescription>
                                <CardTitle class="text-base">{{
                                    props.employee.address ?? 'Not provided'
                                }}</CardTitle>
                            </CardHeader>
                        </Card>
                        <Card class="border-border/60 shadow-none">
                            <CardHeader class="gap-1.5">
                                <CardDescription>Manager</CardDescription>
                                <CardTitle class="text-base">{{
                                    props.employee.manager ?? 'Not assigned'
                                }}</CardTitle>
                            </CardHeader>
                        </Card>
                        <Card class="border-border/60 shadow-none">
                            <CardHeader class="gap-1.5">
                                <CardDescription>Hire date</CardDescription>
                                <CardTitle class="text-base">{{
                                    props.employee.hire_date ?? 'Not set'
                                }}</CardTitle>
                            </CardHeader>
                        </Card>
                    </div>
                </CardContent>
            </Card>

            <Card class="border-border/70 shadow-sm">
                <CardHeader>
                    <Heading
                        variant="small"
                        title="Documents"
                        description="Upload and access employee files from one place."
                    />
                </CardHeader>

                <CardContent class="space-y-6">
                    <Form
                        v-bind="DocumentController.store.form()"
                        class="space-y-4"
                        reset-on-success
                        #default="{ errors, processing }"
                    >
                        <input
                            type="hidden"
                            name="employee_id"
                            :value="String(props.employee.id)"
                        />

                        <div class="grid gap-2">
                            <Label for="category">Category</Label>
                            <Input
                                id="category"
                                name="category"
                                placeholder="Contract, ID, handbook"
                            />
                            <InputError :message="errors.category" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="file">File</Label>
                            <Input id="file" name="file" type="file" />
                            <InputError :message="errors.file" />
                        </div>

                        <Button :disabled="processing">
                            {{ processing ? 'Uploading...' : 'Upload file' }}
                        </Button>
                    </Form>

                    <div class="space-y-3 text-sm">
                        <Card
                            v-for="document in props.documents"
                            :key="String(document.id)"
                            class="border-border/60 shadow-none"
                        >
                            <CardHeader class="gap-1.5">
                                <CardTitle class="text-base">{{
                                    document.name
                                }}</CardTitle>
                                <CardDescription
                                    >{{ document.category }} /
                                    {{
                                        document.original_name
                                    }}</CardDescription
                                >
                            </CardHeader>
                            <CardContent>
                                <Button as-child size="sm" variant="outline">
                                    <Link
                                        :href="
                                            documentShow(Number(document.id))
                                        "
                                    >
                                        Download
                                    </Link>
                                </Button>
                            </CardContent>
                        </Card>
                        <p
                            v-if="props.documents.length === 0"
                            class="rounded-xl border border-dashed border-border/60 px-4 py-6 text-muted-foreground"
                        >
                            No documents have been uploaded for this employee
                            yet.
                        </p>
                    </div>
                </CardContent>
            </Card>
        </section>

        <section class="grid gap-4 xl:grid-cols-3">
            <Card class="border-border/70 shadow-sm">
                <CardHeader>
                    <Heading
                        variant="small"
                        title="Attendance"
                        description="Recent daily logs for this employee."
                    />
                </CardHeader>

                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow class="hover:bg-transparent">
                                <TableHead>Date</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Late</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="attendance in props.attendanceLogs"
                                :key="String(attendance.id)"
                            >
                                <TableCell>{{
                                    attendance.work_date
                                }}</TableCell>
                                <TableCell>
                                    <HrisStatusBadge
                                        :status="attendance.status"
                                    />
                                </TableCell>
                                <TableCell
                                    >{{
                                        attendance.late_minutes
                                    }}
                                    min</TableCell
                                >
                            </TableRow>
                            <TableRow v-if="props.attendanceLogs.length === 0">
                                <TableCell
                                    colspan="3"
                                    class="h-24 text-center text-muted-foreground"
                                >
                                    No attendance logs are available for this
                                    employee yet.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <Card class="border-border/70 shadow-sm">
                <CardHeader>
                    <Heading
                        variant="small"
                        title="Payroll"
                        description="Recent payslips and net pay totals."
                    />
                </CardHeader>

                <CardContent class="space-y-3 text-sm">
                    <Card
                        v-for="payroll in props.payrolls"
                        :key="String(payroll.id)"
                        class="border-border/60 shadow-none"
                    >
                        <CardHeader class="gap-1.5">
                            <div class="flex items-start justify-between gap-3">
                                <CardTitle class="text-base">
                                    {{ payroll.period_start }} to
                                    {{ payroll.period_end }}
                                </CardTitle>
                                <HrisStatusBadge :status="payroll.status" />
                            </div>
                            <CardDescription>
                                Net pay: {{ formatHrisAmount(payroll.net_pay) }}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Button as-child size="sm" variant="outline">
                                <Link :href="payrollShow(Number(payroll.id))">
                                    Open payslip
                                </Link>
                            </Button>
                        </CardContent>
                    </Card>
                    <p
                        v-if="props.payrolls.length === 0"
                        class="rounded-xl border border-dashed border-border/60 px-4 py-6 text-muted-foreground"
                    >
                        No payslips have been generated for this employee yet.
                    </p>
                </CardContent>
            </Card>

            <Card class="border-border/70 shadow-sm">
                <CardHeader>
                    <Heading
                        variant="small"
                        title="Leave"
                        description="Balances and the latest leave history."
                    />
                </CardHeader>

                <CardContent class="space-y-4">
                    <div class="space-y-3 text-sm">
                        <Card
                            v-for="balance in props.leaveBalances"
                            :key="String(balance.id)"
                            class="border-border/60 shadow-none"
                        >
                            <CardHeader class="gap-1.5">
                                <CardTitle class="text-base">{{
                                    balance.leave_type
                                }}</CardTitle>
                                <CardDescription>
                                    {{ balance.remaining_days }} remaining of
                                    {{ balance.allocated_days }}
                                </CardDescription>
                            </CardHeader>
                        </Card>
                        <p
                            v-if="props.leaveBalances.length === 0"
                            class="rounded-xl border border-dashed border-border/60 px-4 py-6 text-muted-foreground"
                        >
                            No leave balances are available for this employee
                            yet.
                        </p>
                    </div>

                    <Table>
                        <TableHeader>
                            <TableRow class="hover:bg-transparent">
                                <TableHead>Type</TableHead>
                                <TableHead>Dates</TableHead>
                                <TableHead>Status</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="leave in props.leaveHistory"
                                :key="String(leave.id)"
                            >
                                <TableCell>{{ leave.leave_type }}</TableCell>
                                <TableCell
                                    >{{ leave.start_date }} to
                                    {{ leave.end_date }}</TableCell
                                >
                                <TableCell>
                                    <HrisStatusBadge :status="leave.status" />
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="props.leaveHistory.length === 0">
                                <TableCell
                                    colspan="3"
                                    class="h-24 text-center text-muted-foreground"
                                >
                                    No leave history is available for this
                                    employee yet.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </section>
    </div>
</template>
