<script setup lang="ts">
import Orderbook from '@/components/trading/Orderbook/Orderbook.vue';
import OrdersList from '@/components/trading/OrdersList.vue';
import PlaceOrderModal from '@/components/trading/PlaceOrderModal.vue';
import WalletOverview from '@/components/trading/WalletOverview/WalletOverview.vue';
import { Button } from '@/components/ui/button';
import { toast } from '@/components/ui/sonner';
import AppLayout from '@/layouts/AppLayout.vue';
import { trading } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import type {
    Asset,
    Order,
    Orderbook as OrderbookType,
    OrderSide,
    TradingSymbol,
} from '@/types/trading';
import { Head, router, usePage } from '@inertiajs/vue3';
import { TrendingDown, TrendingUp } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref, watch } from 'vue';

interface Props {
    balance: string;
    assets: { data: Asset[] };
    orders: { data: Order[] };
    orderbook: OrderbookType;
}

const props = defineProps<Props>();

const page = usePage();
const userId = page.props.auth.user?.id;

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Trading', href: trading().url },
];

const selectedSymbol = ref<TradingSymbol>('BTC');
const isOrderModalOpen = ref(false);
const orderSide = ref<OrderSide>('buy');

const openOrderModal = (side: OrderSide) => {
    orderSide.value = side;
    isOrderModalOpen.value = true;
};

const refreshData = (
    blocks: ('balance' | 'assets' | 'orders' | 'orderbook')[] = [
        'balance',
        'assets',
        'orders',
        'orderbook',
    ],
) => {
    router.reload({
        only: [...blocks, 'flash'],
        data: { symbol: selectedSymbol.value },
    });
};

watch(selectedSymbol, () => {
    refreshData(['orderbook']);
});

const subscribeToUserChannel = () => {
    if (!window.Echo || !userId) return;

    window.Echo.private(`user.${userId}`).listen('.OrderMatched', () => {
        refreshData();
        toast.success('Order fulfilled successfully.');
    });
};

const unsubscribeFromUserChannel = () => {
    if (!window.Echo || !userId) return;
    window.Echo.leave(`user.${userId}`);
};

onMounted(() => {
    subscribeToUserChannel();
});

onUnmounted(() => {
    unsubscribeFromUserChannel();
});
</script>

<template>
    <Head title="Trading" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-[calc(100svh-7rem)] flex-col overflow-hidden bg-zinc-950 p-4 lg:p-6 lg:pb-4"
        >
            <!-- Terminal Header -->
            <div
                class="mb-6 flex shrink-0 items-center justify-between border-b border-zinc-800 pb-4"
            >
                <div class="flex items-center gap-4">
                    <h1
                        class="font-mono text-2xl font-bold tracking-tight text-zinc-100"
                    >
                        VIRGO<span class="text-emerald-500">.</span>EXCHANGE
                    </h1>
                    <div
                        class="flex items-center gap-1 rounded bg-zinc-900 p-1"
                    >
                        <Button
                            v-for="symbol in ['BTC', 'ETH'] as const"
                            :key="symbol"
                            @click="selectedSymbol = symbol"
                            :class="[
                                'h-8 rounded px-4 py-1.5 font-mono text-sm font-medium transition-all',
                                selectedSymbol === symbol
                                    ? 'bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500/20'
                                    : 'bg-transparent text-zinc-500 hover:bg-transparent hover:text-zinc-300',
                            ]"
                        >
                            {{ symbol }}/USD
                        </Button>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <div class="mr-4 flex items-center gap-2">
                        <span
                            class="h-2 w-2 animate-pulse rounded-full bg-emerald-500"
                        ></span>
                        <span class="font-mono text-xs text-zinc-500"
                            >LIVE</span
                        >
                    </div>
                    <Button
                        @click="openOrderModal('buy')"
                        class="bg-emerald-600 font-mono text-sm font-bold hover:bg-emerald-500"
                    >
                        <TrendingUp class="mr-1.5 h-4 w-4" />
                        BUY {{ selectedSymbol }}
                    </Button>
                    <Button
                        @click="openOrderModal('sell')"
                        variant="destructive"
                        class="font-mono text-sm font-bold"
                    >
                        <TrendingDown class="mr-1.5 h-4 w-4" />
                        SELL {{ selectedSymbol }}
                    </Button>
                </div>
            </div>

            <!-- Main Grid -->
            <div class="grid min-h-0 flex-1 gap-4 lg:grid-cols-12 lg:gap-6">
                <!-- Left Panel: Wallet -->
                <div class="lg:col-span-3">
                    <WalletOverview
                        :balance="props.balance"
                        :assets="props.assets.data"
                    />
                </div>

                <!-- Center Panel: Orderbook -->
                <div class="flex min-h-0 flex-col lg:col-span-5">
                    <Orderbook
                        :symbol="selectedSymbol"
                        :orderbook="props.orderbook"
                        @refresh="refreshData(['orderbook'])"
                    />
                </div>

                <!-- Right Panel: Orders -->
                <div class="flex min-h-0 flex-col lg:col-span-4">
                    <OrdersList
                        :orders="props.orders.data"
                        @refresh="refreshData"
                        class="flex min-h-0 flex-1 flex-col"
                    />
                </div>
            </div>
        </div>
    </AppLayout>

    <!-- Place Order Modal -->
    <PlaceOrderModal
        v-model:open="isOrderModalOpen"
        :symbol="selectedSymbol"
        :side="orderSide"
        :balance="props.balance"
        :assets="props.assets.data"
        @success="refreshData"
    />
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap');

.font-mono {
    font-family: 'JetBrains Mono', monospace;
}
</style>
