<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    Check,
    Mail,
    MapPin,
    Phone,
    ShieldCheck,
    Sparkles,
    UserRound,
} from '@lucide/vue';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { home } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Selamat datang kembali',
        description: 'Masuk untuk lanjut ngemil bareng Cemil.in',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
    showRegister?: boolean;
}>();

const isRegistering = ref(false);
</script>

<template>
    <Head :title="isRegistering ? 'Daftar - Cemil.in' : 'Masuk - Cemil.in'" />

    <div
        class="relative overflow-hidden rounded-[28px] border border-[#eadfd4] bg-white shadow-[0_24px_70px_rgba(78,55,35,0.12)]"
    >
        <div class="grid lg:grid-cols-[0.86fr_1fr]">
            <div
                class="relative hidden overflow-hidden bg-[#292724] p-10 text-white lg:flex lg:min-h-[590px] lg:flex-col lg:justify-between"
            >
                <div
                    class="absolute -top-20 -right-24 h-64 w-64 rounded-full border-[30px] border-[#dca477]/20"
                ></div>
                <div
                    class="absolute -bottom-28 -left-20 h-72 w-72 rounded-full bg-[#dca477]/10 blur-2xl"
                ></div>
                <Link :href="home()" class="relative flex items-center gap-2.5">
                    <span
                        class="flex h-10 w-10 rotate-[-6deg] items-center justify-center rounded-xl bg-[#dca477] text-xl text-[#292724]"
                        >✦</span
                    >
                    <span class="text-[22px] font-bold tracking-[-0.05em]"
                        >cemil<span class="text-[#dca477]">.in</span></span
                    >
                </Link>
                <div class="relative">
                    <div
                        class="mb-6 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#dca477]/15 text-[#e7b386]"
                    >
                        <Sparkles class="h-6 w-6" />
                    </div>
                    <h2
                        class="max-w-sm text-4xl leading-tight font-bold tracking-[-0.05em]"
                    >
                        Camilan enak,<br /><em
                            class="font-serif font-normal text-[#e7b386]"
                            >hari lebih hangat.</em
                        >
                    </h2>
                    <p class="mt-5 max-w-xs text-sm leading-6 text-[#bdb5ad]">
                        Temukan camilan favorit dan buat momen kecil terasa
                        lebih spesial.
                    </p>
                    <div class="mt-8 space-y-3 text-sm text-[#e5ddd5]">
                        <p class="flex items-center gap-3">
                            <span
                                class="flex h-6 w-6 items-center justify-center rounded-full bg-[#dca477]/20 text-[#e7b386]"
                                ><Check class="h-3.5 w-3.5"
                            /></span>
                            Bahan pilihan, rasa konsisten
                        </p>
                        <p class="flex items-center gap-3">
                            <span
                                class="flex h-6 w-6 items-center justify-center rounded-full bg-[#dca477]/20 text-[#e7b386]"
                                ><Check class="h-3.5 w-3.5"
                            /></span>
                            Dikirim fresh setiap hari
                        </p>
                    </div>
                </div>
                <p class="relative text-xs text-[#9a9188]">
                    ♡ Disukai oleh 2.000+ penikmat camilan
                </p>
            </div>

            <div class="p-6 sm:p-10 lg:p-12">
                <div class="mb-8 flex items-center justify-between lg:hidden">
                    <Link :href="home()" class="flex items-center gap-2"
                        ><span
                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#dca477] text-lg"
                            >✦</span
                        ><span class="text-xl font-bold tracking-[-0.05em]"
                            >cemil<span class="text-[#c77e48]">.in</span></span
                        ></Link
                    >
                    <Link
                        :href="home()"
                        class="text-xs font-semibold text-[#8b837b] hover:text-[#c77e48]"
                        >Kembali ke beranda</Link
                    >
                </div>

                <Transition name="form-switch" mode="out-in">
                    <div v-if="!isRegistering" key="login">
                        <div class="mb-8">
                            <p
                                class="mb-2 text-xs font-bold tracking-[0.18em] text-[#c77e48] uppercase"
                            >
                                Senang bertemu lagi
                            </p>
                            <h1
                                class="text-3xl font-bold tracking-[-0.05em] text-[#292724]"
                            >
                                Masuk ke akunmu
                            </h1>
                            <p class="mt-2 text-sm text-[#8b837b]">
                                Lanjutkan perjalanan ngemilmu.
                            </p>
                        </div>
                        <p
                            v-if="status"
                            class="mb-5 rounded-xl bg-[#edf5eb] px-4 py-3 text-sm font-medium text-[#477243]"
                        >
                            {{ status }}
                        </p>
                        <Form
                            v-bind="store.form()"
                            :reset-on-success="['password']"
                            v-slot="{ errors, processing }"
                            class="space-y-5"
                        >
                            <div class="space-y-2">
                                <Label for="email" class="text-[#5f5953]"
                                    >Alamat email</Label
                                >
                                <div class="relative">
                                    <Mail
                                        class="absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-[#aaa098]"
                                    /><Input
                                        id="email"
                                        type="email"
                                        name="email"
                                        required
                                        autofocus
                                        autocomplete="email"
                                        placeholder="nama@email.com"
                                        class="h-12 rounded-xl border-[#e5ddd4] bg-[#fcfaf7] pl-10 focus-visible:border-[#dca477] focus-visible:ring-[#dca477]/20"
                                    />
                                </div>
                                <InputError :message="errors.email" />
                            </div>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <Label for="password" class="text-[#5f5953]"
                                        >Password</Label
                                    ><Link
                                        v-if="canResetPassword"
                                        :href="request()"
                                        class="text-xs font-semibold text-[#c77e48] hover:underline"
                                        >Lupa password?</Link
                                    >
                                </div>
                                <PasswordInput
                                    id="password"
                                    name="password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="Masukkan password"
                                    class="h-12 rounded-xl border-[#e5ddd4] bg-[#fcfaf7] focus-visible:border-[#dca477] focus-visible:ring-[#dca477]/20"
                                /><InputError :message="errors.password" />
                            </div>
                            <label
                                class="flex items-center gap-3 text-sm text-[#77716b]"
                                ><Checkbox id="remember" name="remember" />
                                Ingat saya di perangkat ini</label
                            >
                            <button
                                type="submit"
                                class="flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-[#292724] text-sm font-semibold text-white shadow-lg shadow-[#292724]/10 transition-all hover:-translate-y-0.5 hover:bg-[#c77e48]"
                                :disabled="processing"
                            >
                                <Spinner v-if="processing" /> Masuk ke Cemil.in
                                <ArrowRight class="h-4 w-4" />
                            </button>
                        </Form>
                        <p
                            v-if="showRegister"
                            class="mt-8 text-center text-sm text-[#8b837b]"
                        >
                            Belum punya akun?
                            <button
                                type="button"
                                class="font-bold text-[#c77e48] hover:underline"
                                @click="isRegistering = true"
                            >
                                Daftar sekarang
                            </button>
                        </p>
                    </div>
                    <div v-else key="register">
                        <div class="mb-8">
                            <p
                                class="mb-2 text-xs font-bold tracking-[0.18em] text-[#c77e48] uppercase"
                            >
                                Mulai ngemil lebih seru
                            </p>
                            <h1
                                class="text-3xl font-bold tracking-[-0.05em] text-[#292724]"
                            >
                                Buat akun baru
                            </h1>
                            <p class="mt-2 text-sm text-[#8b837b]">
                                Daftar gratis dan temukan camilan favoritmu.
                            </p>
                        </div>
                        <Form
                            action="/register"
                            method="post"
                            v-slot="{ errors, processing }"
                            class="space-y-4"
                        >
                            <div class="space-y-2">
                                <Label for="name" class="text-[#5f5953]"
                                    >Nama lengkap</Label
                                >
                                <div class="relative">
                                    <UserRound
                                        class="absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-[#aaa098]"
                                    /><Input
                                        id="name"
                                        type="text"
                                        name="name"
                                        required
                                        autocomplete="name"
                                        placeholder="Nama kamu"
                                        class="h-12 rounded-xl border-[#e5ddd4] bg-[#fcfaf7] pl-10 focus-visible:border-[#dca477] focus-visible:ring-[#dca477]/20"
                                    />
                                </div>
                                <InputError :message="errors.name" />
                            </div>
                            <div class="space-y-2">
                                <Label
                                    for="register-email"
                                    class="text-[#5f5953]"
                                    >Alamat email</Label
                                >
                                <div class="relative">
                                    <Mail
                                        class="absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-[#aaa098]"
                                    /><Input
                                        id="register-email"
                                        type="email"
                                        name="email"
                                        required
                                        autocomplete="email"
                                        placeholder="nama@email.com"
                                        class="h-12 rounded-xl border-[#e5ddd4] bg-[#fcfaf7] pl-10 focus-visible:border-[#dca477] focus-visible:ring-[#dca477]/20"
                                    />
                                </div>
                                <InputError :message="errors.email" />
                            </div>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="whatsapp" class="text-[#5f5953]"
                                        >Nomor WhatsApp</Label
                                    >
                                    <div class="relative">
                                        <Phone
                                            class="absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-[#aaa098]"
                                        /><Input
                                            id="whatsapp"
                                            type="tel"
                                            name="whatsapp"
                                            required
                                            autocomplete="tel"
                                            placeholder="08xxxxxxxxxx"
                                            class="h-12 rounded-xl border-[#e5ddd4] bg-[#fcfaf7] pl-10 focus-visible:border-[#dca477] focus-visible:ring-[#dca477]/20"
                                        />
                                    </div>
                                    <InputError :message="errors.whatsapp" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="alamat" class="text-[#5f5953]"
                                        >Alamat pengiriman</Label
                                    >
                                    <div class="relative">
                                        <MapPin
                                            class="absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-[#aaa098]"
                                        /><Input
                                            id="alamat"
                                            type="text"
                                            name="alamat"
                                            required
                                            autocomplete="street-address"
                                            placeholder="Alamat lengkap"
                                            class="h-12 rounded-xl border-[#e5ddd4] bg-[#fcfaf7] pl-10 focus-visible:border-[#dca477] focus-visible:ring-[#dca477]/20"
                                        />
                                    </div>
                                    <InputError :message="errors.alamat" />
                                </div>
                            </div>
                            <div class="space-y-2">
                                <Label
                                    for="register-password"
                                    class="text-[#5f5953]"
                                    >Password</Label
                                ><PasswordInput
                                    id="register-password"
                                    name="password"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Minimal 8 karakter"
                                    class="h-12 rounded-xl border-[#e5ddd4] bg-[#fcfaf7] focus-visible:border-[#dca477] focus-visible:ring-[#dca477]/20"
                                /><InputError :message="errors.password" />
                            </div>
                            <div class="space-y-2">
                                <Label
                                    for="password_confirmation"
                                    class="text-[#5f5953]"
                                    >Ulangi password</Label
                                ><PasswordInput
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Ketik ulang password"
                                    class="h-12 rounded-xl border-[#e5ddd4] bg-[#fcfaf7] focus-visible:border-[#dca477] focus-visible:ring-[#dca477]/20"
                                />
                            </div>
                            <button
                                type="submit"
                                class="flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-[#292724] text-sm font-semibold text-white shadow-lg shadow-[#292724]/10 transition-all hover:-translate-y-0.5 hover:bg-[#c77e48]"
                                :disabled="processing"
                            >
                                <Spinner v-if="processing" /> Buat akun
                                <ArrowRight class="h-4 w-4" />
                            </button>
                        </Form>
                        <p class="mt-8 text-center text-sm text-[#8b837b]">
                            Sudah punya akun?
                            <button
                                type="button"
                                class="font-bold text-[#c77e48] hover:underline"
                                @click="isRegistering = false"
                            >
                                Masuk di sini
                            </button>
                        </p>
                    </div>
                </Transition>
                <div
                    class="mt-8 flex items-center justify-center gap-2 text-[11px] text-[#aaa098]"
                >
                    <ShieldCheck class="h-3.5 w-3.5" /> Data kamu aman bersama
                    kami
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.form-switch-enter-active,
.form-switch-leave-active {
    transition:
        opacity 0.2s ease,
        transform 0.2s ease;
}
.form-switch-enter-from {
    opacity: 0;
    transform: translateX(12px);
}
.form-switch-leave-to {
    opacity: 0;
    transform: translateX(-12px);
}
</style>
