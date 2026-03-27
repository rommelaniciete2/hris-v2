<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { CircleCheck, TriangleAlert } from 'lucide-vue-next';
import { ref } from 'vue';
import LeaveApprovalController from '@/actions/App/Http/Controllers/Api/V1/LeaveApprovalController';
import LeaveController from '@/actions/App/Http/Controllers/Hris/LeaveController';
import Heading from '@/components/Heading.vue';
import HrisSectionCard from '@/components/HrisSectionCard.vue';
import HrisSelectField from '@/components/HrisSelectField.vue';
import HrisStatCard from '@/components/HrisStatCard.vue';
import HrisStatusBadge from '@/components/HrisStatusBadge.vue';
import InputError from '@/components/InputError.vue';
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
import { Textarea } from '@/components/ui/textarea';
import { postJson } from '@/lib/http';
import { index as leaveIndex } from '@/routes/hris/leave';

const props = defineProps<{
    leaveTypes: Array<{
        id: number;
        name: string;
        code: string;
        color: string;
    }>;
    balances: Array<{
        id: number;
        leave_type: string;
        allocated_days: number | string;
        used_days: number | string;
        remaining_days: number | string;
    }>;
    history: Array<Record<string, unknown>>;
    pendingApprovals: Array<Record<string, unknown>>;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Leave',
                href: leaveIndex(),
            },
        ],
    },
});

const approvalError = ref('');
const approvalStatus = ref('');
const approvalBusyId = ref<number | null>(null);

async function reviewLeave(
    id: number,
    status: 'approved' | 'rejected',
): Promise<void> {
    approvalBusyId.value = id;
    approvalError.value = '';
    approvalStatus.value = '';

    try {
        await postJson(LeaveApprovalController.url(id), { status });
        approvalStatus.value = `Leave request ${status} successfully.`;
        window.location.reload();
    } catch (error) {
        approvalError.value =
            error instanceof Error
                ? error.message
                : 'Unable to update leave request.';
    } finally {
        approvalBusyId.value = null;
    }
}
</script>

<template>
    <Head title="Leave" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <Heading
            title="Leave Management"
            description="Track balances, submit requests, and review approvals without clutter."
        />

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <HrisStatCard
                v-for="balance in props.balances"
                :key="balance.id"
                :title="String(balance.leave_type)"
                :value="balance.remaining_days"
                :description="`${balance.used_days} used of ${balance.allocated_days}`"
                badge="Remaining"
            />
            <div
                v-if="props.balances.length === 0"
                class="rounded-xl border border-dashed border-border/60 px-4 py-6 text-sm text-muted-foreground md:col-span-2 xl:col-span-4"
            >
                No leave balances have been assigned yet.
            </div>
        </section>

        <section class="grid gap-4 xl:grid-cols-[0.9fr_1.1fr]">
            <HrisSectionCard
                title="New Leave Request"
                description="Submit a simple request for HR review."
            >
                <Form
                    v-bind="LeaveController.store.form()"
                    class="space-y-4"
                    #default="{ errors, processing }"
                >
                    <div class="grid gap-2">
                        <Label for="leave_type_id">Leave type</Label>
                        <HrisSelectField
                            id="leave_type_id"
                            name="leave_type_id"
                            placeholder="Select leave type"
                            :options="props.leaveTypes"
                            option-value="id"
                            option-label="name"
                            :default-value="props.leaveTypes[0]?.id"
                        />
                        <InputError :message="errors.leave_type_id" />
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="start_date">Start date</Label>
                            <Input
                                id="start_date"
                                name="start_date"
                                type="date"
                            />
                            <InputError :message="errors.start_date" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="end_date">End date</Label>
                            <Input id="end_date" name="end_date" type="date" />
                            <InputError :message="errors.end_date" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="reason">Reason</Label>
                        <Textarea id="reason" name="reason" rows="4" />
                        <InputError :message="errors.reason" />
                    </div>

                    <Button :disabled="processing">
                        {{ processing ? 'Submitting...' : 'Submit request' }}
                    </Button>
                </Form>
            </HrisSectionCard>

            <HrisSectionCard
                title="Leave History"
                description="Your latest requests and their current status."
            >
                <Table>
                    <TableHeader>
                        <TableRow class="hover:bg-transparent">
                            <TableHead>Type</TableHead>
                            <TableHead>Dates</TableHead>
                            <TableHead>Days</TableHead>
                            <TableHead>Status</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="item in props.history"
                            :key="String(item.id)"
                        >
                            <TableCell>{{ item.leave_type }}</TableCell>
                            <TableCell>
                                {{ item.start_date }} to
                                {{ item.end_date }}
                            </TableCell>
                            <TableCell>{{ item.days }}</TableCell>
                            <TableCell>
                                <HrisStatusBadge :status="item.status" />
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="props.history.length === 0">
                            <TableCell
                                colspan="4"
                                class="h-24 text-center text-muted-foreground"
                            >
                                No leave requests have been submitted yet.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </HrisSectionCard>
        </section>

        <HrisSectionCard
            v-if="props.pendingApprovals.length > 0"
            title="Pending Approvals"
            description="Approve or reject employee requests from the same page."
        >
            <div class="space-y-4">
                <Alert v-if="approvalStatus">
                    <CircleCheck class="size-4" />
                    <AlertTitle>Leave updated</AlertTitle>
                    <AlertDescription>{{ approvalStatus }}</AlertDescription>
                </Alert>

                <Alert v-if="approvalError" variant="destructive">
                    <TriangleAlert class="size-4" />
                    <AlertTitle>Approval failed</AlertTitle>
                    <AlertDescription>{{ approvalError }}</AlertDescription>
                </Alert>

                <Table>
                    <TableHeader>
                        <TableRow class="hover:bg-transparent">
                            <TableHead>Employee</TableHead>
                            <TableHead>Type</TableHead>
                            <TableHead>Dates</TableHead>
                            <TableHead>Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="item in props.pendingApprovals"
                            :key="String(item.id)"
                        >
                            <TableCell>{{ item.employee }}</TableCell>
                            <TableCell>{{ item.leave_type }}</TableCell>
                            <TableCell>
                                {{ item.start_date }} to
                                {{ item.end_date }}
                            </TableCell>
                            <TableCell>
                                <div class="flex flex-wrap gap-2">
                                    <Button
                                        size="sm"
                                        :disabled="approvalBusyId === item.id"
                                        @click="
                                            reviewLeave(
                                                Number(item.id),
                                                'approved',
                                            )
                                        "
                                    >
                                        Approve
                                    </Button>
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        :disabled="approvalBusyId === item.id"
                                        @click="
                                            reviewLeave(
                                                Number(item.id),
                                                'rejected',
                                            )
                                        "
                                    >
                                        Reject
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </HrisSectionCard>
    </div>
</template>
