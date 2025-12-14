<script setup lang="ts">
import AssetCard from '@/components/trading/WalletOverview/AssetCard.vue';
import { formatUsd } from '@/lib/utils';
import type { Asset } from '@/types/trading';
import { Info, Wallet } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

interface Props {
    balance: string;
    assets: Asset[];
}

const props = defineProps<Props>();

const isLoaded = ref(false);
onMounted(() => {
    setTimeout(() => {
        isLoaded.value = true;
    }, 100);
});

const getAsset = (symbol: string) => {
    return props.assets.find((a) => a.symbol === symbol);
};

const assetConfigs = [
    {
        symbol: 'BTC',
        name: 'Bitcoin',
        icon: '₿',
        colorScheme: 'amber' as const,
        transitionDelay: '150ms',
    },
    {
        symbol: 'ETH',
        name: 'Ethereum',
        icon: 'Ξ',
        colorScheme: 'indigo' as const,
        transitionDelay: '250ms',
    },
];
</script>

<template>
    <div
        class="wallet-container group relative overflow-hidden rounded-xl border border-zinc-800/80 bg-gradient-to-b from-zinc-900 via-zinc-900/95 to-zinc-950"
    >
        <!-- Animated background grid -->
        <div class="pointer-events-none absolute inset-0 opacity-[0.03]">
            <div
                class="absolute inset-0"
                style="
                    background-image:
                        linear-gradient(
                            rgba(255, 255, 255, 0.1) 1px,
                            transparent 1px
                        ),
                        linear-gradient(
                            90deg,
                            rgba(255, 255, 255, 0.1) 1px,
                            transparent 1px
                        );
                    background-size: 20px 20px;
                "
            ></div>
        </div>

        <!-- Glow effect on hover -->
        <div
            class="pointer-events-none absolute -inset-px rounded-xl bg-gradient-to-r from-emerald-500/0 via-emerald-500/10 to-emerald-500/0 opacity-0 blur-xl transition-opacity duration-500 group-hover:opacity-100"
        ></div>

        <!-- Header -->
        <div class="relative border-b border-zinc-800/60 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div
                        class="absolute -inset-1 rounded-lg bg-emerald-500/20 blur-sm"
                    ></div>
                    <div
                        class="relative flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-500 to-emerald-600"
                    >
                        <Wallet
                            class="h-4 w-4 text-white"
                            :stroke-width="2.5"
                        />
                    </div>
                </div>
                <div>
                    <h2
                        class="font-display text-sm font-semibold tracking-tight text-zinc-100"
                    >
                        Profile
                    </h2>
                    <p
                        class="font-mono text-[10px] tracking-widest text-zinc-500 uppercase"
                    >
                        Real-time balance
                    </p>
                </div>
            </div>
        </div>

        <!-- USD Balance - Hero Section -->
        <div class="relative border-b border-zinc-800/60 p-5">
            <div
                class="absolute top-4 right-4 font-mono text-[10px] tracking-widest text-zinc-600 uppercase"
            >
                USD
            </div>
            <div
                class="mb-1 font-mono text-[10px] tracking-widest text-zinc-500 uppercase"
            >
                Available Cash
            </div>
            <div
                class="flex items-baseline gap-1 transition-all duration-700"
                :class="
                    isLoaded
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-2 opacity-0'
                "
            >
                <span class="font-mono text-sm text-zinc-500">$</span>
                <span
                    class="font-display bg-linear-to-r from-zinc-100 to-zinc-300 bg-clip-text text-4xl font-bold tracking-tight text-transparent"
                >
                    {{ formatUsd(balance) }}
                </span>
            </div>
            <!-- Decorative line -->
            <div
                class="mt-4 h-px bg-linear-to-r from-transparent via-zinc-700 to-transparent"
            ></div>
        </div>

        <!-- Assets Section -->
        <div class="relative p-5">
            <div class="mb-4 flex items-center justify-between">
                <span
                    class="font-mono text-[10px] tracking-widest text-zinc-500 uppercase"
                    >Crypto Holdings</span
                >
                <span class="font-mono text-[10px] text-zinc-600"
                    >{{ props.assets.length }} assets</span
                >
            </div>

            <div class="space-y-3">
                <AssetCard
                    v-for="config in assetConfigs"
                    :key="config.symbol"
                    :asset="getAsset(config.symbol)"
                    :name="config.name"
                    :symbol="config.symbol"
                    :icon="config.icon"
                    :color-scheme="config.colorScheme"
                    :is-loaded="isLoaded"
                    :transition-delay="config.transitionDelay"
                />
            </div>
        </div>

        <!-- Footer -->
        <div class="relative border-t border-zinc-800/60 px-5 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <Info class="h-3.5 w-3.5 text-zinc-600" :stroke-width="2" />
                    <span
                        class="font-mono text-[10px] tracking-wider text-zinc-500"
                        >Trading Fee</span
                    >
                </div>
                <div class="flex items-center gap-1">
                    <span class="font-mono text-sm font-semibold text-zinc-300"
                        >1.5</span
                    >
                    <span class="font-mono text-xs text-zinc-500">%</span>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.font-display {
    font-family: 'Space Grotesk', system-ui, sans-serif;
}

.wallet-container {
    box-shadow:
        0 0 0 1px rgba(255, 255, 255, 0.03),
        0 4px 6px -1px rgba(0, 0, 0, 0.3),
        0 2px 4px -2px rgba(0, 0, 0, 0.2);
}
</style>
