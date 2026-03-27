<script setup lang="ts">
import { computed } from 'vue';
import type { HTMLAttributes } from 'vue';
import { Badge } from '@/components/ui/badge';
import type { HrisStatusVariant } from '@/lib/hris';
import { formatHrisLabel, getHrisStatusVariant } from '@/lib/hris';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        status: unknown;
        fallback?: string;
        variant?: HrisStatusVariant;
        class?: HTMLAttributes['class'];
    }>(),
    {
        fallback: 'Unknown',
        variant: undefined,
    },
);

const label = computed(() => formatHrisLabel(props.status, props.fallback));
const resolvedVariant = computed(
    () => props.variant ?? getHrisStatusVariant(props.status),
);
</script>

<template>
    <Badge :variant="resolvedVariant" :class="cn(props.class)">
        {{ label }}
    </Badge>
</template>
