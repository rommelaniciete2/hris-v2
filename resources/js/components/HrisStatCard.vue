<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import type { BadgeVariants } from '@/components/ui/badge';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        title: string;
        value: string | number;
        description?: string;
        badge?: string;
        badgeVariant?: NonNullable<BadgeVariants['variant']>;
        class?: HTMLAttributes['class'];
        valueClass?: HTMLAttributes['class'];
        contentClass?: HTMLAttributes['class'];
    }>(),
    {
        description: undefined,
        badge: undefined,
        badgeVariant: 'outline',
        class: undefined,
        valueClass: undefined,
        contentClass: undefined,
    },
);
</script>

<template>
    <Card :class="cn('gap-4 border-border/70 shadow-sm', props.class)">
        <CardHeader class="gap-1.5">
            <CardDescription>{{ props.title }}</CardDescription>
            <CardTitle :class="cn('text-3xl font-semibold', props.valueClass)">
                {{ props.value }}
            </CardTitle>
        </CardHeader>
        <CardContent
            v-if="props.description || props.badge || $slots.default"
            :class="
                cn(
                    'flex items-center justify-between gap-3',
                    props.contentClass,
                )
            "
        >
            <p v-if="props.description" class="text-sm text-muted-foreground">
                {{ props.description }}
            </p>
            <slot />
            <Badge v-if="props.badge" :variant="props.badgeVariant">
                {{ props.badge }}
            </Badge>
        </CardContent>
    </Card>
</template>
