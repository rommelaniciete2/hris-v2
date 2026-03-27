<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { CircleCheck, MapPin, TriangleAlert } from 'lucide-vue-next';
import { ref } from 'vue';
import AttendancePunchController from '@/actions/App/Http/Controllers/Api/V1/AttendancePunchController';
import Heading from '@/components/Heading.vue';
import HrisSectionCard from '@/components/HrisSectionCard.vue';
import HrisStatusBadge from '@/components/HrisStatusBadge.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { formatHrisLabel } from '@/lib/hris';
import { postJson } from '@/lib/http';
import { index as attendanceIndex } from '@/routes/hris/attendance';

type AttendanceRecord = {
    id: number;
    work_date: string;
    clock_in_at?: string | null;
    clock_out_at?: string | null;
    status: string;
    late_minutes: number;
    undertime_minutes: number;
    overtime_minutes: number;
};

const props = defineProps<{
    employee: { id: number; full_name: string; employee_number: string } | null;
    location: {
        id: number;
        name: string;
        address: string;
        radius_meters: number;
        latitude: number;
        longitude: number;
    } | null;
    schedule: {
        id: number;
        name: string;
        starts_at: string;
        ends_at: string;
        late_grace_minutes: number;
    } | null;
    todayRecord: AttendanceRecord | null;
    logs: AttendanceRecord[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Attendance',
                href: attendanceIndex(),
            },
        ],
    },
});

const statusMessage = ref('');
const errorMessage = ref('');
const processing = ref(false);

async function punch(): Promise<void> {
    errorMessage.value = '';
    statusMessage.value = '';
    processing.value = true;

    try {
        const position = await new Promise<GeolocationPosition>(
            (resolve, reject) => {
                navigator.geolocation.getCurrentPosition(resolve, reject, {
                    enableHighAccuracy: true,
                    timeout: 15000,
                });
            },
        );

        await postJson(AttendancePunchController.url(), {
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
            accuracy: Math.round(position.coords.accuracy),
        });

        statusMessage.value = 'Attendance recorded successfully.';
        window.location.reload();
    } catch (error) {
        errorMessage.value =
            error instanceof Error
                ? error.message
                : 'Unable to complete attendance punch.';
    } finally {
        processing.value = false;
    }
}
</script>

<template>
    <Head title="Attendance" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <Heading
            title="Attendance"
            description="Punch in or out with one action, then review your latest daily logs."
        />

        <section class="grid gap-4 xl:grid-cols-[1.2fr_0.8fr]">
            <HrisSectionCard
                title="Today's Attendance"
                description="Use your current GPS location to validate the punch."
                content-class="space-y-6"
            >
                <div class="grid gap-4 sm:grid-cols-2">
                    <Card class="bg-muted/40 shadow-none">
                        <CardHeader class="gap-1.5">
                            <CardDescription>Employee</CardDescription>
                            <CardTitle class="text-lg">
                                {{
                                    props.employee?.full_name ??
                                    'No employee profile'
                                }}
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="text-sm text-muted-foreground">
                            {{
                                props.employee?.employee_number ??
                                'Missing profile'
                            }}
                        </CardContent>
                    </Card>

                    <Card class="bg-muted/40 shadow-none">
                        <CardHeader class="gap-1.5">
                            <CardDescription>Status</CardDescription>
                            <div class="flex items-center gap-2">
                                <CardTitle class="text-lg">
                                    {{
                                        formatHrisLabel(
                                            props.todayRecord?.status,
                                            'Ready',
                                        )
                                    }}
                                </CardTitle>
                                <HrisStatusBadge
                                    :status="
                                        props.todayRecord?.status ?? 'ready'
                                    "
                                />
                            </div>
                        </CardHeader>
                        <CardContent class="text-sm text-muted-foreground">
                            {{
                                props.todayRecord?.clock_out_at
                                    ? "You have completed today's log."
                                    : 'You can punch using the button below.'
                            }}
                        </CardContent>
                    </Card>
                </div>

                <div class="flex flex-wrap items-center gap-4">
                    <Button :disabled="processing" @click="punch">
                        {{
                            processing
                                ? 'Checking location...'
                                : 'Time In / Time Out'
                        }}
                    </Button>
                </div>

                <Alert v-if="statusMessage">
                    <CircleCheck class="size-4" />
                    <AlertTitle>Attendance updated</AlertTitle>
                    <AlertDescription>{{ statusMessage }}</AlertDescription>
                </Alert>

                <Alert v-if="errorMessage" variant="destructive">
                    <TriangleAlert class="size-4" />
                    <AlertTitle>Punch failed</AlertTitle>
                    <AlertDescription>{{ errorMessage }}</AlertDescription>
                </Alert>
            </HrisSectionCard>

            <HrisSectionCard
                title="Assigned Site"
                description="Attendance is only valid within the configured radius."
                content-class="space-y-4 text-sm"
            >
                <div
                    class="flex items-start gap-3 rounded-xl border border-border/60 p-4"
                >
                    <MapPin class="mt-0.5 size-4 text-muted-foreground" />
                    <div>
                        <p class="text-muted-foreground">Location</p>
                        <p class="mt-1 font-medium">
                            {{ props.location?.name ?? 'Not assigned' }}
                        </p>
                        <p class="mt-1 text-muted-foreground">
                            {{ props.location?.address ?? 'No address' }}
                        </p>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <Card class="bg-muted/40 shadow-none">
                        <CardHeader class="gap-1.5">
                            <CardDescription>Radius</CardDescription>
                            <CardTitle class="text-lg">
                                {{ props.location?.radius_meters ?? 0 }}
                                meters
                            </CardTitle>
                        </CardHeader>
                    </Card>

                    <Card class="bg-muted/40 shadow-none">
                        <CardHeader class="gap-1.5">
                            <CardDescription>Schedule</CardDescription>
                            <CardTitle class="text-base">
                                {{ props.schedule?.name ?? 'Not assigned' }}
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="text-sm text-muted-foreground">
                            {{ props.schedule?.starts_at ?? '--:--' }} to
                            {{ props.schedule?.ends_at ?? '--:--' }}
                        </CardContent>
                    </Card>
                </div>
            </HrisSectionCard>
        </section>

        <HrisSectionCard
            title="Daily Logs"
            description="A compact view of your latest attendance records."
        >
            <Table>
                <TableHeader>
                    <TableRow class="hover:bg-transparent">
                        <TableHead>Date</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Late</TableHead>
                        <TableHead>Undertime</TableHead>
                        <TableHead>Overtime</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="item in props.logs" :key="item.id">
                        <TableCell>{{ item.work_date }}</TableCell>
                        <TableCell>
                            <HrisStatusBadge :status="item.status" />
                        </TableCell>
                        <TableCell>{{ item.late_minutes }} min</TableCell>
                        <TableCell>
                            {{ item.undertime_minutes }} min
                        </TableCell>
                        <TableCell>{{ item.overtime_minutes }} min</TableCell>
                    </TableRow>
                    <TableRow v-if="props.logs.length === 0">
                        <TableCell
                            colspan="5"
                            class="h-24 text-center text-muted-foreground"
                        >
                            No attendance logs have been recorded yet.
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </HrisSectionCard>
    </div>
</template>
