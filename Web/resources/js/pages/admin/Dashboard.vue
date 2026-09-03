<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    BarChart3,
    ClipboardList,
    Coins,
    Package,
    ArrowUpRight,
} from '@lucide/vue';
import AppLayout from '@/layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });
defineProps<{
    stats: {
        orders: number;
        pendingOrders: number;
        items: number;
        revenue: number;
    };
    recentOrders: Array<{ id_order: number; status_pesanan: string }>;
}>();
</script>

<template>
    <Head title="Admin Dashboard" />
    <div
        class="min-h-screen bg-[#fcfaf7] px-5 py-8 text-[#292724] sm:px-8 lg:py-12"
    >
        <div class="mx-auto max-w-6xl space-y-8">
            <div
                class="rounded-3xl bg-[#292724] p-7 text-white shadow-xl sm:p-9"
            >
                <p
                    class="text-xs font-semibold tracking-[0.2em] text-[#dca477] uppercase"
                >
                    Cemil.in Admin
                </p>
                <div class="mt-3 flex items-end justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight">
                            Ringkasan toko
                        </h1>
                        <p class="mt-2 text-sm text-[#d8ccc0]">
                            Pantau performa pesanan dan katalog hari ini.
                        </p>
                    </div>
                    <BarChart3
                        class="hidden h-12 w-12 text-[#dca477] sm:block"
                    />
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="card in [
                        {
                            label: 'Total pesanan',
                            value: stats.orders,
                            icon: ClipboardList,
                        },
                        {
                            label: 'Sedang diproses',
                            value: stats.pendingOrders,
                            icon: Package,
                        },
                        {
                            label: 'Menu tersedia',
                            value: stats.items,
                            icon: BarChart3,
                        },
                        {
                            label: 'Pendapatan',
                            value: `Rp ${stats.revenue.toLocaleString('id-ID')}`,
                            icon: Coins,
                        },
                    ]"
                    :key="card.label"
                    class="rounded-3xl border border-[#eadfd4] bg-white p-5 shadow-[0_8px_30px_rgba(82,59,38,0.06)]"
                >
                    <div class="flex items-center justify-between">
                        <span
                            class="rounded-xl bg-[#f8ead8] p-2.5 text-[#c77e48]"
                            ><component :is="card.icon" class="h-5 w-5" /></span
                        ><ArrowUpRight class="h-4 w-4 text-[#dca477]" />
                    </div>
                    <p class="mt-5 text-sm text-[#77716b]">{{ card.label }}</p>
                    <p class="mt-1 text-2xl font-bold">{{ card.value }}</p>
                </div>
            </div>
            <div
                class="rounded-3xl border border-[#eadfd4] bg-white p-6 shadow-[0_8px_30px_rgba(82,59,38,0.06)]"
            >
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold">Pesanan terbaru</h2>
                    <ClipboardList class="h-5 w-5 text-[#c77e48]" />
                </div>
                <p
                    v-if="!recentOrders.length"
                    class="mt-6 rounded-2xl bg-[#fcfaf7] p-5 text-sm text-[#77716b]"
                >
                    Belum ada pesanan.
                </p>
                <div
                    v-for="order in recentOrders"
                    :key="order.id_order"
                    class="mt-4 flex items-center justify-between rounded-2xl bg-[#fcfaf7] px-4 py-3 text-sm"
                >
                    <span class="font-medium"
                        >Pesanan #{{ order.id_order }}</span
                    ><span
                        class="rounded-full bg-[#f8ead8] px-3 py-1 text-xs font-semibold text-[#b66f3d] capitalize"
                        >{{ order.status_pesanan }}</span
                    >
                </div>
            </div>
        </div>
    </div>
</template>
