import type { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx } from 'clsx';
import type { ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';
import type { RouteDefinition } from '@/wayfinder';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(
    href: NonNullable<InertiaLinkProps['href']> | RouteDefinition<'get'>,
) {
    return typeof href === 'string' ? href : href?.url;
}
