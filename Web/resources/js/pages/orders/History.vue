<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';
import {
    ArrowRight,
    CalendarDays,
    CreditCard,
    MapPin,
    PackageOpen,
    ReceiptText,
    Tag,
    X,
} from '@lucide/vue';
import Heading from '@/components/Heading.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';

defineOptions({ layout: AppLayout });

type OrderItem = {
    nama?: string;
    item?: { nama?: string };
    jumlah?: number;
    harga_satuan?: string | number;
    catatan?: string;
};

type Order = {
    id_order: number;
    total_harga: string | number;
    status_pembayaran: string;
    status_pesanan: string;
    payment_type?: string;
    midtrans_order_id?: string;
    transaction_id?: string;
    payment_url?: string;
    snap_token?: string;
    created_at: string;
    tanggal_pesan?: string;
    alamat_pengiriman?: string;
    kode_voucher?: string;
    diskon?: string | number;
    user?: { name?: string; email?: string };
    orderDetails?: OrderItem[];
};

const props = defineProps<{
    title: string;
    orders: Order[];
}>();
const orders = ref([...props.orders]);
const selectedOrder = ref<Order | null>(null);
let statusTimer: ReturnType<typeof setInterval> | undefined;

const paymentBadgeClass = (status: string) => {
    const normalized = status.toLowerCase();

    if (normalized === 'paid') {
        return 'bg-emerald-100 text-emerald-800';
    }

    if (normalized === 'pending') {
        return 'bg-amber-100 text-amber-800';
    }

    return 'bg-rose-100 text-rose-800';
};

const formatCurrency = (value: string | number) =>
    Number(value || 0).toLocaleString('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    });

const paymentLabel = (type?: string) => {
    if (!type) {
        return 'Belum dipilih';
    }

    return type.toUpperCase();
};

const openDetails = (order: Order) => {
    selectedOrder.value = order;
};

const closeDetails = () => {
    selectedOrder.value = null;
};

const payNow = async (order: Order) => {
    const snap = (window as any).snap;

    if (!snap) {
        if (order.payment_url) {
            window.open(order.payment_url, '_blank');
        }

        return;
    }

    const csrfElement = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfElement?.getAttribute('content') ?? '';

    const response = await fetch(`/orders/${order.id_order}/pay`, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
        },
        credentials: 'same-origin',
    });

    const payload = await response.json().catch(() => ({}));

    if (!response.ok || !payload.snap_token) {
        if (payload?.message) {
            window.alert(payload.message);
        }

        return;
    }

    snap.pay(payload.snap_token, {
        onSuccess: () => window.location.reload(),
        onPending: () => window.location.reload(),
        onError: () => window.location.reload(),
        onClose: () => window.location.reload(),
    });
};

const synchronizePaymentStatuses = async () => {
    await Promise.all(
        orders.value
            .filter(
                (order) =>
                    ['pending', 'paid'].includes(order.status_pembayaran) &&
                    order.midtrans_order_id,
            )
            .map(async (order) => {
                const response = await fetch(
                    `/orders/${order.id_order}/payment-status`,
                    {
                        headers: { Accept: 'application/json' },
                        credentials: 'same-origin',
                    },
                );

                if (!response.ok) {
                    return;
                }

                const payload = await response.json();
                const index = orders.value.findIndex(
                    (item) => item.id_order === order.id_order,
                );

                if (index !== -1 && payload.order) {
                    orders.value[index] = {
                        ...orders.value[index],
                        ...payload.order,
                    };
                }
            }),
    );
};

onMounted(() => {
    synchronizePaymentStatuses();
    statusTimer = setInterval(synchronizePaymentStatuses, 5000);
});

onUnmounted(() => {
    if (statusTimer) {
        clearInterval(statusTimer);
    }
});
</script>

