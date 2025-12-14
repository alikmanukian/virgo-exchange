<script setup lang="ts">
import { formatAmount, formatUsd } from '@/lib/utils';
import type { OrderbookLevel } from '@/types/trading';
import { computed } from 'vue';

interface Props {
    level: OrderbookLevel;
    side: 'ask' | 'bid';
}

const props = defineProps<Props>();

const colorClasses = computed(() => {
    return props.side === 'ask'
        ? {
              depthBar: 'bg-red-500/10',
              price: 'text-red-400',
          }
        : {
              depthBar: 'bg-emerald-500/10',
              price: 'text-emerald-400',
          };
});
</script>

<template>
    <div class="relative grid grid-cols-3 px-4 py-1.5 hover:bg-zinc-800/50">
        <!-- Depth Bar -->
        <div
            class="absolute inset-y-0 right-0 w-full"
            :class="colorClasses.depthBar"
        ></div>

        <span
            class="relative font-mono text-xs font-medium"
            :class="colorClasses.price"
        >
            ${{ formatUsd(level.price) }}
        </span>
        <span class="relative text-right font-mono text-xs text-zinc-400">
            {{ formatAmount(level.amount) }}
        </span>
        <span class="relative text-right font-mono text-xs text-zinc-500">
            ${{ formatUsd(level.total) }}
        </span>
    </div>
</template>
