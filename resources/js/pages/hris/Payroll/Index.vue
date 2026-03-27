<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { CircleCheck, TriangleAlert } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import PayrollRunController from '@/actions/App/Http/Controllers/Api/V1/PayrollRunController';
import Heading from '@/components/Heading.vue';
import HrisSectionCard from '@/components/HrisSectionCard.vue';
import HrisStatCard from '@/components/HrisStatCard.vue';
import HrisStatusBadge from '@/components/HrisStatusBadge.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
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
import { formatHrisAmount, sumHrisAmounts } from '@/lib/hris';
import { postJson } from '@/lib/http';
import {
    index as payrollIndex,
    show as payrollShow,
} from '@/routes/hris/payroll';

const props = defineProps<{
    payrolls: {
        data: Array<{
            id: number;
            employee: string;
            period_start: string;
            period_end: string;
            pay_date: string;
            status: string;
            gross_pay: number | string;
            total_deductions: number | string;
            net_pay: number | string;
        }>;
    };
    defaultRun: {
        period_start: string;
        period_end: string;
        pay_date: string;
    };
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Payroll',
                href: payrollIndex(),
            },
        ],
    },
});

const runForm = ref({ ...props.defaultRun });
const runError = ref('');
const runStatus = ref('');
const processing = ref(false);
const netTotal = computed(() =>
    formatHrisAmount(
        sumHrisAmounts(props.payrolls.data.map((payroll) => payroll.net_pay)),
    ),
);
const deductionTotal = computed(() =>
    formatHrisAmount(
        sumHrisAmounts(
            props.payrolls.data.map((payroll) => payroll.total_deductions),
        ),
    ),
);

async function runPayroll(): Promise<void> {
    processing.value = true;
    runError.value = '';
    runStatus.value = '';

    try {
        await postJson(PayrollRunController.url(), runForm.value);
        runStatus.value = 'Payroll run completed successfully.';
        window.location.reload();
    } catch (error) {
        runError.value =
            error instanceof Error
                ? error.message
                : 'Unable to process payroll.';
    } finally {
        processing.value = false;
    }
}
</script>

<template>
    <Head title="Payroll" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <Heading
            title="Payroll"
            description="Process semi-monthly payroll, review results, and open printable payslips."
        />

        <HrisSectionCard
            title="Run Payroll"
            description="Generate payroll rows for the selected cutoff period."
            content-class="space-y-5"
        >
            <div class="grid gap-4 md:grid-cols-3">
                <div class="grid gap-2">
                    <Label for="period_start">Period start</Label>
                    <Input
                        id="period_start"
                        v-model="runForm.period_start"
                        type="date"
                    />
                </div>
                <div class="grid gap-2">
                    <Label for="period_end">Period end</Label>
                    <Input
                        id="period_end"
                        v-model="runForm.period_end"
                        type="date"
                    />
                </div>
                <div class="grid gap-2">
                    <Label for="pay_date">Pay date</Label>
                    <Input
                        id="pay_date"
                        v-model="runForm.pay_date"
                        type="date"
                    />
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-4">
                <Button :disabled="processing" @click="runPayroll">
                    {{ processing ? 'Running payroll...' : 'Run payroll' }}
                </Button>
            </div>

            <Alert v-if="runStatus">
                <CircleCheck class="size-4" />
                <AlertTitle>Payroll completed</AlertTitle>
                <AlertDescription>{{ runStatus }}</AlertDescription>
            </Alert>

            <Alert v-if="runError" variant="destructive">
                <TriangleAlert class="size-4" />
                <AlertTitle>Payroll failed</AlertTitle>
                <AlertDescription>{{ runError }}</AlertDescription>
            </Alert>
        </HrisSectionCard>

        <section class="grid gap-4 md:grid-cols-3">
            <HrisStatCard
                title="Processed rows"
                :value="props.payrolls.data.length"
                badge="Latest batch"
            />
            <HrisStatCard
                title="Latest net total"
                :value="netTotal"
                badge="Currency total"
            />
            <HrisStatCard
                title="Latest deduction total"
                :value="deductionTotal"
                badge="With deductions"
            />
        </section>

        <HrisSectionCard
            title="Payroll History"
            description="Open a payslip to review the breakdown."
        >
            <Table>
                <TableHeader>
                    <TableRow class="hover:bg-transparent">
                        <TableHead>Employee</TableHead>
                        <TableHead>Period</TableHead>
                        <TableHead>Net Pay</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Payslip</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="payroll in props.payrolls.data"
                        :key="payroll.id"
                    >
                        <TableCell>{{ payroll.employee }}</TableCell>
                        <TableCell>
                            {{ payroll.period_start }} to
                            {{ payroll.period_end }}
                        </TableCell>
                        <TableCell>{{
                            formatHrisAmount(payroll.net_pay)
                        }}</TableCell>
                        <TableCell>
                            <HrisStatusBadge :status="payroll.status" />
                        </TableCell>
                        <TableCell>
                            <Button as-child size="sm" variant="outline">
                                <Link :href="payrollShow(payroll.id)">
                                    Open
                                </Link>
                            </Button>
                        </TableCell>
                    </TableRow>
                    <TableRow v-if="props.payrolls.data.length === 0">
                        <TableCell
                            colspan="5"
                            class="h-24 text-center text-muted-foreground"
                        >
                            No payroll history is available yet.
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </HrisSectionCard>
    </div>
</template>
