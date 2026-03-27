import type { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';
import type { RouteDefinition } from '@/wayfinder';

export type LinkTarget =
    | NonNullable<InertiaLinkProps['href']>
    | RouteDefinition<'get'>;

export type BreadcrumbItem = {
    title: string;
    href: LinkTarget;
};

export type NavItem = {
    title: string;
    href: LinkTarget;
    icon?: LucideIcon;
    isActive?: boolean;
};
