<script setup lang="ts">
import { store } from '@/actions/App/Http/Controllers/OrderController';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { formatUsd } from '@/lib/utils';
import type { Asset, OrderSide, TradingSymbol } from '@/types/trading';
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

interface Props {
    open: boolean;
    symbol: TradingSymbol;
    side: OrderSide;
    balance: string;
    assets: Asset[];
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'update:open': [value: boolean];
    success: [];
}>();

const form = useForm({
    symbol: props.symbol,
    side: props.side,
    price: '',
    amount: '',
});

// Reset form when modal opens and sync symbol/side
watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            form.reset();
            form.clearErrors();
            form.symbol = props.symbol;
            form.side = props.side;
        }
    },
);

watch(
    () => [props.symbol, props.side],
    ([symbol, side]) => {
        form.symbol = symbol;
        form.side = side;
    },
);

const total = computed(() => {
    const p = parseFloat(form.price) || 0;
    const a = parseFloat(form.amount) || 0;
    return (p * a).toFixed(2);
});

const availableBalance = computed(() => {
    return parseFloat(props.balance) || 0;
});

const availableAsset = computed(() => {
    const asset = props.assets.find((a) => a.symbol === props.symbol);
    return asset ? parseFloat(asset.available_amount) : 0;
});

const canSubmit = computed(() => {
    const p = parseFloat(form.price) || 0;
    const a = parseFloat(form.amount) || 0;
    const t = p * a;

    if (p <= 0 || a <= 0) return false;

    if (props.side === 'buy') {
        return t <= availableBalance.value;
    } else {
        return a <= availableAsset.value;
    }
});

const submit = () => {
    if (form.processing || !canSubmit.value) return;

    form.post(store.url(), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            emit('update:open', false);
            emit('success');
        },
    });
};

const closeModal = () => {
    emit('update:open', false);
};
</script>

<template>
    <Dialog :open="open" @update:open="closeModal">
        <DialogContent
            class="border-zinc-800 bg-zinc-900 sm:max-w-md"
            :class="
                side === 'buy'
                    ? 'ring-1 ring-emerald-500/20'
                    : 'ring-1 ring-red-500/20'
            "
        >
            <DialogHeader>
                <DialogTitle class="flex items-center gap-3 font-mono">
                    <span
                        :class="[
                            'rounded px-2 py-1 text-xs font-bold uppercase',
                            side === 'buy'
                                ? 'bg-emerald-500/20 text-emerald-400'
                                : 'bg-red-500/20 text-red-400',
                        ]"
                    >
                        {{ side }}
                    </span>
                    <span class="text-zinc-100">{{ symbol }}/USD</span>
                </DialogTitle>
                <DialogDescription class="font-mono text-xs text-zinc-500">
                    Place a limit {{ side }} order for {{ symbol }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="mt-4 space-y-4">
                <!-- General Error -->
                <div
                    v-if="form.errors.general"
                    class="rounded-md bg-red-500/10 px-3 py-2 text-sm text-red-400"
                >
                    {{ form.errors.general }}
                </div>

                <!-- Available Balance/Asset -->
                <div class="rounded-md bg-zinc-800/50 px-3 py-2">
                    <div class="flex items-center justify-between">
                        <span
                            class="font-mono text-[10px] tracking-wider text-zinc-600 uppercase"
                        >
                            {{
                                side === 'buy'
                                    ? 'Available Balance'
                                    : `Available ${symbol}`
                            }}
                        </span>
                        <span class="font-mono text-sm text-zinc-300">
                            <template v-if="side === 'buy'">
                                ${{ formatUsd(availableBalance) }}
                            </template>
                            <template v-else>
                                {{ availableAsset.toFixed(8) }} {{ symbol }}
                            </template>
                        </span>
                    </div>
                </div>

                <!-- Price Input -->
                <div class="space-y-2">
                    <Label class="font-mono text-xs text-zinc-400"
                        >Price (USD)</Label
                    >
                    <div class="relative">
                        <span
                            class="absolute top-1/2 left-3 -translate-y-1/2 font-mono text-sm text-zinc-500"
                            >$</span
                        >
                        <Input
                            v-model="form.price"
                            type="number"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                            class="border-zinc-700 bg-zinc-800/50 pl-7 font-mono text-zinc-100 placeholder:text-zinc-600 focus:border-zinc-600 focus:ring-zinc-600"
                            :aria-invalid="!!form.errors.price"
                        />
                    </div>
                    <p v-if="form.errors.price" class="text-xs text-red-400">
                        {{ form.errors.price }}
                    </p>
                </div>

                <!-- Amount Input -->
                <div class="space-y-2">
                    <Label class="font-mono text-xs text-zinc-400"
                        >Amount ({{ symbol }})</Label
                    >
                    <div class="relative">
                        <Input
                            v-model="form.amount"
                            type="number"
                            step="0.00000001"
                            min="0"
                            placeholder="0.00000000"
                            class="border-zinc-700 bg-zinc-800/50 font-mono text-zinc-100 placeholder:text-zinc-600 focus:border-zinc-600 focus:ring-zinc-600"
                            :aria-invalid="!!form.errors.amount"
                        />
                        <span
                            class="absolute top-1/2 right-3 -translate-y-1/2 font-mono text-sm text-zinc-500"
                        >
                            {{ symbol }}
                        </span>
                    </div>
                    <p v-if="form.errors.amount" class="text-xs text-red-400">
                        {{ form.errors.amount }}
                    </p>
                </div>

                <!-- Total Preview -->
                <div
                    class="rounded-md border border-zinc-700 bg-zinc-800/30 px-4 py-3"
                >
                    <div class="flex items-center justify-between">
                        <span
                            class="font-mono text-xs tracking-wider text-zinc-500 uppercase"
                            >Total</span
                        >
                        <span
                            class="font-mono text-lg font-semibold text-zinc-100"
                        >
                            ${{ total }}
                        </span>
                    </div>
                    <div
                        class="mt-1 text-right font-mono text-[10px] text-zinc-600"
                    >
                        + 1.5% commission on match
                    </div>
                </div>

                <!-- Insufficient Balance Warning -->
                <div
                    v-if="
                        side === 'buy' &&
                        parseFloat(total) > availableBalance &&
                        parseFloat(total) > 0
                    "
                    class="rounded-md bg-amber-500/10 px-3 py-2 text-xs text-amber-400"
                >
                    Insufficient balance. You need ${{
                        formatUsd(parseFloat(total) - availableBalance)
                    }}
                    more.
                </div>
                <div
                    v-if="
                        side === 'sell' &&
                        (parseFloat(form.amount) || 0) > availableAsset &&
                        parseFloat(form.amount) > 0
                    "
                    class="rounded-md bg-amber-500/10 px-3 py-2 text-xs text-amber-400"
                >
                    Insufficient {{ symbol }}. You need
                    {{
                        (
                            (parseFloat(form.amount) || 0) - availableAsset
                        ).toFixed(8)
                    }}
                    more.
                </div>

                <!-- Submit Button -->
                <Button
                    type="submit"
                    :disabled="!canSubmit || form.processing"
                    :class="[
                        'w-full font-mono font-bold uppercase',
                        side === 'buy'
                            ? 'bg-emerald-600 hover:bg-emerald-500 disabled:bg-emerald-600/50'
                            : 'bg-red-600 hover:bg-red-500 disabled:bg-red-600/50',
                    ]"
                >
                    <Spinner v-if="form.processing" class="mr-2 h-4 w-4" />
                    {{ form.processing ? 'Placing Order...' : 'Place Order' }}
                </Button>
            </form>
        </DialogContent>
    </Dialog>
</template>
