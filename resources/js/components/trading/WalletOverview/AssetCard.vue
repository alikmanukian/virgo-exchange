<script setup lang="ts">
import { formatCrypto } from '@/lib/utils';
import type { Asset } from '@/types/trading';
import { computed } from 'vue';

interface Props {
    asset: Asset | undefined;
    name: string;
    symbol: string;
    icon: string;
    colorScheme: 'amber' | 'indigo';
    isLoaded: boolean;
    transitionDelay: string;
}

const props = defineProps<Props>();

const amount = computed(() => {
    return props.asset ? formatCrypto(props.asset.amount) : '0';
});

const colorClasses = computed(() => {
    const schemes = {
        amber: {
            border: 'hover:border-amber-500/30',
            glow: 'bg-amber-500/10',
            gradient: 'from-amber-400 to-amber-600',
            shadow: 'shadow-amber-500/20',
        },
        indigo: {
            border: 'hover:border-indigo-500/30',
            glow: 'bg-indigo-500/10',
            gradient: 'from-indigo-400 to-indigo-600',
            shadow: 'shadow-indigo-500/20',
        },
    };
    return schemes[props.colorScheme];
});
</script>

<template>
    <div
        class="asset-card group/asset relative overflow-hidden rounded-lg border border-zinc-800/50 bg-zinc-800/30 p-3 transition-all duration-300 hover:bg-zinc-800/50"
        :class="[
            colorClasses.border,
            isLoaded ? 'translate-x-0 opacity-100' : '-translate-x-4 opacity-0',
        ]"
        :style="{ transitionDelay }"
    >
        <!-- Background glow -->
        <div
            class="pointer-events-none absolute -top-8 -right-8 h-24 w-24 rounded-full opacity-50 blur-2xl transition-opacity duration-300 group-hover/asset:opacity-100"
            :class="colorClasses.glow"
        ></div>

        <div class="relative flex items-center gap-3">
            <div
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br shadow-lg"
                :class="[colorClasses.gradient, colorClasses.shadow]"
            >
                <span class="font-display text-lg font-bold text-white">
                    {{ icon }}
                </span>
            </div>
            <div
                class="font-display flex-1 text-sm font-semibold text-zinc-100"
            >
                {{ name }}
            </div>
            <div class="flex shrink-0 items-baseline gap-1.5">
                <span
                    class="font-mono text-lg font-bold text-zinc-100 tabular-nums"
                >
                    {{ amount }}
                </span>
                <span class="font-mono text-xs text-zinc-500">{{
                    symbol
                }}</span>
            </div>
        </div>
    </div>
</template>

<style scoped>
.font-display {
    font-family: 'Space Grotesk', system-ui, sans-serif;
}

.asset-card {
    backdrop-filter: blur(8px);
}
</style>
