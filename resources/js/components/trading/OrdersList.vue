<script setup lang="ts">
import { destroy } from '@/actions/App/Http/Controllers/OrderController';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { formatAmount, formatDate, formatUsd } from '@/lib/utils';
import type { Order } from '@/types/trading';
import { useAutoAnimate } from '@formkit/auto-animate/vue';
import { useForm } from '@inertiajs/vue3';
import { Inbox } from 'lucide-vue-next';
import { ref } from 'vue';

const [ordersContainer] = useAutoAnimate({ duration: 200 });

interface Props {
    orders: Order[];
}

defineProps<Props>();

const emit = defineEmits<{
    refresh: [];
}>();

const cancellingOrderId = ref<number | null>(null);
const cancelForm = useForm({});

const getStatusColor = (status: string) => {
    switch (status) {
        case 'open':
            return 'text-amber-400 bg-amber-500/10';
        case 'filled':
            return 'text-emerald-400 bg-emerald-500/10';
        case 'cancelled':
            return 'text-zinc-500 bg-zinc-500/10';
        default:
            return 'text-zinc-400 bg-zinc-500/10';
    }
};

const cancelOrder = (order: Order) => {
    if (cancelForm.processing) return;

    cancellingOrderId.value = order.id;

    cancelForm.delete(destroy.url(order.id), {
        preserveScroll: true,
        onSuccess: () => {
            emit('refresh');
        },
        onFinish: () => {
            cancellingOrderId.value = null;
        },
    });
};
</script>

<template>
    <div
        class="flex flex-col rounded-lg border border-zinc-800 bg-zinc-900/50 backdrop-blur"
    >
        <!-- Header -->
        <div class="shrink-0 border-b border-zinc-800 px-4 py-3">
            <div class="flex items-center justify-between">
                <h2
                    class="font-mono text-xs font-semibold tracking-wider text-zinc-500 uppercase"
                >
                    My Orders
                </h2>
                <span class="font-mono text-[10px] text-zinc-600">
                    {{ orders.length }} orders
                </span>
            </div>
        </div>

        <!-- Empty State -->
        <div
            v-if="orders.length === 0"
            class="flex flex-1 flex-col items-center justify-center px-4 py-12 text-center"
        >
            <Inbox
                class="mx-auto mb-3 h-10 w-10 text-zinc-700"
                :stroke-width="1.5"
            />
            <div class="font-mono text-xs text-zinc-600">No orders yet</div>
            <div class="mt-1 font-mono text-[10px] text-zinc-700">
                Place your first order to get started
            </div>
        </div>

        <!-- Orders List -->
        <div
            v-else
            ref="ordersContainer"
            class="min-h-0 flex-1 divide-y divide-zinc-800/50 overflow-y-auto"
        >
            <div
                v-for="order in orders"
                :key="order.id"
                :class="[
                    'p-4 transition-colors',
                    order.status === 'cancelled'
                        ? 'bg-zinc-900/30 opacity-60'
                        : 'hover:bg-zinc-800/30',
                ]"
            >
                <!-- Order Header -->
                <div class="mb-2 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span
                            :class="[
                                'rounded px-2 py-0.5 font-mono text-[10px] font-bold uppercase',
                                order.status === 'cancelled'
                                    ? 'bg-zinc-700/30 text-zinc-500'
                                    : order.side === 'buy'
                                      ? 'bg-emerald-500/20 text-emerald-400'
                                      : 'bg-red-500/20 text-red-400',
                            ]"
                        >
                            {{ order.side }}
                        </span>
                        <span
                            :class="[
                                'font-mono text-sm font-semibold',
                                order.status === 'cancelled'
                                    ? 'text-zinc-500 line-through'
                                    : 'text-zinc-200',
                            ]"
                        >
                            {{ order.symbol }}/USD
                        </span>
                    </div>
                    <span
                        :class="[
                            'rounded px-2 py-0.5 font-mono text-[10px] font-medium uppercase',
                            getStatusColor(order.status),
                        ]"
                    >
                        {{ order.status }}
                    </span>
                </div>

                <!-- Order Details -->
                <div class="grid grid-cols-2 gap-x-4 gap-y-1">
                    <div class="flex justify-between">
                        <span class="font-mono text-[10px] text-zinc-600"
                            >Price</span
                        >
                        <span
                            :class="[
                                'font-mono text-xs',
                                order.status === 'cancelled'
                                    ? 'text-zinc-500'
                                    : 'text-zinc-300',
                            ]"
                            >${{ formatUsd(order.price) }}</span
                        >
                    </div>
                    <div class="flex justify-between">
                        <span class="font-mono text-[10px] text-zinc-600"
                            >Amount</span
                        >
                        <span
                            :class="[
                                'font-mono text-xs',
                                order.status === 'cancelled'
                                    ? 'text-zinc-500'
                                    : 'text-zinc-300',
                            ]"
                            >{{ formatAmount(order.amount) }}</span
                        >
                    </div>
                    <div class="flex justify-between">
                        <span class="font-mono text-[10px] text-zinc-600"
                            >Total</span
                        >
                        <span
                            :class="[
                                'font-mono text-xs',
                                order.status === 'cancelled'
                                    ? 'text-zinc-500'
                                    : 'text-zinc-300',
                            ]"
                            >${{ formatUsd(order.total) }}</span
                        >
                    </div>
                    <div class="flex justify-between">
                        <span class="font-mono text-[10px] text-zinc-600"
                            >Time</span
                        >
                        <span class="font-mono text-[10px] text-zinc-500">{{
                            formatDate(order.created_at)
                        }}</span>
                    </div>
                </div>

                <!-- Cancel Button -->
                <div v-if="order.status === 'open'" class="mt-3">
                    <Button
                        size="sm"
                        variant="outline"
                        @click="cancelOrder(order)"
                        :disabled="
                            cancelForm.processing &&
                            cancellingOrderId === order.id
                        "
                        class="w-full border-zinc-700 bg-transparent font-mono text-[10px] text-zinc-400 hover:border-red-500/50! hover:bg-red-500/10! hover:text-red-400"
                    >
                        <Spinner
                            v-if="
                                cancelForm.processing &&
                                cancellingOrderId === order.id
                            "
                            class="mr-2 h-3 w-3"
                        />
                        {{
                            cancelForm.processing &&
                            cancellingOrderId === order.id
                                ? 'CANCELLING...'
                                : 'CANCEL ORDER'
                        }}
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>
