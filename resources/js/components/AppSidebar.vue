<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    BriefcaseBusiness,
    CalendarClock,
    FileSpreadsheet,
    LayoutGrid,
    TimerReset,
    Users,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as attendanceIndex } from '@/routes/hris/attendance';
import { index as employeesIndex } from '@/routes/hris/employees';
import { index as leaveIndex } from '@/routes/hris/leave';
import { index as payrollIndex } from '@/routes/hris/payroll';
import { index as reportsIndex } from '@/routes/hris/reports';
import type { NavItem } from '@/types';

const page = usePage();
const capabilities = computed(() => page.props.auth.capabilities);

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
    ];

    if (capabilities.value.employees) {
        items.push({
            title: 'Employees',
            href: employeesIndex(),
            icon: Users,
        });
    }

    if (capabilities.value.attendance) {
        items.push({
            title: 'Attendance',
            href: attendanceIndex(),
            icon: TimerReset,
        });
    }

    if (capabilities.value.leave) {
        items.push({
            title: 'Leave',
            href: leaveIndex(),
            icon: CalendarClock,
        });
    }

    if (capabilities.value.payroll) {
        items.push({
            title: 'Payroll',
            href: payrollIndex(),
            icon: FileSpreadsheet,
        });
    }

    if (capabilities.value.reports) {
        items.push({
            title: 'Reports',
            href: reportsIndex(),
            icon: BriefcaseBusiness,
        });
    }

    return items;
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
