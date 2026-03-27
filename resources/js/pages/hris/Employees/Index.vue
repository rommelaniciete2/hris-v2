<script setup lang="ts">
import { Form, Head, Link, router } from '@inertiajs/vue3';
import { CircleCheck, TriangleAlert } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import ApplicantHireController from '@/actions/App/Http/Controllers/Api/V1/ApplicantHireController';
import ApplicantController from '@/actions/App/Http/Controllers/Hris/ApplicantController';
import DepartmentController from '@/actions/App/Http/Controllers/Hris/DepartmentController';
import EmployeeController from '@/actions/App/Http/Controllers/Hris/EmployeeController';
import JobPostingController from '@/actions/App/Http/Controllers/Hris/JobPostingController';
import PerformanceReviewController from '@/actions/App/Http/Controllers/Hris/PerformanceReviewController';
import Heading from '@/components/Heading.vue';
import HrisSelectField from '@/components/HrisSelectField.vue';
import HrisStatusBadge from '@/components/HrisStatusBadge.vue';
import InputError from '@/components/InputError.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
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
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { postJson } from '@/lib/http';
import {
    index as employeesIndex,
    show as employeeShow,
} from '@/routes/hris/employees';

const props = defineProps<{
    filters: { search: string; tab: string };
    employees: { data: Array<Record<string, unknown>> };
    departments: Array<Record<string, unknown>>;
    positions: Array<Record<string, unknown>>;
    roles: Array<Record<string, unknown>>;
    employeeOptions: Array<Record<string, unknown>>;
    attendanceLocations: Array<Record<string, unknown>>;
    workSchedules: Array<Record<string, unknown>>;
    jobPostings: Array<Record<string, unknown>>;
    applicants: Array<Record<string, unknown>>;
    performanceReviews: Array<Record<string, unknown>>;
    reviewCycles: Array<Record<string, unknown>>;
    upcomingInterviews: Array<Record<string, unknown>>;
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Employees', href: employeesIndex() }],
    },
});

const tabs = [
    { key: 'directory', label: 'Directory' },
    { key: 'organization', label: 'Organization' },
    { key: 'recruitment', label: 'Recruitment' },
    { key: 'performance', label: 'Performance' },
] as const;

const performanceKpis = ['Quality', 'Delivery', 'Teamwork'] as const;
const employmentStatusOptions = [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
];
const employmentTypeOptions = [
    { value: 'regular', label: 'Regular' },
    { value: 'probationary', label: 'Probationary' },
    { value: 'contractual', label: 'Contractual' },
];
const jobPostingStatusOptions = [
    { value: 'open', label: 'Open' },
    { value: 'paused', label: 'Paused' },
    { value: 'closed', label: 'Closed' },
];
const performanceScoreOptions = Array.from({ length: 5 }, (_, index) => ({
    value: String(index + 1),
    label: String(index + 1),
}));

const activeTab = ref<(typeof tabs)[number]['key']>(
    (tabs.find((tab) => tab.key === props.filters.tab)?.key ??
        'directory') as (typeof tabs)[number]['key'],
);
const search = ref(props.filters.search || '');
const hireBusy = ref(false);
const hireError = ref('');
const hireStatus = ref('');
const selectedApplicantId = ref<number | null>(
    Number(
        props.applicants.find((applicant) => applicant.stage !== 'hired')?.id ??
            0,
    ) || null,
);

const selectedApplicant = computed(
    () =>
        props.applicants.find(
            (applicant) => Number(applicant.id) === selectedApplicantId.value,
        ) ?? null,
);

const hireForm = ref({
    role_id: Number(
        props.roles.find((role) => role.slug === 'employee')?.id ??
            props.roles[0]?.id ??
            0,
    ),
    employee_number: '',
    first_name: '',
    last_name: '',
    email: '',
    hire_date: new Date().toISOString().slice(0, 10),
    department_id: Number(props.departments[0]?.id ?? 0) || null,
    position_id: Number(props.positions[0]?.id ?? 0) || null,
    manager_id: Number(props.employeeOptions[0]?.id ?? 0) || null,
    attendance_location_id: Number(props.attendanceLocations[0]?.id ?? 0),
    work_schedule_id: Number(props.workSchedules[0]?.id ?? 0),
    employment_type: 'regular',
    base_salary: '0',
});

