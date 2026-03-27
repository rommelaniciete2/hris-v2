<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { ChevronDown } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

defineOptions({
    inheritAttrs: false,
});

const props = defineProps<{
    modelValue?: string | number | null;
    class?: HTMLAttributes['class'];
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

function handleChange(event: Event): void {
    emit('update:modelValue', (event.target as HTMLSelectElement).value);
}
</script>

<template>
    <div data-slot="native-select-wrapper" class="relative">
        <select
            data-slot="native-select"
            v-bind="$attrs"
            :value="props.modelValue ?? undefined"
            :class="
                cn(
                    'border-input text-foreground focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive dark:bg-input/30 flex h-9 w-full appearance-none rounded-md border bg-transparent px-3 py-2 pr-9 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50',
                    props.class,
                )
            "
            @change="handleChange"
        >
            <slot />
        </select>
        <ChevronDown
            class="text-muted-foreground pointer-events-none absolute top-1/2 right-3 size-4 -translate-y-1/2"
        />
    </div>
</template>
