import type { BadgeVariants } from '@/components/ui/badge';

export type HrisStatusVariant = NonNullable<BadgeVariants['variant']>;

const statusVariants: Record<string, HrisStatusVariant> = {
    active: 'default',
    approved: 'default',
    completed: 'default',
    hired: 'default',
    open: 'default',
    present: 'default',
    processed: 'default',
    released: 'default',
    submitted: 'default',
    within_radius: 'default',

    draft: 'secondary',
    in_progress: 'secondary',
    interviewed: 'secondary',
    late: 'secondary',
    on_leave: 'secondary',
    paused: 'secondary',
    pending: 'secondary',
    probationary: 'secondary',
    ready: 'secondary',
    regular: 'secondary',
    scheduled: 'secondary',
    screening: 'secondary',

    absent: 'destructive',
    cancelled: 'destructive',
    closed: 'destructive',
    inactive: 'destructive',
    outside_radius: 'destructive',
    rejected: 'destructive',
    suspended: 'destructive',
    terminated: 'destructive',
};

const amountFormatter = new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
});

export function getHrisStatusVariant(value: unknown): HrisStatusVariant {
    const normalized = String(value ?? '')
        .trim()
        .toLowerCase();

    return statusVariants[normalized] ?? 'outline';
}

export function formatHrisLabel(value: unknown, fallback = 'Unknown'): string {
    const normalized = String(value ?? '')
        .trim()
        .replaceAll(/[_-]+/g, ' ')
        .replaceAll(/\s+/g, ' ');

    if (normalized.length === 0) {
        return fallback;
    }

    return normalized
        .split(' ')
        .map((segment) => {
            if (segment.length === 0) {
                return segment;
            }

            return segment[0].toUpperCase() + segment.slice(1);
        })
        .join(' ');
}

export function formatHrisAmount(value: unknown, fallback = '0.00'): string {
    const amount = Number(value);

    if (!Number.isFinite(amount)) {
        return fallback;
    }

    return amountFormatter.format(Math.round(amount * 100) / 100);
}

export function sumHrisAmounts(values: unknown[]): number {
    return (
        values.reduce<number>((total, value) => {
            const amount = Number(value);

            if (!Number.isFinite(amount)) {
                return total;
            }

            return total + Math.round(amount * 100);
        }, 0) / 100
    );
}
