<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ReportExportController from '@/actions/App/Http/Controllers/Api/V1/ReportExportController';
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
                title: 'Reports',
                href: reportsIndex(),
            },
        ],
    },
});

function exportUrl(type: string): string {
    return ReportExportController.url({
        query: {
            type,
        },
    });
}
</script>

<template>
    <Head title="Reports" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <Heading
            title="Reports"
            description="Summary cards and practical export-ready tables instead of heavy dashboards."
        />

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <HrisStatCard
                v-for="(value, key) in props.cards"
                :key="key"
                :title="formatHrisLabel(key)"
                :value="value"
                badge="Snapshot"
            />
        </section>

        <section class="grid gap-4 xl:grid-cols-2">
            <HrisSectionCard
                title="Attendance Report"
                description="Latest attendance entries ready for CSV export."
            >
                <template #actions>
                    <Button as-child size="sm" variant="outline">
                        <a :href="exportUrl('attendance')">Export CSV</a>
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
                            <TableCell>{{ item.late_minutes }} min</TableCell>
                            <TableCell>
                                {{ item.overtime_minutes }} min
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="props.attendance.length === 0">
                            <TableCell
                                colspan="4"
                                class="h-24 text-center text-muted-foreground"
                            >
                                No attendance rows are ready to export yet.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </HrisSectionCard>

            <HrisSectionCard
                title="Payroll Report"
                description="Recent payroll rows and their net totals."
            >
                <template #actions>
                    <Button as-child size="sm" variant="outline">
                        <a :href="exportUrl('payroll')">Export CSV</a>
                    </Button>
                </template>

                <Table>
                    <TableHeader>
                        <TableRow class="hover:bg-transparent">
                            <TableHead>Employee</TableHead>
                            <TableHead>Period</TableHead>
                            <TableHead>Net Pay</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="item in props.payrolls"
                            :key="String(item.id)"
                        >
                            <TableCell>{{ item.employee }}</TableCell>
                            <TableCell>
                                {{ item.period_start }} to
                                {{ item.period_end }}
                            </TableCell>
                            <TableCell>{{
                                formatHrisAmount(item.net_pay)
                            }}</TableCell>
                        </TableRow>
                        <TableRow v-if="props.payrolls.length === 0">
                            <TableCell
                                colspan="3"
                                class="h-24 text-center text-muted-foreground"
                            >
                                No payroll rows are available yet.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </HrisSectionCard>
        </section>

        <section class="grid gap-4 md:grid-cols-2">
            <HrisSectionCard
                title="Performance Snapshot"
                description="Most recent submitted evaluations."
                content-class="space-y-3 text-sm"
            >
                <article
                    v-for="item in props.reviews.slice(0, 5)"
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
                    <p class="mt-2">Score: {{ item.overall_score }}</p>
                </article>
                <p
                    v-if="props.reviews.length === 0"
                    class="rounded-xl border border-dashed border-border/60 px-4 py-6 text-sm text-muted-foreground"
                >
                    No review results are available yet.
                </p>
            </HrisSectionCard>

            <HrisSectionCard
                title="Recruitment Snapshot"
                description="Upcoming interviews that still need follow-through."
                content-class="space-y-3 text-sm"
            >
                <article
                    v-for="item in props.interviews.slice(0, 5)"
                    :key="String(item.id)"
                    class="rounded-xl border border-border/60 p-3"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-medium">{{ item.applicant }}</p>
                            <p class="text-muted-foreground">
                                {{ item.interviewer }}
                            </p>
                        </div>
                        <HrisStatusBadge :status="item.status" />
                    </div>
                    <p class="mt-2">{{ item.scheduled_at }}</p>
                </article>
                <p
                    v-if="props.interviews.length === 0"
                    class="rounded-xl border border-dashed border-border/60 px-4 py-6 text-sm text-muted-foreground"
                >
                    No interviews are queued right now.
                </p>
            </HrisSectionCard>
        </section>
    </div>
</template>
