<script setup lang="ts">
import { useSlots } from 'vue';
import type { HTMLAttributes } from 'vue';
import Heading from '@/components/Heading.vue';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { cn } from '@/lib/utils';

defineOptions({
    inheritAttrs: false,
});

const props = withDefaults(
    defineProps<{
        title: string;
        description?: string;
        variant?: 'default' | 'small';
        class?: HTMLAttributes['class'];
        headerClass?: HTMLAttributes['class'];
        contentClass?: HTMLAttributes['class'];
    }>(),
    {
        description: undefined,
        variant: 'small',
        class: undefined,
        headerClass: undefined,
        contentClass: undefined,
    },
);

const slots = useSlots();
</script>

<template>
    <Card
        v-bind="$attrs"
        :class="cn('border-border/70 shadow-sm', props.class)"
    >
        <CardHeader
            :class="
                cn(
                    slots.actions &&
                        'flex flex-row items-start justify-between gap-4',
                    props.headerClass,
                )
            "
        >
            <Heading
                :variant="props.variant"
                :title="props.title"
                :description="props.description"
            />
            <slot name="actions" />
        </CardHeader>
        <CardContent :class="props.contentClass">
            <slot />
        </CardContent>
    </Card>
</template>
