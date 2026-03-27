<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import HrisSectionCard from '@/components/HrisSectionCard.vue';
import HrisStatCard from '@/components/HrisStatCard.vue';
import HrisStatusBadge from '@/components/HrisStatusBadge.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { formatHrisAmount, formatHrisLabel } from '@/lib/hris';
import { index as payrollIndex } from '@/routes/hris/payroll';

const props = defineProps<{
    payroll: {
        id: number;
        employee: string;
        employee_number: string;
        period_start: string;
        period_end: string;
        pay_date: string;
        status: string;
        earnings_breakdown: Record<string, number | string>;
        deductions_breakdown: Record<string, number | string>;
        gross_pay: number | string;
        total_deductions: number | string;
        net_pay: number | string;
    };
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Payslip',
                href: payrollIndex(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Payslip" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6 print:p-0">
        <HrisSectionCard
            title="Payslip"
            description="Printable payroll summary for the selected employee and cutoff."
            content-class="grid gap-4 md:grid-cols-2"
        >
            <template #actions>
                <HrisStatusBadge :status="props.payroll.status" />
            </template>

            <Card class="bg-muted/40 shadow-none">
                <CardHeader class="gap-1.5">
                    <CardDescription>Employee</CardDescription>
                    <CardTitle class="text-lg">
                        {{ props.payroll.employee }}
                    </CardTitle>
                </CardHeader>
                <CardContent class="text-sm text-muted-foreground">
                    {{ props.payroll.employee_number }}
                </CardContent>
            </Card>
            <Card class="bg-muted/40 shadow-none">
                <CardHeader class="gap-1.5">
                    <CardDescription>Payroll period</CardDescription>
                    <CardTitle class="text-lg">
                        {{ props.payroll.period_start }} to
                        {{ props.payroll.period_end }}
                    </CardTitle>
                </CardHeader>
                <CardContent class="text-sm text-muted-foreground">
                    Pay date: {{ props.payroll.pay_date }}
                </CardContent>
            </Card>
        </HrisSectionCard>

        <section class="grid gap-4 md:grid-cols-2">
            <HrisSectionCard
                title="Earnings"
                description="All earnings included in this cutoff."
                content-class="space-y-3 text-sm"
            >
                <div
                    v-for="(value, key) in props.payroll.earnings_breakdown"
                    :key="key"
                    class="flex items-center justify-between gap-4 rounded-xl border border-border/60 px-4 py-3"
                >
                    <span>{{ formatHrisLabel(key) }}</span>
                    <span class="font-medium">{{
                        formatHrisAmount(value)
                    }}</span>
                </div>
                <p
                    v-if="
                        Object.keys(props.payroll.earnings_breakdown).length ===
                        0
                    "
                    class="rounded-xl border border-dashed border-border/60 px-4 py-6 text-muted-foreground"
                >
                    No earnings breakdown was generated for this cutoff.
                </p>
            </HrisSectionCard>

            <HrisSectionCard
                title="Deductions"
                description="Statutory and attendance-based deductions."
                content-class="space-y-3 text-sm"
            >
                <div
                    v-for="(value, key) in props.payroll.deductions_breakdown"
                    :key="key"
                    class="flex items-center justify-between gap-4 rounded-xl border border-border/60 px-4 py-3"
                >
                    <span>{{ formatHrisLabel(key) }}</span>
                    <span class="font-medium">{{
                        formatHrisAmount(value)
                    }}</span>
                </div>
                <p
                    v-if="
                        Object.keys(props.payroll.deductions_breakdown)
                            .length === 0
                    "
                    class="rounded-xl border border-dashed border-border/60 px-4 py-6 text-muted-foreground"
                >
                    No deductions were applied for this cutoff.
                </p>
            </HrisSectionCard>
        </section>

        <section class="grid gap-4 md:grid-cols-3">
            <HrisStatCard
                title="Gross pay"
                :value="formatHrisAmount(props.payroll.gross_pay)"
                badge="Before deductions"
            />
            <HrisStatCard
                title="Total deductions"
                :value="formatHrisAmount(props.payroll.total_deductions)"
                badge="All deductions"
            />
            <HrisStatCard
                title="Net pay"
                :value="formatHrisAmount(props.payroll.net_pay)"
                badge="Take home"
                badge-variant="default"
            />
        </section>
    </div>
</template>