<template>
    <Head :title="title" />
    <div
        class="min-h-screen overflow-x-clip bg-[#fcfaf7] px-5 py-8 text-[#292724] sm:px-8 lg:py-12"
    >
        <div class="mx-auto max-w-5xl">
            <div class="mb-8 flex items-end justify-between gap-4">
                <div>
                    <p
                        class="mb-2 text-xs font-semibold tracking-[0.2em] text-[#c77e48] uppercase"
                    >
                        Cemil.in
                    </p>
                    <Heading
                        variant="default"
                        :title="title"
                        description="Pantau perjalanan pesanan snack favoritmu."
                    />
                </div>
                <div
                    class="hidden rounded-2xl bg-[#f8ead8] p-3 text-[#c77e48] sm:block"
                >
                    <PackageOpen class="h-6 w-6" />
                </div>
            </div>
            <div v-if="orders.length" class="grid gap-4">
                <div
                    v-for="order in orders"
                    :key="order.id_order"
                    class="group cursor-pointer overflow-hidden rounded-3xl border border-[#eadfd4] bg-white shadow-[0_8px_30px_rgba(82,59,38,0.06)] transition duration-300 hover:-translate-y-0.5 hover:shadow-[0_14px_35px_rgba(82,59,38,0.12)]"
                    @click="openDetails(order)"
                >
                    <div
                        class="h-1.5 bg-gradient-to-r from-[#dca477] via-[#f0c49e] to-[#f8ead8]"
                    ></div>
                    <div class="p-5 sm:p-6">
                        <div
                            class="flex flex-wrap items-start justify-between gap-4"
                        >
                            <div>
                                <div
                                    class="flex items-center gap-2 text-xs font-semibold tracking-wider text-[#a39a91] uppercase"
                                >
                                    <ReceiptText class="h-3.5 w-3.5" />
                                    Order #{{ order.id_order }}
                                </div>
                                <p class="mt-2 text-sm text-[#77716b]">
                                    {{ order.created_at }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span
                                    class="rounded-full bg-[#292724] px-3 py-1.5 text-xs font-semibold text-white capitalize"
                                    >{{ order.status_pesanan }}</span
                                >
                                <span
                                    :class="[
                                        'rounded-full px-3 py-1.5 text-xs font-semibold capitalize',
                                        paymentBadgeClass(
                                            order.status_pembayaran,
                                        ),
                                    ]"
                                >
                                    {{ order.status_pembayaran }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-5 space-y-2 text-sm text-[#625c56]">
                            <div
                                v-if="order.orderDetails?.length"
                                class="space-y-1"
                            >
                                <div
                                    v-for="detail in order.orderDetails"
                                    :key="`${order.id_order}-${detail.item?.nama ?? detail.nama}`"
                                    class="flex justify-between gap-3"
                                >
                                    <span
                                        >{{
                                            detail.item?.nama ??
                                            detail.nama ??
                                            'Produk'
                                        }}
                                        <span class="text-[#a39a91]"
                                            >× {{ detail.jumlah ?? 1 }}</span
                                        ></span
                                    >
                                    <span class="font-medium">{{
                                        formatCurrency(
                                            Number(detail.harga_satuan ?? 0) *
                                                (detail.jumlah ?? 1),
                                        )
                                    }}</span>
                                </div>
                            </div>
                        </div>

                        <div
                            class="mt-5 flex flex-wrap items-center justify-between gap-4 border-t border-[#f0e7de] pt-4"
                        >
                            <div>
                                <p
                                    class="text-xs tracking-wider text-[#a39a91] uppercase"
                                >
                                    Total pembayaran
                                </p>
                                <div
                                    class="mt-1 text-xl font-bold text-[#292724]"
                                >
                                    {{ formatCurrency(order.total_harga) }}
                                </div>
                            </div>
                            <Button
                                v-if="order.status_pembayaran === 'pending'"
                                variant="default"
                                @click.stop="payNow(order)"
                                class="rounded-full bg-[#292724] px-5 hover:bg-[#c77e48]"
                            >
                                Bayar Sekarang
                                <ArrowRight class="ml-2 h-4 w-4" />
                            </Button>
                            <span
                                v-else
                                class="flex items-center gap-1 text-sm font-semibold text-[#c77e48]"
                                >Lihat detail
                                <ArrowRight
                                    class="h-4 w-4 transition-transform group-hover:translate-x-1"
                            /></span>
                        </div>
                    </div>
                </div>
            </div>
            <div
                v-else
                class="rounded-3xl border border-dashed border-[#decdbd] bg-white/60 p-12 text-center"
            >
                <PackageOpen class="mx-auto h-10 w-10 text-[#dca477]" />
                <p class="mt-4 font-semibold text-[#625c56]">
                    Belum ada pesanan di sini
                </p>
                <p class="mt-1 text-sm text-[#a39a91]">
                    Pesanan snack kamu akan muncul di halaman ini.
                </p>
            </div>
        </div>
    </div>

    <div
        v-if="selectedOrder"
        class="fixed inset-0 z-50 flex items-center justify-center bg-[#292724]/55 p-4 backdrop-blur-sm"
        @click.self="closeDetails"
    >
        <div
            class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-3xl border border-[#eadfd4] bg-[#fffdfb] shadow-2xl"
        >
            <div
                class="flex items-start justify-between bg-[#292724] p-6 text-white"
            >
                <div>
                    <p
                        class="text-xs font-semibold tracking-[0.2em] text-[#dca477] uppercase"
                    >
                        Detail pesanan
                    </p>
                    <h2 class="mt-2 text-2xl font-bold">
                        #{{ selectedOrder.id_order }}
                    </h2>
                </div>
                <button
                    type="button"
                    class="rounded-full p-2 text-[#f6eee4] transition hover:bg-white/10"
                    aria-label="Tutup"
                    @click="closeDetails"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>
            <div class="grid gap-3 p-6 text-sm sm:grid-cols-2">
                <div class="rounded-2xl bg-[#f8ead8]/60 p-4">
                    <span class="flex items-center gap-2 text-[#a39a91]"
                        ><CreditCard class="h-4 w-4" /> Pembayaran</span
                    >
                    <p class="mt-1 font-semibold text-[#292724] capitalize">
                        {{ selectedOrder.status_pembayaran }} ·
                        {{ paymentLabel(selectedOrder.payment_type) }}
                    </p>
                </div>
                <div class="rounded-2xl bg-[#f8ead8]/60 p-4">
                    <span class="flex items-center gap-2 text-[#a39a91]"
                        ><CalendarDays class="h-4 w-4" /> Tanggal pesan</span
                    >
                    <p class="mt-1 font-semibold text-[#292724]">
                        {{ selectedOrder.tanggal_pesan || '-' }}
                    </p>
                </div>
                <div>
                    <span class="text-[#a39a91]">Nama pelanggan</span>
                    <p class="mt-1 font-semibold">
                        {{ selectedOrder.user?.name || 'Customer' }}
                    </p>
                </div>
                <div>
                    <span class="text-[#a39a91]">Voucher</span>
                    <p class="mt-1 flex items-center gap-1 font-semibold">
                        <Tag class="h-4 w-4 text-[#c77e48]" />{{
                            selectedOrder.kode_voucher || '-'
                        }}
                    </p>
                </div>
                <div>
                    <span class="text-[#a39a91]">Diskon</span>
                    <p class="mt-1 font-semibold">
                        {{ formatCurrency(selectedOrder.diskon || 0) }}
                    </p>
                </div>
                <div>
                    <span class="text-[#a39a91]">Total</span>
                    <p class="mt-1 text-lg font-bold text-[#c77e48]">
                        {{ formatCurrency(selectedOrder.total_harga) }}
                    </p>
                </div>
                <div class="sm:col-span-2">
                    <span class="flex items-center gap-2 text-[#a39a91]"
                        ><MapPin class="h-4 w-4" /> Alamat pengiriman</span
                    >
                    <p class="mt-1 font-semibold">
                        {{ selectedOrder.alamat_pengiriman || '-' }}
                    </p>
                </div>
                <div class="sm:col-span-2">
                    <span class="text-[#a39a91]">Transaction ID</span>
                    <p class="mt-1 font-mono text-xs font-semibold break-all">
                        {{ selectedOrder.transaction_id || '-' }}
                    </p>
                </div>
            </div>
            <div class="border-t border-[#eadfd4] px-6 py-5">
                <h3 class="font-semibold">Menu pesanan</h3>
                <div
                    v-for="detail in selectedOrder.orderDetails || []"
                    :key="`${selectedOrder.id_order}-${detail.item?.nama ?? detail.nama}`"
                    class="mt-3 rounded-xl bg-[#fcfaf7] p-3 text-sm"
                >
                    <div class="flex justify-between gap-3 font-medium">
                        <span
                            >{{
                                detail.item?.nama ?? detail.nama ?? 'Produk'
                            }}
                            × {{ detail.jumlah ?? 1 }}</span
                        ><span>{{
                            formatCurrency(
                                Number(detail.harga_satuan ?? 0) *
                                    (detail.jumlah ?? 1),
                            )
                        }}</span>
                    </div>
                    <p
                        v-if="detail.catatan"
                        class="mt-1 text-xs text-[#a39a91]"
                    >
                        Catatan: {{ detail.catatan }}
                    </p>
                </div>
            </div>
            <div class="flex justify-end bg-[#fcfaf7] px-6 py-4">
                <Button
                    type="button"
                    variant="outline"
                    class="rounded-full border-[#decdbd]"
                    @click="closeDetails"
                    >Tutup</Button
                >
            </div>
        </div>
    </div>
</template>
