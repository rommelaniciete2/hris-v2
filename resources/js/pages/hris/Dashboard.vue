<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import HrisSectionCard from '@/components/HrisSectionCard.vue';
import HrisStatCard from '@/components/HrisStatCard.vue';
import HrisStatusBadge from '@/components/HrisStatusBadge.vue';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { formatHrisAmount, formatHrisLabel } from '@/lib/hris';
import { dashboard } from '@/routes';
import { index as employeesIndex } from '@/routes/hris/employees';
import { index as payrollIndex } from '@/routes/hris/payroll';
import { index as reportsIndex } from '@/routes/hris/reports';

const props = defineProps<{
    cards: Record<string, number>;
    attendance: Array<Record<string, unknown>>;
    payrolls: Array<Record<string, unknown>>;
    reviews: Array<Record<string, unknown>>;
    interviews: Array<Record<string, unknown>>;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

const cardMeta: Record<string, string> = {
    employees: 'Active employee records',
    present_today: 'Attendance logs created today',
    pending_leaves: 'Requests waiting for review',
    open_recruitment: 'Applicants not yet closed out',
};
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <Heading
            title="HRIS Overview"
            description="A calm, simple view of staffing, attendance, approvals, and payroll activity."
        />

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <HrisStatCard
                v-for="(value, key) in props.cards"
                :key="key"
                :title="formatHrisLabel(key)"
                :value="value"
                :description="cardMeta[key] ?? 'Current HRIS snapshot'"
                badge="Live"
            />
        </section>

        <section class="grid gap-4 xl:grid-cols-[1.3fr_1fr]">
            <HrisSectionCard
                title="Recent Attendance"
                description="Latest employee time logs."
            >
                <template #actions>
                    <Button as-child size="sm" variant="outline">
                        <Link :href="reportsIndex()">View reports</Link>
                    </Button>
                </template>

                <Table>
                    <TableHeader>
                        <TableRow class="hover:bg-transparent">
                            <TableHead>Date</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Late</TableHead>
                            <TableHead>Overtime</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="item in props.attendance"
                            :key="String(item.id)"
                        >
                            <TableCell>{{ item.work_date }}</TableCell>
                            <TableCell>
                                <HrisStatusBadge :status="item.status" />
                            </TableCell>
                            <TableCell>
                                {{ item.late_minutes ?? 0 }} min
                            </TableCell>
                            <TableCell>
                                {{ item.overtime_minutes ?? 0 }} min
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="props.attendance.length === 0">
                            <TableCell
                                colspan="4"
                                class="h-24 text-center text-muted-foreground"
                            >
                                No attendance logs are available yet.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </HrisSectionCard>

            <div class="space-y-4">
                <HrisSectionCard
                    title="Payroll Activity"
                    description="Most recent payroll outputs."
                    content-class="space-y-3 text-sm"
                >
                    <template #actions>
                        <Button as-child size="sm" variant="outline">
                            <Link :href="payrollIndex()">Open payroll</Link>
                        </Button>
                    </template>

                    <article
                        v-for="item in props.payrolls.slice(0, 4)"
                        :key="String(item.id)"
                        class="rounded-xl border border-border/60 p-3"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-medium">
                                    {{ item.employee }}
                                </p>
                                <p class="text-muted-foreground">
                                    {{ item.period_start }} to
                                    {{ item.period_end }}
                                </p>
                            </div>
                            <HrisStatusBadge
                                :status="item.status ?? 'processed'"
                            />
                        </div>
                        <p class="mt-2 text-foreground">
                            Net: {{ formatHrisAmount(item.net_pay) }}
                        </p>
                    </article>
                    <p
                        v-if="props.payrolls.length === 0"
                        class="rounded-xl border border-dashed border-border/60 px-4 py-6 text-sm text-muted-foreground"
                    >
                        No payroll activity has been generated yet.
                    </p>
                </HrisSectionCard>

                <HrisSectionCard
                    title="Upcoming Interviews"
                    description="Scheduled recruitment touchpoints."
                    content-class="space-y-3 text-sm"
                >
                    <article
                        v-for="item in props.interviews.slice(0, 4)"
                        :key="String(item.id)"
                        class="rounded-xl border border-border/60 p-3"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-medium">
                                    {{ item.applicant }}
                                </p>
                                <p class="text-muted-foreground">
                                    {{ item.interviewer }}
                                </p>
                            </div>
                            <HrisStatusBadge :status="item.status" />
                        </div>
                        <p class="mt-2 text-foreground">
                            {{ item.scheduled_at }}
                        </p>
                    </article>
                    <p
                        v-if="props.interviews.length === 0"
                        class="rounded-xl border border-dashed border-border/60 px-4 py-6 text-sm text-muted-foreground"
                    >
                        No interviews are scheduled right now.
                    </p>
                </HrisSectionCard>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2">
            <HrisSectionCard
                title="Review Activity"
                description="Latest performance submissions."
                content-class="space-y-3 text-sm"
            >
                <article
                    v-for="item in props.reviews.slice(0, 4)"
                    :key="String(item.id)"
                    class="rounded-xl border border-border/60 p-3"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-medium">{{ item.employee }}</p>
                            <p class="text-muted-foreground">
                                {{ item.reviewer }}
                            </p>
                        </div>
                        <HrisStatusBadge :status="item.status" />
                    </div>
                    <p class="mt-2">Overall score: {{ item.overall_score }}</p>
                </article>
                <p
                    v-if="props.reviews.length === 0"
                    class="rounded-xl border border-dashed border-border/60 px-4 py-6 text-sm text-muted-foreground"
                >
                    No review activity has been submitted yet.
                </p>
            </HrisSectionCard>

            <HrisSectionCard
                title="Quick Access"
                description="Jump into the busiest work areas."
                content-class="grid gap-3 sm:grid-cols-3"
            >
                <Button as-child class="justify-start" variant="outline">
                    <Link :href="employeesIndex()">Employees</Link>
                </Button>
                <Button as-child class="justify-start" variant="outline">
                    <Link :href="payrollIndex()">Payroll</Link>
                </Button>
                <Button as-child class="justify-start" variant="outline">
                    <Link :href="reportsIndex()">Reports</Link>
                </Button>
            </HrisSectionCard>
        </section>
    </div>
</template>
