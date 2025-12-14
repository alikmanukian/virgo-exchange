<script setup lang="ts">
import OrderbookRow from '@/components/trading/Orderbook/OrderbookRow.vue';
import { formatUsd } from '@/lib/utils';
import type { Orderbook, TradingSymbol } from '@/types/trading';
import { useAutoAnimate } from '@formkit/auto-animate/vue';
import { RefreshCw } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref, watch } from 'vue';

const [asksContainer] = useAutoAnimate({ duration: 150 });
const [bidsContainer] = useAutoAnimate({ duration: 150 });

interface Props {
    symbol: TradingSymbol;
    orderbook: Orderbook | null;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    refresh: [];
}>();

const connected = ref(false);

const subscribeToChannel = () => {
    if (!window.Echo) return;

    window.Echo.channel(`orderbook.${props.symbol}`)
        .listen('.OrderbookUpdated', () => {
            emit('refresh');
            connected.value = true;
        })
        .subscribed(() => {
            connected.value = true;
        })
        .error(() => {
            connected.value = false;
        });
};

const unsubscribeFromChannel = () => {
    if (!window.Echo) return;
    window.Echo.leave(`orderbook.${props.symbol}`);
};

watch(
    () => props.symbol,
    (_newSymbol, oldSymbol) => {
        if (oldSymbol) {
            window.Echo?.leave(`orderbook.${oldSymbol}`);
        }
        subscribeToChannel();
    },
);

onMounted(() => {
    subscribeToChannel();
});

onUnmounted(() => {
    unsubscribeFromChannel();
});
</script>

<template>
    <div
        class="flex min-h-0 flex-1 flex-col rounded-lg border border-zinc-800 bg-zinc-900/50 backdrop-blur"
    >
        <!-- Header -->
        <div class="shrink-0 border-b border-zinc-800 px-4 py-3">
            <div class="flex items-center justify-between">
                <h2
                    class="font-mono text-xs font-semibold tracking-wider text-zinc-500 uppercase"
                >
                    Order Book
                </h2>
                <div class="flex items-center gap-2">
                    <span
                        class="h-1.5 w-1.5 rounded-full"
                        :class="connected ? 'bg-emerald-400' : 'bg-zinc-600'"
                    ></span>
                    <span class="font-mono text-xs font-semibold text-zinc-300">
                        {{ symbol }}/USD
                    </span>
                </div>
            </div>
        </div>

        <!-- Column Headers -->
        <div
            class="grid shrink-0 grid-cols-3 border-b border-zinc-800 px-4 py-2"
        >
            <span
                class="font-mono text-[10px] tracking-wider text-zinc-600 uppercase"
                >Price (USD)</span
            >
            <span
                class="text-right font-mono text-[10px] tracking-wider text-zinc-600 uppercase"
                >Amount</span
            >
            <span
                class="text-right font-mono text-[10px] tracking-wider text-zinc-600 uppercase"
                >Total</span
            >
        </div>

        <!-- Orderbook Data -->
        <div class="min-h-0 flex-1 overflow-y-auto">
            <!-- Asks (Sell Orders) - Red -->
            <div ref="asksContainer" class="border-b border-zinc-800">
                <div
                    v-if="!orderbook?.asks?.length"
                    class="px-4 py-8 text-center"
                >
                    <span class="font-mono text-xs text-zinc-600"
                        >No sell orders</span
                    >
                </div>
                <OrderbookRow
                    v-for="level in orderbook?.asks?.slice().reverse()"
                    :key="level.price"
                    :level="level"
                    side="ask"
                />
            </div>

            <!-- Spread Indicator -->
            <div class="border-b border-zinc-800 bg-zinc-800/30 px-4 py-2">
                <div class="flex items-center justify-center gap-2">
                    <span
                        class="font-mono text-[10px] tracking-wider text-zinc-600 uppercase"
                        >Spread</span
                    >
                    <span
                        v-if="orderbook?.asks?.[0] && orderbook?.bids?.[0]"
                        class="font-mono text-xs text-zinc-400"
                    >
                        ${{
                            formatUsd(
                                parseFloat(orderbook.asks[0].price) -
                                    parseFloat(orderbook.bids[0].price),
                            )
                        }}
                    </span>
                    <span v-else class="font-mono text-xs text-zinc-600"
                        >—</span
                    >
                </div>
            </div>

            <!-- Bids (Buy Orders) - Green -->
            <div ref="bidsContainer">
                <div
                    v-if="!orderbook?.bids?.length"
                    class="px-4 py-8 text-center"
                >
                    <span class="font-mono text-xs text-zinc-600"
                        >No buy orders</span
                    >
                </div>
                <OrderbookRow
                    v-for="level in orderbook?.bids"
                    :key="level.price"
                    :level="level"
                    side="bid"
                />
            </div>
        </div>

        <!-- Footer -->
        <div class="shrink-0 border-t border-zinc-800 px-4 py-2">
            <div class="flex items-center justify-between">
                <span class="font-mono text-[10px] text-zinc-600">
                    {{ connected ? 'Live updates' : 'Connecting...' }}
                </span>
                <button
                    @click="emit('refresh')"
                    class="flex items-center gap-1 font-mono text-[10px] text-zinc-500 transition-colors hover:text-emerald-400"
                >
                    <RefreshCw class="h-3 w-3" />
                    REFRESH
                </button>
            </div>
        </div>
    </div>
</template>