function runSearch(): void {
    router.get(
        employeesIndex(),
        { search: search.value, tab: activeTab.value },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function selectApplicant(applicant: Record<string, unknown>): void {
    selectedApplicantId.value = Number(applicant.id);
    const parts = String(applicant.full_name ?? '')
        .trim()
        .split(/\s+/);

    hireForm.value.first_name = parts[0] ?? '';
    hireForm.value.last_name = parts.slice(1).join(' ') || 'Employee';
    hireForm.value.email = String(applicant.email ?? '');
}

async function hireApplicant(): Promise<void> {
    if (selectedApplicant.value === null) {
        return;
    }

    hireBusy.value = true;
    hireError.value = '';
    hireStatus.value = '';

    try {
        await postJson(
            ApplicantHireController.url(Number(selectedApplicant.value.id)),
            hireForm.value,
        );
        hireStatus.value = 'Applicant hired successfully.';
        window.location.reload();
    } catch (error) {
        hireError.value =
            error instanceof Error
                ? error.message
                : 'Unable to hire applicant.';
    } finally {
        hireBusy.value = false;
    }
}
</script>

<template>
    <Head title="Employees" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <Tabs v-model="activeTab" class="space-y-4">
            <Card class="border-border/70 shadow-sm">
                <CardHeader
                    class="gap-5 lg:flex-row lg:items-end lg:justify-between"
                >
                    <Heading
                        title="Employee Management"
                        description="People records, structure, hiring, and reviews in one clean workspace."
                    />

                    <div
                        class="flex flex-col gap-3 md:flex-row md:items-center"
                    >
                        <TabsList
                            class="grid w-full grid-cols-2 md:grid-cols-4"
                        >
                            <TabsTrigger
                                v-for="tab in tabs"
                                :key="tab.key"
                                :value="tab.key"
                            >
                                {{ tab.label }}
                            </TabsTrigger>
                        </TabsList>

                        <form class="flex gap-2" @submit.prevent="runSearch">
                            <Input
                                v-model="search"
                                type="search"
                                placeholder="Search employees"
                            />
                            <Button type="submit" variant="outline"
                                >Search</Button
                            >
                        </form>
                    </div>
                </CardHeader>
            </Card>

            <TabsContent value="directory" class="space-y-4">
                <section class="grid gap-4 xl:grid-cols-[1.3fr_0.9fr]">
                    <Card class="border-border/70 shadow-sm">
                        <CardHeader>
                            <Heading
                                variant="small"
                                title="Employee Directory"
                                description="Searchable records with a simple route into each employee profile."
                            />
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow class="hover:bg-transparent">
                                        <TableHead>Employee</TableHead>
                                        <TableHead>Department</TableHead>
                                        <TableHead>Position</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Profile</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow
                                        v-for="employee in props.employees.data"
                                        :key="String(employee.id)"
                                    >
                                        <TableCell class="whitespace-normal">
                                            <p class="font-medium">
                                                {{ employee.full_name }}
                                            </p>
                                            <p class="text-muted-foreground">
                                                {{ employee.employee_number }}
                                            </p>
                                        </TableCell>
                                        <TableCell>{{
                                            employee.department ?? 'Unassigned'
                                        }}</TableCell>
                                        <TableCell>{{
                                            employee.position ?? 'Unassigned'
                                        }}</TableCell>
                                        <TableCell>
                                            <HrisStatusBadge
                                                :status="
                                                    employee.employment_status
                                                "
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <Button
                                                as-child
                                                size="sm"
                                                variant="outline"
                                            >
                                                <Link
                                                    :href="
                                                        employeeShow(
                                                            Number(employee.id),
                                                        )
                                                    "
                                                >
                                                    Open
                                                </Link>
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                    <TableRow
                                        v-if="props.employees.data.length === 0"
                                    >
                                        <TableCell
                                            colspan="5"
                                            class="h-24 text-center text-muted-foreground"
                                        >
                                            No employee records match the
                                            current search.
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
                                title="Add Employee"
                                description="Capture only the essentials for onboarding."
                            />
                        </CardHeader>

                        <CardContent>
                            <Form
                                v-bind="EmployeeController.store.form()"
                                class="space-y-4"
                                reset-on-success
                                #default="{ errors, processing }"
                            >
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="grid gap-2">
                                        <Label for="first_name"
                                            >First name</Label
                                        >
                                        <Input
                                            id="first_name"
                                            name="first_name"
                                        />
                                        <InputError
                                            :message="errors.first_name"
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="last_name">Last name</Label>
                                        <Input
                                            id="last_name"
                                            name="last_name"
                                        />
                                        <InputError
                                            :message="errors.last_name"
                                        />
                                    </div>
                                </div>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="grid gap-2">
                                        <Label for="email">Email</Label>
                                        <Input
                                            id="email"
                                            name="email"
                                            type="email"
                                        />
                                        <InputError :message="errors.email" />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="role_id">Role</Label>
                                        <HrisSelectField
                                            id="role_id"
                                            name="role_id"
                                            placeholder="Select role"
                                            :options="props.roles"
                                            option-value="id"
                                            option-label="name"
                                        />
                                        <InputError :message="errors.role_id" />
                                    </div>
                                </div>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="grid gap-2">
                                        <Label for="department_id"
                                            >Department</Label
                                        >
                                        <HrisSelectField
                                            id="department_id"
                                            name="department_id"
                                            empty-label="Optional"
                                            :options="props.departments"
                                            option-value="id"
                                            option-label="name"
                                            default-value=""
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="position_id"
                                            >Position</Label
                                        >
                                        <HrisSelectField
                                            id="position_id"
                                            name="position_id"
                                            empty-label="Optional"
                                            :options="props.positions"
                                            option-value="id"
                                            option-label="name"
                                            default-value=""
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
                                            placeholder="Select site"
                                            :options="props.attendanceLocations"
                                            option-value="id"
                                            option-label="name"
                                        />
                                        <InputError
                                            :message="
                                                errors.attendance_location_id
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
                                            placeholder="Select schedule"
                                            :options="props.workSchedules"
                                            option-value="id"
                                            option-label="name"
                                        />
                                        <InputError
                                            :message="errors.work_schedule_id"
                                        />
                                    </div>
                                </div>

                                <div class="grid gap-4 md:grid-cols-3">
                                    <div class="grid gap-2">
                                        <Label for="hire_date">Hire date</Label>
                                        <Input
                                            id="hire_date"
                                            name="hire_date"
                                            type="date"
                                        />
                                        <InputError
                                            :message="errors.hire_date"
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="employment_status"
                                            >Status</Label
                                        >
                                        <HrisSelectField
                                            id="employment_status"
                                            name="employment_status"
                                            :options="employmentStatusOptions"
                                            default-value="active"
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="employment_type"
                                            >Type</Label
                                        >
                                        <HrisSelectField
                                            id="employment_type"
                                            name="employment_type"
                                            :options="employmentTypeOptions"
                                            default-value="regular"
                                        />
                                    </div>
                                </div>

                                <div class="grid gap-2">
                                    <Label for="base_salary"
                                        >Monthly salary</Label
                                    >
                                    <Input
                                        id="base_salary"
                                        name="base_salary"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                    />
                                    <InputError :message="errors.base_salary" />
                                </div>

                                <Button :disabled="processing">
                                    {{
                                        processing
                                            ? 'Saving...'
                                            : 'Create employee'
                                    }}
                                </Button>
                            </Form>
                        </CardContent>
                    </Card>
                </section>
            </TabsContent>

            <TabsContent value="organization" class="space-y-4">
                <section class="grid gap-4 xl:grid-cols-[0.8fr_1.2fr]">
                    <Card class="border-border/70 shadow-sm">
                        <CardHeader>
                            <Heading
                                variant="small"
                                title="Departments"
                                description="A simple hierarchy that stays easy to scan."
                            />
                        </CardHeader>

                        <CardContent>
                            <Form
                                v-bind="DepartmentController.store.form()"
                                class="space-y-4"
                                reset-on-success
                                #default="{ errors, processing }"
                            >
                                <div class="grid gap-2">
                                    <Label for="department_name"
                                        >Department name</Label
                                    >
                                    <Input id="department_name" name="name" />
                                    <InputError :message="errors.name" />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="department_code">Code</Label>
                                    <Input id="department_code" name="code" />
                                    <InputError :message="errors.code" />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="parent_id"
                                        >Parent department</Label
                                    >
                                    <HrisSelectField
                                        id="parent_id"
                                        name="parent_id"
                                        empty-label="None"
                                        :options="props.departments"
                                        option-value="id"
                                        option-label="name"
                                        default-value=""
                                    />
                                </div>
                                <Button :disabled="processing">
                                    {{
                                        processing
                                            ? 'Saving...'
                                            : 'Add department'
                                    }}
                                </Button>
                            </Form>
                        </CardContent>
                    </Card>

                    <Card class="border-border/70 shadow-sm">
                        <CardHeader>
                            <Heading
                                variant="small"
                                title="Structure Snapshot"
                                description="Departments and positions shown in quiet lists instead of a complex chart."
                            />
                        </CardHeader>

                        <CardContent class="space-y-6">
                            <div class="grid gap-4 md:grid-cols-2">
                                <Card
                                    v-for="department in props.departments"
                                    :key="String(department.id)"
                                    class="gap-3 border-border/60 shadow-none"
                                >
                                    <CardHeader class="gap-1.5">
                                        <CardTitle class="text-base">{{
                                            department.name
                                        }}</CardTitle>
                                        <CardDescription>{{
                                            department.code
                                        }}</CardDescription>
                                    </CardHeader>
                                </Card>
                            </div>

                            <Table>
                                <TableHeader>
                                    <TableRow class="hover:bg-transparent">
                                        <TableHead>Position</TableHead>
                                        <TableHead>Department</TableHead>
                                        <TableHead>Code</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow
                                        v-for="position in props.positions"
                                        :key="String(position.id)"
                                    >
                                        <TableCell>{{
                                            position.name
                                        }}</TableCell>
                                        <TableCell>
                                            {{
                                                props.departments.find(
                                                    (department) =>
                                                        Number(
                                                            department.id,
                                                        ) ===
                                                        Number(
                                                            position.department_id,
                                                        ),
                                                )?.name ?? 'Unassigned'
                                            }}
                                        </TableCell>
                                        <TableCell>{{
                                            position.code ?? 'N/A'
                                        }}</TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                </section>
            </TabsContent>

            <TabsContent value="recruitment" class="space-y-4">
                <section class="grid gap-4 xl:grid-cols-[1.1fr_0.9fr]">
                    <div class="space-y-4">
                        <Card class="border-border/70 shadow-sm">
                            <CardHeader>
                                <Heading
                                    variant="small"
                                    title="Job Posting"
                                    description="Keep openings tied to the org structure."
                                />
                            </CardHeader>

                            <CardContent>
                                <Form
                                    v-bind="JobPostingController.store.form()"
                                    class="grid gap-4 md:grid-cols-2"
                                    reset-on-success
                                    #default="{ processing }"
                                >
                                    <div class="grid gap-2 md:col-span-2">
                                        <Label for="title">Title</Label>
                                        <Input id="title" name="title" />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="job_department_id"
                                            >Department</Label
                                        >
                                        <HrisSelectField
                                            id="job_department_id"
                                            name="department_id"
                                            empty-label="Optional"
                                            :options="props.departments"
                                            option-value="id"
                                            option-label="name"
                                            default-value=""
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="job_status">Status</Label>
                                        <HrisSelectField
                                            id="job_status"
                                            name="status"
                                            :options="jobPostingStatusOptions"
                                            default-value="open"
                                        />
                                    </div>
                                    <div class="md:col-span-2">
                                        <Button :disabled="processing">
                                            {{
                                                processing
                                                    ? 'Saving...'
                                                    : 'Create posting'
                                            }}
                                        </Button>
                                    </div>
                                </Form>

                                <div class="mt-5 space-y-3">
                                    <Card
                                        v-for="jobPosting in props.jobPostings"
                                        :key="String(jobPosting.id)"
                                        class="gap-3 border-border/60 shadow-none"
                                    >
                                        <CardHeader
                                            class="flex flex-row items-start justify-between gap-3"
                                        >
                                            <div class="space-y-1">
                                                <CardTitle class="text-base">{{
                                                    jobPosting.title
                                                }}</CardTitle>
                                                <CardDescription>
                                                    {{
                                                        jobPosting.department ??
                                                        'No department'
                                                    }}
                                                </CardDescription>
                                            </div>
                                            <HrisStatusBadge
                                                :status="jobPosting.status"
                                            />
                                        </CardHeader>
                                    </Card>
                                    <p
                                        v-if="props.jobPostings.length === 0"
                                        class="rounded-xl border border-dashed border-border/60 px-4 py-6 text-sm text-muted-foreground"
                                    >
                                        No job postings have been created yet.
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        <Card class="border-border/70 shadow-sm">
                            <CardHeader>
                                <Heading
                                    variant="small"
                                    title="Applicants"
                                    description="Add applicants and keep interviews lightweight."
                                />
                            </CardHeader>

                            <CardContent class="space-y-6">
                                <Form
                                    v-bind="ApplicantController.store.form()"
                                    class="grid gap-4 md:grid-cols-2"
                                    reset-on-success
                                    #default="{ errors, processing }"
                                >
                                    <div class="grid gap-2 md:col-span-2">
                                        <Label for="full_name"
                                            >Applicant name</Label
                                        >
                                        <Input
                                            id="full_name"
                                            name="full_name"
                                        />
                                        <InputError
                                            :message="errors.full_name"
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="applicant_email"
                                            >Email</Label
                                        >
                                        <Input
                                            id="applicant_email"
                                            name="email"
                                            type="email"
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="scheduled_at"
                                            >Interview schedule</Label
                                        >
                                        <Input
                                            id="scheduled_at"
                                            name="scheduled_at"
                                            type="datetime-local"
                                        />
                                    </div>
                                    <div class="grid gap-2 md:col-span-2">
                                        <Label for="resume">Resume</Label>
                                        <Input
                                            id="resume"
                                            name="resume"
                                            type="file"
                                        />
                                    </div>
                                    <div class="md:col-span-2">
                                        <Button :disabled="processing">
                                            {{
                                                processing
                                                    ? 'Saving...'
                                                    : 'Add applicant'
                                            }}
                                        </Button>
                                    </div>
                                </Form>

                                <Table>
                                    <TableHeader>
                                        <TableRow class="hover:bg-transparent">
                                            <TableHead>Applicant</TableHead>
                                            <TableHead>Posting</TableHead>
                                            <TableHead>Stage</TableHead>
                                            <TableHead>Hire</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow
                                            v-for="applicant in props.applicants"
                                            :key="String(applicant.id)"
                                        >
                                            <TableCell
                                                class="whitespace-normal"
                                            >
                                                <p class="font-medium">
                                                    {{ applicant.full_name }}
                                                </p>
                                                <p
                                                    class="text-muted-foreground"
                                                >
                                                    {{
                                                        applicant.email ??
                                                        'No email'
                                                    }}
                                                </p>
                                            </TableCell>
                                            <TableCell>{{
                                                applicant.job_posting ??
                                                'General pool'
                                            }}</TableCell>
                                            <TableCell>
                                                <HrisStatusBadge
                                                    :status="applicant.stage"
                                                />
                                            </TableCell>
                                            <TableCell>
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    @click="
                                                        selectApplicant(
                                                            applicant,
                                                        )
                                                    "
                                                >
                                                    Prepare
                                                </Button>
                                            </TableCell>
                                        </TableRow>
                                        <TableRow
                                            v-if="props.applicants.length === 0"
                                        >
                                            <TableCell
                                                colspan="4"
                                                class="h-24 text-center text-muted-foreground"
                                            >
                                                No applicants are in the
                                                pipeline yet.
                                            </TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </CardContent>
                        </Card>
                    </div>

                    <div class="space-y-4">
                        <Card class="border-border/70 shadow-sm">
                            <CardHeader>
                                <Heading
                                    variant="small"
                                    title="Hire Applicant"
                                    description="Convert a selected applicant into an employee record."
                                />
                            </CardHeader>

                            <CardContent class="space-y-4">
                                <div v-if="selectedApplicant" class="space-y-4">
                                    <Card class="bg-muted/40 shadow-none">
                                        <CardHeader class="gap-1.5">
                                            <CardTitle class="text-base">{{
                                                selectedApplicant.full_name
                                            }}</CardTitle>
                                            <CardDescription>
                                                {{
                                                    selectedApplicant.job_posting ??
                                                    'General pool'
                                                }}
                                            </CardDescription>
                                        </CardHeader>
                                    </Card>

                                    <div class="grid gap-4 md:grid-cols-2">
                                        <div class="grid gap-2">
                                            <Label>First name</Label
                                            ><Input
                                                v-model="hireForm.first_name"
                                            />
                                        </div>
                                        <div class="grid gap-2">
                                            <Label>Last name</Label
                                            ><Input
                                                v-model="hireForm.last_name"
                                            />
                                        </div>
                                    </div>
                                    <div class="grid gap-4 md:grid-cols-2">
                                        <div class="grid gap-2">
                                            <Label>Email</Label
                                            ><Input
                                                v-model="hireForm.email"
                                                type="email"
                                            />
                                        </div>
                                        <div class="grid gap-2">
                                            <Label>Employee number</Label
                                            ><Input
                                                v-model="
                                                    hireForm.employee_number
                                                "
                                            />
                                        </div>
                                    </div>
                                    <div class="grid gap-4 md:grid-cols-2">
                                        <div class="grid gap-2">
                                            <Label>Hire date</Label
                                            ><Input
                                                v-model="hireForm.hire_date"
                                                type="date"
                                            />
                                        </div>
                                        <div class="grid gap-2">
                                            <Label>Base salary</Label
                                            ><Input
                                                v-model="hireForm.base_salary"
                                                type="number"
                                                min="0"
                                                step="0.01"
                                            />
                                        </div>
                                    </div>

                                    <Alert v-if="hireStatus">
                                        <CircleCheck class="size-4" />
                                        <AlertTitle>Applicant hired</AlertTitle>
                                        <AlertDescription>{{
                                            hireStatus
                                        }}</AlertDescription>
                                    </Alert>

                                    <Alert
                                        v-if="hireError"
                                        variant="destructive"
                                    >
                                        <TriangleAlert class="size-4" />
                                        <AlertTitle>Hiring failed</AlertTitle>
                                        <AlertDescription>{{
                                            hireError
                                        }}</AlertDescription>
                                    </Alert>

                                    <Button
                                        :disabled="hireBusy"
                                        @click="hireApplicant"
                                    >
                                        {{
                                            hireBusy
                                                ? 'Hiring...'
                                                : 'Hire applicant'
                                        }}
                                    </Button>
                                </div>
                                <p
                                    v-else
                                    class="rounded-xl border border-dashed border-border/60 px-4 py-6 text-sm text-muted-foreground"
                                >
                                    No applicants are ready to hire right now.
                                </p>
                            </CardContent>
                        </Card>

                        <Card class="border-border/70 shadow-sm">
                            <CardHeader>
                                <Heading
                                    variant="small"
                                    title="Upcoming Interviews"
                                    description="A compact queue of scheduled conversations."
                                />
                            </CardHeader>

                            <CardContent class="space-y-3 text-sm">
                                <Card
                                    v-for="interview in props.upcomingInterviews"
                                    :key="String(interview.id)"
                                    class="gap-3 border-border/60 shadow-none"
                                >
                                    <CardHeader
                                        class="flex flex-row items-start justify-between gap-3"
                                    >
                                        <div>
                                            <CardTitle class="text-base">{{
                                                interview.applicant ??
                                                'Applicant'
                                            }}</CardTitle>
                                            <CardDescription>{{
                                                interview.interviewer ?? 'TBD'
                                            }}</CardDescription>
                                        </div>
                                        <HrisStatusBadge
                                            :status="interview.status"
                                        />
                                    </CardHeader>
                                    <CardContent>{{
                                        interview.scheduled_at ??
                                        'Schedule pending'
                                    }}</CardContent>
                                </Card>
                                <p
                                    v-if="props.upcomingInterviews.length === 0"
                                    class="rounded-xl border border-dashed border-border/60 px-4 py-6 text-muted-foreground"
                                >
                                    No interviews are scheduled yet.
                                </p>
                            </CardContent>
                        </Card>
                    </div>
                </section>
            </TabsContent>

            <TabsContent value="performance" class="space-y-4">
                <section class="grid gap-4 xl:grid-cols-[0.9fr_1.1fr]">
                    <Card class="border-border/70 shadow-sm">
                        <CardHeader>
                            <Heading
                                variant="small"
                                title="New Review"
                                description="Simple KPI scoring with comments."
                            />
                        </CardHeader>

                        <CardContent>
                            <Form
                                v-bind="
                                    PerformanceReviewController.store.form()
                                "
                                class="space-y-4"
                                reset-on-success
                                #default="{ processing }"
                            >
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="grid gap-2">
                                        <Label for="performance_review_cycle_id"
                                            >Review cycle</Label
                                        >
                                        <HrisSelectField
                                            id="performance_review_cycle_id"
                                            name="performance_review_cycle_id"
                                            placeholder="Select cycle"
                                            :options="props.reviewCycles"
                                            option-value="id"
                                            option-label="name"
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="employee_id"
                                            >Employee</Label
                                        >
                                        <HrisSelectField
                                            id="employee_id"
                                            name="employee_id"
                                            placeholder="Select employee"
                                            :options="props.employeeOptions"
                                            option-value="id"
                                            option-label="full_name"
                                        />
                                    </div>
                                </div>

                                <Card
                                    v-for="(kpi, index) in performanceKpis"
                                    :key="kpi"
                                    class="border-border/60 shadow-none"
                                >
                                    <CardHeader class="gap-3">
                                        <CardTitle class="text-base">{{
                                            kpi
                                        }}</CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        <div
                                            class="grid gap-4 md:grid-cols-[1fr_140px]"
                                        >
                                            <Input
                                                :name="`kpis[${index}][name]`"
                                                :default-value="kpi"
                                            />
                                            <HrisSelectField
                                                :name="`kpis[${index}][score]`"
                                                :options="
                                                    performanceScoreOptions
                                                "
                                                default-value="1"
                                            />
                                        </div>
                                    </CardContent>
                                </Card>

                                <Button :disabled="processing">
                                    {{
                                        processing
                                            ? 'Submitting...'
                                            : 'Submit review'
                                    }}
                                </Button>
                            </Form>
                        </CardContent>
                    </Card>

                    <Card class="border-border/70 shadow-sm">
                        <CardHeader>
                            <Heading
                                variant="small"
                                title="Recent Reviews"
                                description="Latest evaluation outcomes at a glance."
                            />
                        </CardHeader>

                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow class="hover:bg-transparent">
                                        <TableHead>Employee</TableHead>
                                        <TableHead>Reviewer</TableHead>
                                        <TableHead>Score</TableHead>
                                        <TableHead>Status</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow
                                        v-for="review in props.performanceReviews"
                                        :key="String(review.id)"
                                    >
                                        <TableCell>{{
                                            review.employee ?? 'Employee'
                                        }}</TableCell>
                                        <TableCell>{{
                                            review.reviewer ?? 'Reviewer'
                                        }}</TableCell>
                                        <TableCell>{{
                                            review.overall_score ?? 'N/A'
                                        }}</TableCell>
                                        <TableCell>
                                            <HrisStatusBadge
                                                :status="review.status"
                                            />
                                        </TableCell>
                                    </TableRow>
                                    <TableRow
                                        v-if="
                                            props.performanceReviews.length ===
                                            0
                                        "
                                    >
                                        <TableCell
                                            colspan="4"
                                            class="h-24 text-center text-muted-foreground"
                                        >
                                            No performance reviews have been
                                            submitted yet.
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                </section>
            </TabsContent>
        </Tabs>
    </div>
</template>
