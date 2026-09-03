<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ClipboardList } from '@lucide/vue';

defineOptions({ layout: AppLayout });

defineProps<{
    orders: {
        data: Array<{
            id_order: number;
            status_pesanan: string;
            status_pembayaran: string;
            payment_type?: string;
            transaction_id?: string;
            transaction_status?: string;
            total_harga: string | number;
            user?: { name?: string; email?: string };
        }>;
    };
}>();

const formatCurrency = (value: string | number) =>
    Number(value || 0).toLocaleString('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    });
</script>

<template>
    <Head title="Kelola Pesanan" />
    <div
        class="min-h-screen bg-[#fcfaf7] px-5 py-8 text-[#292724] sm:px-8 lg:py-12"
    >
        <div class="mx-auto max-w-7xl space-y-7">
            <div class="flex items-end justify-between">
                <div>
                    <p
                        class="mb-2 text-xs font-semibold tracking-[0.2em] text-[#c77e48] uppercase"
                    >
                        Cemil.in Admin
                    </p>
                    <h1 class="text-3xl font-bold">Kelola Pesanan</h1>
                    <p class="mt-2 text-sm text-[#77716b]">
                        Pantau pembayaran dan proses pemenuhan pesanan.
                    </p>
                </div>
                <ClipboardList
                    class="hidden h-10 w-10 text-[#dca477] sm:block"
                />
            </div>
            <div
                class="overflow-x-auto rounded-3xl border border-[#eadfd4] bg-white shadow-[0_8px_30px_rgba(82,59,38,0.06)]"
            >
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr
                            class="border-b border-[#eadfd4] bg-[#292724] text-white"
                        >
                            <th class="p-4">Order</th>
                            <th class="p-4">Pelanggan</th>
                            <th class="p-4">Status</th>
                            <th class="p-4">Pembayaran</th>
                            <th class="p-4">Metode</th>
                            <th class="p-4">Transaksi</th>
                            <th class="p-4 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="order in orders.data"
                            :key="order.id_order"
                            class="border-b border-[#f0e7de] transition-colors last:border-0 hover:bg-[#fff3e8]"
                        >
                            <td class="p-4">#{{ order.id_order }}</td>
                            <td class="p-4">
                                {{ order.user?.name || 'Customer' }}
                            </td>
                            <td class="p-4">{{ order.status_pesanan }}</td>
                            <td class="p-4">{{ order.status_pembayaran }}</td>
                            <td class="p-4">{{ order.payment_type || '-' }}</td>
                            <td class="p-4">
                                {{
                                    order.transaction_id ||
                                    order.transaction_status ||
                                    '-'
                                }}
                            </td>
                            <td class="p-4 text-right">
                                {{ formatCurrency(order.total_harga) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
