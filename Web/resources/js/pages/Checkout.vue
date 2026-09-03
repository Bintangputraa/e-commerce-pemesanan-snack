<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { home } from '@/routes';

defineOptions({ layout: AppLayout });

type CheckoutItem = {
    id: number;
    name: string;
    price: number;
    quantity: number;
};
const props = defineProps<{
    items: CheckoutItem[];
    savedAddress: string | null;
    minimumDate: string;
}>();
const isSubmitting = ref(false);

async function submitCheckout(event: Event) {
    const form = event.target as HTMLFormElement;
    if (!form) {
        return;
    }

    const csrfElement = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfElement?.getAttribute('content') ?? '';

    isSubmitting.value = true;

    try {
        const response = await fetch(form.action || '/checkout', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: new FormData(form),
            credentials: 'same-origin',
        });

        const payload = await response.json().catch(() => ({}));

        if (!response.ok) {
            const message =
                payload?.message ?? 'Checkout gagal, silakan coba lagi.';
            window.alert(message);
            return;
        }

        const snap = (window as any).snap;

        if (payload.snap_token && snap?.pay) {
            snap.pay(payload.snap_token, {
                onSuccess: () => {
                    window.location.href = '/orders/history';
                },
                onPending: () => {
                    window.location.href = '/orders/history';
                },
                onError: () => {
                    window.location.href = '/orders/history';
                },
                onClose: () => {
                    window.location.href = '/orders/history';
                },
            });

            return;
        }

        window.alert(
            'Midtrans Snap belum termuat atau token pembayaran tidak tersedia.',
        );
    } catch (error) {
        console.error(error);
        window.alert('Gagal memproses checkout. Silakan coba lagi.');
    } finally {
        isSubmitting.value = false;
    }
}
</script>

<template>
    <Head title="Checkout" />
    <div class="min-h-screen bg-[#fcfaf7] px-5 py-10 text-[#292724] sm:px-8">
        <div class="mx-auto max-w-5xl">
            <Link :href="home()" class="text-sm font-semibold text-[#c77e48]"
                >← Kembali ke menu</Link
            >
            <h1 class="mt-5 text-3xl font-bold tracking-tight">Checkout</h1>
            <p class="mt-2 text-sm text-[#77716b]">
                Pesanan pre-order paling cepat diproses besok.
            </p>
            <form
                :action="'/checkout'"
                method="post"
                class="mt-8 grid gap-6 lg:grid-cols-[1fr_0.8fr]"
                @submit.prevent="submitCheckout"
            >
                <section
                    class="space-y-5 rounded-3xl border border-[#eadfd4] bg-[#fffdfb] p-6 shadow-sm"
                >
                    <div>
                        <h2 class="font-semibold">Alamat pengiriman</h2>
                        <div
                            class="mt-3 flex items-start gap-3 rounded-2xl bg-[#fff3e8] p-4"
                        >
                            <input
                                id="saved-address"
                                type="radio"
                                name="address_option"
                                value="saved"
                                :checked="!!props.savedAddress"
                                class="mt-1 accent-[#c77e48]"
                            />
                            <label for="saved-address" class="text-sm"
                                ><strong>Gunakan alamat tersimpan</strong
                                ><br /><span class="text-[#77716b]">{{
                                    props.savedAddress ||
                                    'Belum ada alamat tersimpan'
                                }}</span></label
                            >
                        </div>
                        <div class="mt-3 grid gap-2">
                            <Label for="alamat_pengiriman"
                                >Alamat baru / alamat pengiriman</Label
                            >
                            <textarea
                                id="alamat_pengiriman"
                                name="alamat_pengiriman"
                                :placeholder="
                                    props.savedAddress ||
                                    'Masukkan alamat lengkap'
                                "
                                :value="props.savedAddress || ''"
                                required
                                rows="3"
                                class="w-full rounded-xl border border-[#e5ddd4] bg-white px-3 py-2 text-sm outline-none focus:border-[#dca477]"
                            ></textarea>
                        </div>
                    </div>
                    <div class="grid gap-2">
                        <Label for="tanggal_pesan">Waktu pemesanan</Label>
                        <Input
                            id="tanggal_pesan"
                            name="tanggal_pesan"
                            type="date"
                            :min="props.minimumDate"
                            :value="props.minimumDate"
                            required
                        />
                        <p class="text-xs text-[#8b837b]">
                            Tidak dapat memilih hari ini.
                        </p>
                    </div>
                    <div>
                        <h2 class="font-semibold">Item pesanan</h2>
                        <div
                            v-for="(item, index) in props.items"
                            :key="item.id"
                            class="mt-3 rounded-2xl border border-[#eee4da] p-4"
                        >
                            <div class="flex justify-between text-sm">
                                <span
                                    >{{ item.name }} × {{ item.quantity }}</span
                                >
                                <strong
                                    >Rp
                                    {{
                                        (
                                            item.price * item.quantity
                                        ).toLocaleString('id-ID')
                                    }}</strong
                                >
                            </div>
                            <input
                                :name="`items[${index}][id]`"
                                type="hidden"
                                :value="item.id"
                            />
                            <input
                                :name="`items[${index}][quantity]`"
                                type="hidden"
                                :value="item.quantity"
                            />
                            <div class="mt-3">
                                <Label :for="`note-${item.id}`" class="text-xs"
                                    >Catatan item (opsional)</Label
                                >
                                <Input
                                    :id="`note-${item.id}`"
                                    :name="`items[${index}][catatan]`"
                                    class="mt-1"
                                    placeholder="Contoh: level pedas sedang"
                                />
                            </div>
                        </div>
                    </div>
                    <div class="grid gap-2">
                        <Label for="kode_voucher">Voucher</Label>
                        <Input
                            id="kode_voucher"
                            name="kode_voucher"
                            placeholder="Masukkan kode voucher"
                        />
                        <p class="text-xs text-[#8b837b]">
                            Coba kode CEMIL10 untuk diskon 10%.
                        </p>
                    </div>
                </section>
                <aside
                    class="h-fit rounded-3xl border border-[#eadfd4] bg-white p-6 shadow-sm"
                >
                    <h2 class="font-semibold">Pembayaran</h2>
                    <p class="mt-2 text-sm text-[#77716b]">
                        Pembayaran aman melalui Midtrans Snap.
                    </p>
                    <div
                        class="my-6 rounded-2xl bg-[#f8ead8] p-4 text-sm text-[#71492f]"
                    >
                        Setelah pesanan dibuat, status pembayaran akan diproses
                        melalui payment gateway Midtrans.
                    </div>
                    <Button
                        type="submit"
                        :disabled="isSubmitting || !props.items.length"
                        class="w-full rounded-full bg-[#292724] py-3 text-white hover:bg-[#c77e48]"
                    >
                        {{
                            isSubmitting
                                ? 'Memproses...'
                                : 'Buat pesanan & bayar'
                        }}
                    </Button>
                </aside>
            </form>
        </div>
    </div>
</template>
