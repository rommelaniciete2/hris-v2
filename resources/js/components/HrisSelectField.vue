<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

type HrisSelectOption = Record<string, unknown>;

const EMPTY_VALUE = '__hris-empty-option__';

const props = withDefaults(
    defineProps<{
        id?: string;
        name?: string;
        options: HrisSelectOption[];
        placeholder?: string;
        emptyLabel?: string;
        optionValue?: string;
        optionLabel?: string;
        optionDisabled?: string;
        modelValue?: string | number | null;
        defaultValue?: string | number | null;
        disabled?: boolean;
        required?: boolean;
    }>(),
    {
        placeholder: 'Select an option',
        optionValue: 'value',
        optionLabel: 'label',
        optionDisabled: 'disabled',
        disabled: false,
        required: false,
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const rootElement = ref<HTMLElement | null>(null);
const internalValue = ref<string | undefined>(
    normalizeValue(props.modelValue ?? props.defaultValue),
);

let formElement: HTMLFormElement | null = null;

function normalizeValue(value: unknown): string | undefined {
    if (value === null || value === undefined) {
        return undefined;
    }

    return String(value);
}

function resolveOptionLabel(option: HrisSelectOption): string {
    const label = option[props.optionLabel];

    if (label === null || label === undefined || String(label).length === 0) {
        return String(option[props.optionValue] ?? '');
    }

    return String(label);
}

function handleValueChange(value: unknown): void {
    const normalizedValue = normalizeValue(value) ?? '';
    const nextValue = normalizedValue === EMPTY_VALUE ? '' : normalizedValue;

    if (props.modelValue === undefined) {
        internalValue.value = nextValue;
    }

    emit('update:modelValue', nextValue);
}

function handleFormReset(): void {
    const nextValue = normalizeValue(props.defaultValue);

    internalValue.value = nextValue;

    if (props.modelValue !== undefined) {
        emit('update:modelValue', nextValue ?? '');
    }
}

const selectValue = computed(() => {
    if (internalValue.value === '' && props.emptyLabel) {
        return EMPTY_VALUE;
    }

    return internalValue.value;
});

const submittedValue = computed(() => {
    if (internalValue.value === EMPTY_VALUE) {
        return '';
    }

    return internalValue.value ?? '';
});

watch(
    () => props.modelValue,
    (value) => {
        internalValue.value = normalizeValue(value);
    },
);

watch(
    () => props.defaultValue,
    (value) => {
        if (props.modelValue === undefined) {
            internalValue.value = normalizeValue(value);
        }
    },
);

onMounted(() => {
    formElement = rootElement.value?.closest('form') ?? null;
    formElement?.addEventListener('reset', handleFormReset);
});

onBeforeUnmount(() => {
    formElement?.removeEventListener('reset', handleFormReset);
});
</script>

<template>
    <div ref="rootElement">
        <input
            v-if="props.name"
            type="hidden"
            :name="props.name"
            :value="submittedValue"
            :disabled="props.disabled"
        />
        <Select
            :disabled="props.disabled"
            :model-value="selectValue"
            @update:model-value="handleValueChange"
        >
            <SelectTrigger :id="props.id" class="w-full">
                <SelectValue :placeholder="props.placeholder" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem v-if="props.emptyLabel" :value="EMPTY_VALUE">
                    {{ props.emptyLabel }}
                </SelectItem>
                <SelectItem
                    v-for="(option, index) in props.options"
                    :key="
                        `${props.name ?? props.id ?? 'select'}-${index}-${String(option[props.optionValue] ?? '')}`
                    "
                    :value="String(option[props.optionValue] ?? '')"
                    :disabled="Boolean(option[props.optionDisabled])"
                >
                    {{ resolveOptionLabel(option) }}
                </SelectItem>
            </SelectContent>
        </Select>
    </div>
</template>
