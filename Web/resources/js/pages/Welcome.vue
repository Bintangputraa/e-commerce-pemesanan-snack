<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    Check,
    ChevronRight,
    Heart,
    Menu,
    Minus,
    Plus,
    Search,
    ShoppingBag,
    Sparkles,
    Star,
    Truck,
    UserRound,
    X,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { dashboard, login } from '@/routes';
import { edit as profileEdit } from '@/routes/profile';

type Product = {
    id: number;
    name: string;
    description: string | null;
    category: string;
    price: number;
    image: string | null;
    tag?: string;
    color: string;
};

const props = defineProps<{ items: Product[]; favoriteIds?: number[] }>();
const page = usePage();
const currentUser = computed(() => page.props.auth?.user);
const products = computed(() =>
    props.items.map((product, index) => ({
        ...product,
        color: [
            '#f8ead8',
            '#fbe4df',
            '#f6eddc',
            '#e7efe6',
            '#e8e5f2',
            '#f5e9d1',
        ][index % 6],
    })),
);
const categoryIcons: Record<string, string> = {
    'Gurih & asin': '🥨',
    Manis: '🍪',
    Pedas: '🌶️',
    'Paket hemat': '🎁',
};
const categories = computed(() => [
    {
        name: 'Semua camilan',
        icon: '✦',
        count: `${products.value.length} produk`,
    },
    ...Array.from(
        new Set(products.value.map((product) => product.category)),
    ).map((name) => ({
        name,
        icon: categoryIcons[name] || '🍿',
        count: `${products.value.filter((product) => product.category === name).length} produk`,
    })),
]);

const activeCategory = ref('Semua camilan');
const searchQuery = ref('');
const cart = ref<Record<number, number>>({});
const isCartOpen = ref(false);
const isMobileMenuOpen = ref(false);
const likedProducts = ref<number[]>(props.favoriteIds ?? []);
const selectedProduct = ref<Product | null>(null);

const filteredProducts = computed(() =>
    products.value.filter((product) => {
        const matchesCategory =
            activeCategory.value === 'Semua camilan' ||
            product.category === activeCategory.value;
        const matchesSearch = product.name
            .toLowerCase()
            .includes(searchQuery.value.toLowerCase());
        return matchesCategory && matchesSearch;
    }),
);

const cartItems = computed(() =>
    products.value
        .filter((product) => cart.value[product.id])
        .map((product) => ({ ...product, quantity: cart.value[product.id] })),
);
const cartCount = computed(() =>
    Object.values(cart.value).reduce((total, quantity) => total + quantity, 0),
);
const cartTotal = computed(() =>
    cartItems.value.reduce(
        (total, item) => total + item.price * item.quantity,
        0,
    ),
);
const checkoutUrl = computed(
    () => `/checkout?cart=${encodeURIComponent(JSON.stringify(cart.value))}`,
);

const formatPrice = (price: number) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(price);

function addToCart(productId: number) {
    cart.value[productId] = (cart.value[productId] || 0) + 1;
}

function updateQuantity(productId: number, change: number) {
    const quantity = (cart.value[productId] || 0) + change;
    if (quantity <= 0) {
        delete cart.value[productId];
        return;
    }
    cart.value[productId] = quantity;
}

function setQuantity(productId: number, value: string | number) {
    const quantity = Math.floor(Number(value));

    if (!Number.isFinite(quantity) || quantity <= 0) {
        delete cart.value[productId];
        return;
    }

    cart.value[productId] = quantity;
}

function toggleLike(productId: number) {
    if (!currentUser.value) {
        router.visit(login());
        return;
    }

    const wasLiked = likedProducts.value.includes(productId);
    likedProducts.value = wasLiked
        ? likedProducts.value.filter((id) => id !== productId)
        : [...likedProducts.value, productId];
    router.post(
        `/favorites/${productId}/toggle`,
        {},
        {
            preserveScroll: true,
            onError: () => {
                likedProducts.value = wasLiked
                    ? [...likedProducts.value, productId]
                    : likedProducts.value.filter((id) => id !== productId);
            },
        },
    );
}

function openProduct(product: Product): void {
    selectedProduct.value = product;
}

function closeProduct(): void {
    selectedProduct.value = null;
}
</script>

<template>
    <Head title="Cemil.in - Teman ngemil setiap hari">
        <meta
            name="description"
            content="Camilan pilihan yang dikirim dengan penuh rasa. Temukan snack favoritmu di Cemil.in."
        />
    </Head>

    <div class="min-h-screen overflow-x-clip bg-[#fcfaf7] text-[#272522]">
        <div
            class="bg-[#292724] px-6 py-2.5 text-center text-xs font-medium tracking-wide text-[#f6eee4]"
        >
            Gratis ongkir untuk pesanan di atas Rp100.000
            <span class="mx-2 text-[#d8a77d]">•</span> Khusus hari ini
        </div>

        <header
            class="sticky top-0 isolate z-30 border-b border-[#e8e1d8]/80 bg-[#fcfaf7]/95 shadow-[0_1px_0_rgba(232,225,216,0.4)] backdrop-blur-md"
        >
            <div
                class="mx-auto flex h-[76px] max-w-7xl items-center justify-between px-5 sm:px-8"
            >
                <a
                    href="#"
                    class="flex items-center gap-2.5"
                    aria-label="Cemil.in beranda"
                >
                    <span
                        class="flex h-10 w-10 rotate-[-6deg] items-center justify-center rounded-xl bg-[#dca477] text-xl shadow-sm"
                        >✦</span
                    >
                    <span class="text-[22px] font-bold tracking-[-0.05em]"
                        >cemil<span class="text-[#c77e48]">.in</span></span
                    >
                </a>

                <nav
                    class="hidden items-center gap-8 text-sm font-medium text-[#77716b] md:flex"
                >
                    <a
                        href="#produk"
                        class="transition-colors hover:text-[#c77e48]"
                        >Menu snack</a
                    >
                    <a
                        href="#cerita"
                        class="transition-colors hover:text-[#c77e48]"
                        >Cerita kami</a
                    >
                    <a
                        href="#bantuan"
                        class="transition-colors hover:text-[#c77e48]"
                        >Bantuan</a
                    >
                </nav>

                <div class="flex items-center gap-2.5">
                    <button
                        class="flex h-10 w-10 items-center justify-center rounded-full border border-[#e5ddd4] text-[#625c56] transition-colors hover:border-[#dca477] hover:text-[#c77e48] md:hidden"
                        aria-label="Buka menu navigasi"
                        :aria-expanded="isMobileMenuOpen"
                        @click="isMobileMenuOpen = !isMobileMenuOpen"
                    >
                        <X v-if="isMobileMenuOpen" class="h-[18px] w-[18px]" />
                        <Menu v-else class="h-[18px] w-[18px]" />
                    </button>
                    <div class="relative hidden lg:block">
                        <Search
                            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-[#9a938c]"
                        />
                        <input
                            v-model="searchQuery"
                            placeholder="Cari camilan..."
                            class="h-10 w-44 rounded-full border border-[#e5ddd4] bg-white pr-3 pl-9 text-sm transition-all outline-none placeholder:text-[#aaa29a] focus:w-56 focus:border-[#dca477] focus:ring-2 focus:ring-[#dca477]/20"
                        />
                    </div>
                    <Link
                        v-if="!currentUser"
                        :href="login()"
                        class="hidden rounded-full px-4 py-2 text-sm font-semibold text-[#625c56] transition-colors hover:text-[#c77e48] sm:block"
                        >Masuk</Link
                    >
                    <Link
                        v-else
                        :href="profileEdit()"
                        class="flex h-10 w-10 items-center justify-center rounded-full border border-[#e5ddd4] bg-white text-sm font-bold text-[#c77e48] transition-colors hover:border-[#dca477]"
                        :aria-label="`Buka profil ${currentUser.name}`"
                        >{{ currentUser.name.charAt(0).toUpperCase() }}</Link
                    >
                    <button
                        class="relative flex h-10 w-10 items-center justify-center rounded-full bg-[#292724] text-white transition-transform hover:scale-105"
                        aria-label="Buka keranjang"
                        @click="isCartOpen = true"
                    >
                        <ShoppingBag class="h-[17px] w-[17px]" />
                        <span
                            v-if="cartCount"
                            class="absolute -top-1 -right-1 flex h-[18px] min-w-[18px] items-center justify-center rounded-full bg-[#dca477] px-1 text-[10px] font-bold text-[#292724]"
                            >{{ cartCount }}</span
                        >
                    </button>
                </div>
            </div>
            <Transition name="mobile-menu">
                <div
                    v-if="isMobileMenuOpen"
                    class="border-t border-[#e8e1d8] bg-[#fcfaf7] px-5 py-4 shadow-sm md:hidden"
                >
                    <nav class="grid gap-1 text-sm font-medium text-[#625c56]">
                        <a
                            href="#produk"
                            class="rounded-xl px-3 py-3 transition-colors hover:bg-[#fff3e8] hover:text-[#c77e48]"
                            @click="isMobileMenuOpen = false"
                            >Menu snack</a
                        >
                        <a
                            href="#cerita"
                            class="rounded-xl px-3 py-3 transition-colors hover:bg-[#fff3e8] hover:text-[#c77e48]"
                            @click="isMobileMenuOpen = false"
                            >Cerita kami</a
                        >
                        <a
                            href="#bantuan"
                            class="rounded-xl px-3 py-3 transition-colors hover:bg-[#fff3e8] hover:text-[#c77e48]"
                            @click="isMobileMenuOpen = false"
                            >Bantuan</a
                        >
                    </nav>
                    <div class="mt-3 flex gap-2">
                        <div class="relative flex-1">
                            <Search
                                class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-[#9a938c]"
                            />
                            <input
                                v-model="searchQuery"
                                placeholder="Cari camilan..."
                                class="h-11 w-full rounded-xl border border-[#e5ddd4] bg-white pr-3 pl-9 text-sm outline-none placeholder:text-[#aaa29a] focus:border-[#dca477] focus:ring-2 focus:ring-[#dca477]/20"
                            />
                        </div>
                        <Link
                            v-if="!currentUser"
                            :href="login()"
                            class="flex h-11 items-center rounded-xl bg-[#292724] px-4 text-sm font-semibold text-white transition-colors hover:bg-[#c77e48]"
                            @click="isMobileMenuOpen = false"
                            >Masuk</Link
                        >
                        <Link
                            v-else
                            :href="profileEdit()"
                            class="flex h-11 items-center gap-2 rounded-xl bg-[#292724] px-4 text-sm font-semibold text-white transition-colors hover:bg-[#c77e48]"
                            @click="isMobileMenuOpen = false"
                            ><UserRound class="h-4 w-4" /> Profil</Link
                        >
                    </div>
                </div>
            </Transition>
        </header>

        <main>
            <section
                class="relative mx-auto max-w-7xl px-5 pt-12 pb-16 sm:px-8 sm:pt-20 lg:pb-24"
            >
                <div
                    class="grid items-center gap-12 lg:grid-cols-[1fr_0.9fr] lg:gap-20"
                >
                    <div class="relative z-10 max-w-xl">
                        <div
                            class="mb-6 inline-flex items-center gap-2 rounded-full border border-[#ecd3bb] bg-[#fff8f0] px-3.5 py-2 text-xs font-semibold text-[#b66f3d]"
                        >
                            <Sparkles class="h-3.5 w-3.5" /> Snack pilihan, mood
                            lebih baik
                        </div>
                        <h1
                            class="text-5xl leading-[1.03] font-bold tracking-[-0.065em] text-[#292724] sm:text-6xl lg:text-[76px]"
                        >
                            Teman baik<br />untuk
                            <em class="font-serif font-normal text-[#c77e48]"
                                >setiap gigitan.</em
                            >
                        </h1>
                        <p
                            class="mt-6 max-w-md text-base leading-7 text-[#77716b] sm:text-lg"
                        >
                            Camilan enak yang dibuat dari bahan pilihan, dikemas
                            dengan hati, dan siap menemani hari-harimu.
                        </p>
                        <div class="mt-9 flex flex-wrap items-center gap-4">
                            <a
                                href="#produk"
                                class="group inline-flex items-center gap-3 rounded-full bg-[#292724] px-6 py-3.5 text-sm font-semibold text-white shadow-lg shadow-[#292724]/10 transition-all hover:-translate-y-0.5 hover:bg-[#c77e48]"
                                >Jelajahi camilan
                                <ArrowRight
                                    class="h-4 w-4 transition-transform group-hover:translate-x-1"
                            /></a>
                            <span
                                class="flex items-center gap-2 text-sm text-[#77716b]"
                                ><span class="flex -space-x-2"
                                    ><span
                                        class="h-7 w-7 rounded-full border-2 border-[#fcfaf7] bg-[#e1b28c]"
                                    ></span
                                    ><span
                                        class="h-7 w-7 rounded-full border-2 border-[#fcfaf7] bg-[#a9b7a4]"
                                    ></span
                                    ><span
                                        class="h-7 w-7 rounded-full border-2 border-[#fcfaf7] bg-[#d4b5b0]"
                                    ></span
                                ></span>
                                Disukai 2.000+ orang</span
                            >
                        </div>
                    </div>
                    <div class="relative mx-auto w-full max-w-[520px]">
                        <div
                            class="absolute top-1/4 -right-2 h-48 w-48 rounded-full bg-[#e8c3a5]/50 blur-3xl"
                        ></div>
                        <div
                            class="relative overflow-hidden rounded-[45%_45%_20%_20%/35%_35%_15%_15%] bg-[#e8d6c1] shadow-2xl shadow-[#c9a886]/25"
                        >
                            <img
                                src="https://images.unsplash.com/photo-1621939514649-280e2aa1f7c7?auto=format&fit=crop&w=1000&q=90"
                                alt="Camilan Cemil.in"
                                class="aspect-[0.9] w-full object-cover mix-blend-multiply transition-transform duration-700 hover:scale-105"
                            />
                            <div
                                class="absolute right-5 bottom-5 left-5 flex items-center justify-between rounded-2xl border border-white/50 bg-white/75 p-4 backdrop-blur-md"
                            >
                                <div>
                                    <p
                                        class="text-xs font-medium text-[#82776e]"
                                    >
                                        Paling banyak dicari
                                    </p>
                                    <p class="mt-1 font-semibold">
                                        Basreng Daun Jeruk
                                    </p>
                                </div>
                                <span
                                    class="rounded-full bg-[#292724] px-3 py-2 text-xs font-bold text-white"
                                    >24K</span
                                >
                            </div>
                        </div>
                        <div
                            class="absolute top-12 -left-5 hidden -rotate-6 items-center gap-2 rounded-2xl bg-white px-4 py-3 text-xs font-semibold shadow-xl shadow-[#9b7a5e]/10 sm:flex"
                        >
                            <span
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-[#f6e7d8] text-lg"
                                >🤎</span
                            >
                            Dibuat dengan cinta
                        </div>
                        <div
                            class="absolute -right-4 bottom-24 hidden rotate-6 items-center gap-2 rounded-2xl bg-white px-4 py-3 text-xs font-semibold shadow-xl shadow-[#9b7a5e]/10 sm:flex"
                        >
                            <Check
                                class="h-4 w-4 rounded-full bg-[#cfdfca] p-0.5 text-[#477243]"
                            />
                            100% fresh
                        </div>
                    </div>
                </div>
            </section>

            <section class="border-y border-[#e8e1d8] bg-white/60 py-6">
                <div
                    class="mx-auto flex max-w-7xl items-center justify-between gap-5 overflow-x-auto px-5 text-xs font-medium text-[#77716b] sm:px-8"
                >
                    <span class="flex shrink-0 items-center gap-2"
                        ><Truck class="h-4 w-4 text-[#c77e48]" /> Dikirim setiap
                        hari</span
                    ><span class="h-4 w-px bg-[#e5ddd4]"></span
                    ><span class="flex shrink-0 items-center gap-2"
                        >✦ Bahan pilihan</span
                    ><span class="h-4 w-px bg-[#e5ddd4]"></span
                    ><span class="flex shrink-0 items-center gap-2"
                        >♡ Tanpa pengawet berlebih</span
                    ><span class="h-4 w-px bg-[#e5ddd4]"></span
                    ><span class="flex shrink-0 items-center gap-2"
                        >↻ Garansi rasa enak</span
                    >
                </div>
            </section>

            <section
                id="produk"
                class="mx-auto max-w-7xl px-5 py-20 sm:px-8 lg:py-24"
            >
                <div
                    class="flex flex-col justify-between gap-6 sm:flex-row sm:items-end"
                >
                    <div>
                        <p
                            class="mb-3 text-xs font-bold tracking-[0.2em] text-[#c77e48] uppercase"
                        >
                            Pilihan hari ini
                        </p>
                        <h2
                            class="text-3xl font-bold tracking-[-0.04em] sm:text-4xl"
                        >
                            Cari yang bikin
                            <em class="font-serif font-normal">senang.</em>
                        </h2>
                    </div>
                    <a
                        href="#produk"
                        class="group flex items-center gap-2 text-sm font-semibold text-[#c77e48]"
                        >Lihat semua
                        <ChevronRight
                            class="h-4 w-4 transition-transform group-hover:translate-x-1"
                    /></a>
                </div>
                <div class="mt-10 flex gap-2 overflow-x-auto pb-2">
                    <button
                        v-for="category in categories"
                        :key="category.name"
                        class="flex shrink-0 items-center gap-2 rounded-full border px-4 py-2.5 text-sm font-medium transition-all"
                        :class="
                            activeCategory === category.name
                                ? 'border-[#292724] bg-[#292724] text-white shadow-md'
                                : 'border-[#e5ddd4] bg-white text-[#77716b] hover:border-[#dca477] hover:text-[#c77e48]'
                        "
                        @click="activeCategory = category.name"
                    >
                        <span>{{ category.icon }}</span
                        >{{ category.name }}
                        <span
                            v-if="activeCategory === category.name"
                            class="text-[10px] text-[#dca477]"
                            >{{ category.count }}</span
                        >
                    </button>
                </div>
                <div
                    v-if="filteredProducts.length"
                    class="mt-8 grid gap-x-5 gap-y-10 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <article
                        v-for="product in filteredProducts"
                        :key="product.id"
                        class="group cursor-pointer"
                        @click="openProduct(product)"
                    >
                        <div
                            class="relative overflow-hidden rounded-[28px]"
                            :style="{ backgroundColor: product.color }"
                        >
                            <img
                                v-if="product.image"
                                :src="product.image"
                                :alt="product.name"
                                class="aspect-[1.12] w-full object-cover mix-blend-multiply transition duration-500 group-hover:scale-105"
                                loading="lazy"
                            />
                            <div
                                v-else
                                class="flex aspect-[1.12] items-center justify-center text-6xl transition duration-500 group-hover:scale-105"
                            >
                                {{ categoryIcons[product.category] || '🍿' }}
                            </div>
                            <span
                                v-if="product.tag"
                                class="absolute top-4 left-4 rounded-full bg-white/90 px-3 py-1.5 text-[11px] font-bold text-[#60574f] shadow-sm"
                                >{{ product.tag }}</span
                            >
                            <button
                                class="absolute top-4 right-4 flex h-9 w-9 items-center justify-center rounded-full bg-white/85 text-[#91877d] opacity-0 shadow-sm transition-all group-hover:opacity-100 hover:text-[#c77e48]"
                                :class="{
                                    'text-[#c77e48] opacity-100':
                                        likedProducts.includes(product.id),
                                }"
                                :aria-label="`Sukai ${product.name}`"
                                @click.stop="toggleLike(product.id)"
                            >
                                <Heart
                                    class="h-4 w-4"
                                    :fill="
                                        likedProducts.includes(product.id)
                                            ? 'currentColor'
                                            : 'none'
                                    "
                                />
                            </button>
                            <button
                                class="absolute right-4 bottom-4 flex h-11 w-11 translate-y-2 items-center justify-center rounded-full bg-[#292724] text-white opacity-0 shadow-lg transition-all group-hover:translate-y-0 group-hover:opacity-100 hover:bg-[#c77e48]"
                                :aria-label="`Tambah ${product.name} ke keranjang`"
                                @click.stop="addToCart(product.id)"
                            >
                                <Plus class="h-5 w-5" />
                            </button>
                        </div>
                        <div
                            class="mt-4 flex items-start justify-between gap-3"
                        >
                            <div>
                                <p class="mb-1 text-xs text-[#9a938c]">
                                    {{ product.category }}
                                </p>
                                <h3 class="font-semibold">
                                    {{ product.name }}
                                </h3>
                            </div>
                            <p
                                class="shrink-0 text-sm font-bold text-[#c77e48]"
                            >
                                {{ formatPrice(product.price) }}
                            </p>
                        </div>
                    </article>
                </div>
                <div
                    v-else
                    class="mt-8 rounded-3xl border border-dashed border-[#ded4c9] bg-white p-12 text-center text-[#77716b]"
                >
                    Belum ada camilan yang cocok dengan pencarianmu.
                </div>
            </section>

            <section
                class="mx-5 mb-20 overflow-hidden rounded-[32px] bg-[#dca477] sm:mx-8 lg:mx-auto lg:max-w-7xl"
            >
                <div
                    class="relative grid items-center gap-8 px-7 py-10 sm:px-12 lg:grid-cols-[1fr_0.8fr] lg:px-16 lg:py-14"
                >
                    <div
                        class="absolute -top-24 -right-16 h-64 w-64 rounded-full border-[28px] border-white/10"
                    ></div>
                    <div>
                        <p
                            class="mb-3 text-xs font-bold tracking-[0.2em] text-[#71492f] uppercase"
                        >
                            Buat ngemil makin seru
                        </p>
                        <h2
                            class="max-w-lg text-3xl leading-tight font-bold tracking-[-0.04em] text-[#292724] sm:text-4xl"
                        >
                            Paket hemat untuk<br /><em
                                class="font-serif font-normal"
                                >momen favoritmu.</em
                            >
                        </h2>
                        <p
                            class="mt-4 max-w-md text-sm leading-6 text-[#71492f]"
                        >
                            Isi lebih banyak, bayar lebih ringan. Cocok untuk
                            sharing atau stok di rumah.
                        </p>
                        <a
                            href="#produk"
                            class="mt-7 inline-flex items-center gap-2 rounded-full bg-[#292724] px-5 py-3 text-sm font-semibold text-white transition-transform hover:-translate-y-0.5"
                            >Lihat paket hemat <ArrowRight class="h-4 w-4"
                        /></a>
                    </div>
                    <div class="relative flex justify-center">
                        <div class="grid grid-cols-2 gap-3">
                            <div
                                class="mt-8 rotate-[-7deg] overflow-hidden rounded-2xl shadow-xl"
                            >
                                <img
                                    src="https://images.unsplash.com/photo-1599599810694-57a9c3ad1a7a?auto=format&fit=crop&w=500&q=80"
                                    alt="Paket camilan manis"
                                    class="h-36 w-32 object-cover sm:h-44 sm:w-40"
                                />
                            </div>
                            <div class="overflow-hidden rounded-2xl shadow-xl">
                                <img
                                    src="https://images.unsplash.com/photo-1621939514649-280e2aa1f7c7?auto=format&fit=crop&w=500&q=80"
                                    alt="Paket camilan pedas"
                                    class="h-36 w-32 object-cover sm:h-44 sm:w-40"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section
                id="cerita"
                class="mx-auto max-w-7xl px-5 pb-20 sm:px-8 lg:pb-28"
            >
                <div
                    class="grid gap-12 lg:grid-cols-[0.8fr_1fr] lg:items-center lg:gap-24"
                >
                    <div class="relative mx-auto w-full max-w-md">
                        <div
                            class="absolute -bottom-5 -left-5 h-32 w-32 rounded-full bg-[#e8d6c1]"
                        ></div>
                        <img
                            src="https://images.unsplash.com/photo-1558961363-fa8fdf82db35?auto=format&fit=crop&w=800&q=85"
                            alt="Cookies pilihan Cemil.in"
                            class="relative aspect-square w-full rounded-[40px] object-cover"
                        />
                    </div>
                    <div>
                        <p
                            class="mb-3 text-xs font-bold tracking-[0.2em] text-[#c77e48] uppercase"
                        >
                            Kecil tapi berarti
                        </p>
                        <h2
                            class="max-w-lg text-3xl leading-tight font-bold tracking-[-0.04em] sm:text-4xl"
                        >
                            Karena hari biasa juga pantas dirayakan.
                        </h2>
                        <p
                            class="mt-5 max-w-lg text-base leading-7 text-[#77716b]"
                        >
                            Cemil.in dimulai dari satu hal sederhana: percaya
                            bahwa camilan yang enak bisa membuat hari terasa
                            sedikit lebih baik. Kami memilih bahan dengan teliti
                            dan membuat setiap pesanan seperti untuk keluarga
                            sendiri.
                        </p>
                        <div class="mt-8 flex items-center gap-8">
                            <div>
                                <p class="text-2xl font-bold">
                                    4.9<span class="text-[#c77e48]">/5</span>
                                </p>
                                <div class="mt-1 flex gap-0.5 text-[#e2a15d]">
                                    <Star
                                        v-for="star in 5"
                                        :key="star"
                                        class="h-3.5 w-3.5"
                                        fill="currentColor"
                                    />
                                </div>
                            </div>
                            <div class="h-10 w-px bg-[#e5ddd4]"></div>
                            <p class="text-sm leading-5 text-[#77716b]">
                                Dari <strong class="text-[#292724]">500+</strong
                                ><br />ulasan pelanggan
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer id="bantuan" class="border-t border-[#e8e1d8] bg-white">
            <div
                class="mx-auto grid max-w-7xl gap-10 px-5 py-12 sm:px-8 md:grid-cols-[1.4fr_1fr_1fr_1.3fr]"
            >
                <div>
                    <a href="#" class="flex items-center gap-2.5"
                        ><span
                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#dca477] text-sm"
                            >✦</span
                        ><span class="text-lg font-bold tracking-[-0.05em]"
                            >cemil<span class="text-[#c77e48]">.in</span></span
                        ></a
                    >
                    <p class="mt-4 max-w-xs text-sm leading-6 text-[#8b837b]">
                        Camilan enak untuk menemani cerita kecilmu setiap hari.
                    </p>
                </div>
                <div>
                    <h3 class="mb-4 text-sm font-bold">Cemil.in</h3>
                    <div class="space-y-3 text-sm text-[#8b837b]">
                        <a href="#cerita" class="block hover:text-[#c77e48]"
                            >Tentang kami</a
                        ><a href="#produk" class="block hover:text-[#c77e48]"
                            >Menu snack</a
                        ><a href="#" class="block hover:text-[#c77e48]"
                            >Jadi reseller</a
                        >
                    </div>
                </div>
                <div>
                    <h3 class="mb-4 text-sm font-bold">Bantuan</h3>
                    <div class="space-y-3 text-sm text-[#8b837b]">
                        <a href="#" class="block hover:text-[#c77e48]"
                            >Cara pesan</a
                        ><a href="#" class="block hover:text-[#c77e48]"
                            >Pengiriman</a
                        ><a href="#" class="block hover:text-[#c77e48]"
                            >Hubungi kami</a
                        >
                    </div>
                </div>
                <div>
                    <h3 class="mb-4 text-sm font-bold">
                        Dapatkan kabar terbaru
                    </h3>
                    <p class="mb-3 text-sm leading-5 text-[#8b837b]">
                        Promo dan camilan baru, langsung di inbox.
                    </p>
                    <div
                        class="flex rounded-full border border-[#e5ddd4] bg-[#fcfaf7] p-1"
                    >
                        <input
                            placeholder="Email kamu"
                            class="min-w-0 flex-1 bg-transparent px-3 text-sm outline-none placeholder:text-[#aaa29a]"
                        /><button
                            class="rounded-full bg-[#292724] px-4 py-2 text-xs font-semibold text-white"
                        >
                            Daftar
                        </button>
                    </div>
                </div>
            </div>
            <div
                class="border-t border-[#eee8e1] px-5 py-5 text-center text-xs text-[#a39a91]"
            >
                © 2025 Cemil.in · Dibuat untuk para penikmat camilan
            </div>
        </footer>

        <Transition name="drawer">
            <div v-if="isCartOpen" class="fixed inset-0 z-50">
                <div
                    class="absolute inset-0 bg-[#292724]/35 backdrop-blur-sm"
                    @click="isCartOpen = false"
                ></div>
                <aside
                    class="absolute top-0 right-0 flex h-full w-full max-w-md flex-col bg-[#fcfaf7] shadow-2xl"
                >
                    <div
                        class="flex items-center justify-between border-b border-[#e8e1d8] px-6 py-5"
                    >
                        <div>
                            <h2 class="text-lg font-bold">Keranjangmu</h2>
                            <p class="mt-1 text-xs text-[#8b837b]">
                                {{ cartCount }} item camilan
                            </p>
                        </div>
                        <button
                            class="rounded-full p-2 text-[#77716b] transition-colors hover:bg-[#eee8e1]"
                            aria-label="Tutup keranjang"
                            @click="isCartOpen = false"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>
                    <div
                        v-if="cartItems.length"
                        class="flex-1 space-y-4 overflow-y-auto p-6"
                    >
                        <div
                            v-for="item in cartItems"
                            :key="item.id"
                            class="flex gap-4 rounded-2xl bg-white p-3"
                        >
                            <img
                                v-if="item.image"
                                :src="item.image"
                                :alt="item.name"
                                class="h-20 w-20 rounded-xl object-cover"
                            />
                            <div
                                v-else
                                class="flex h-20 w-20 shrink-0 items-center justify-center rounded-xl bg-[#f3e7da] text-2xl"
                            >
                                {{ categoryIcons[item.category] || '🍿' }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold">
                                    {{ item.name }}
                                </p>
                                <p class="mt-1 text-xs text-[#c77e48]">
                                    {{ formatPrice(item.price) }}
                                </p>
                                <div class="mt-3 flex items-center gap-2">
                                    <button
                                        class="flex h-6 w-6 items-center justify-center rounded-full border border-[#e5ddd4] text-[#77716b]"
                                        @click="updateQuantity(item.id, -1)"
                                    >
                                        <Minus class="h-3 w-3" /></button
                                    ><input
                                        type="number"
                                        min="1"
                                        :value="item.quantity"
                                        class="h-7 w-14 rounded-full border border-[#e5ddd4] bg-[#fcfaf7] text-center text-xs font-semibold outline-none focus:border-[#dca477] focus:ring-2 focus:ring-[#dca477]/20"
                                        :aria-label="`Jumlah ${item.name}`"
                                        @input="
                                            setQuantity(
                                                item.id,
                                                (
                                                    $event.target as HTMLInputElement
                                                ).value,
                                            )
                                        "
                                    /><button
                                        class="flex h-6 w-6 items-center justify-center rounded-full border border-[#e5ddd4] text-[#77716b]"
                                        @click="updateQuantity(item.id, 1)"
                                    >
                                        <Plus class="h-3 w-3" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        v-else
                        class="flex flex-1 flex-col items-center justify-center px-8 text-center"
                    >
                        <div
                            class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-[#f3e7da]"
                        >
                            <ShoppingBag class="h-7 w-7 text-[#c77e48]" />
                        </div>
                        <h3 class="font-semibold">Keranjang masih kosong</h3>
                        <p class="mt-2 text-sm text-[#8b837b]">
                            Yuk pilih camilan favoritmu untuk mulai ngemil.
                        </p>
                        <button
                            class="mt-6 rounded-full bg-[#292724] px-5 py-3 text-sm font-semibold text-white"
                            @click="isCartOpen = false"
                        >
                            Mulai belanja
                        </button>
                    </div>
                    <div
                        v-if="cartItems.length"
                        class="border-t border-[#e8e1d8] bg-white p-6"
                    >
                        <div class="mb-4 flex justify-between text-sm">
                            <span class="text-[#77716b]">Total sementara</span
                            ><strong>{{ formatPrice(cartTotal) }}</strong>
                        </div>
                        <Link
                            :href="checkoutUrl"
                            class="block w-full rounded-full bg-[#292724] py-3.5 text-center text-sm font-semibold text-white transition-colors hover:bg-[#c77e48]"
                            @click="isCartOpen = false"
                            >Lanjut ke pembayaran
                            <ArrowRight class="ml-2 inline h-4 w-4"
                        /></Link>
                        <p class="mt-3 text-center text-[11px] text-[#a39a91]">
                            Pajak dan ongkir dihitung saat checkout
                        </p>
                    </div>
                </aside>
            </div>
        </Transition>
        <Dialog
            :open="selectedProduct !== null"
            @update:open="(open) => !open && closeProduct()"
        >
            <DialogContent
                v-if="selectedProduct"
                class="overflow-hidden border-[#eadfd4] bg-[#fffdfb] p-0 sm:max-w-2xl"
            >
                <div class="grid sm:grid-cols-2">
                    <div
                        class="min-h-64"
                        :style="{ backgroundColor: selectedProduct.color }"
                    >
                        <img
                            v-if="selectedProduct.image"
                            :src="selectedProduct.image"
                            :alt="selectedProduct.name"
                            class="h-full min-h-64 w-full object-cover mix-blend-multiply"
                        />
                        <div
                            v-else
                            class="flex h-full min-h-64 items-center justify-center text-7xl"
                        >
                            {{
                                categoryIcons[selectedProduct.category] || '🍿'
                            }}
                        </div>
                    </div>
                    <div class="flex flex-col p-6 sm:p-8">
                        <DialogHeader>
                            <p
                                class="text-xs font-bold tracking-[0.18em] text-[#c77e48] uppercase"
                            >
                                {{ selectedProduct.category }}
                            </p>
                            <DialogTitle class="mt-2 text-2xl text-[#292724]">{{
                                selectedProduct.name
                            }}</DialogTitle>
                            <DialogDescription
                                class="pt-2 text-sm leading-6 text-[#77716b]"
                                >{{
                                    selectedProduct.description ||
                                    'Camilan pilihan Cemil.in yang dibuat dengan bahan berkualitas.'
                                }}</DialogDescription
                            >
                        </DialogHeader>
                        <p class="mt-6 text-xl font-bold text-[#c77e48]">
                            {{ formatPrice(selectedProduct.price) }}
                        </p>
                        <DialogFooter class="mt-6 sm:justify-start">
                            <button
                                class="w-full rounded-full bg-[#292724] px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-[#c77e48]"
                                @click="
                                    addToCart(selectedProduct.id);
                                    closeProduct();
                                "
                            >
                                Tambah ke keranjang
                            </button>
                        </DialogFooter>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    </div>
</template>

<style scoped>
.mobile-menu-enter-active,
.mobile-menu-leave-active {
    max-height: 320px;
    overflow: hidden;
    transition:
        max-height 0.25s ease,
        opacity 0.2s ease;
}
.mobile-menu-enter-from,
.mobile-menu-leave-to {
    max-height: 0;
    opacity: 0;
}
.drawer-enter-active,
.drawer-leave-active {
    transition: opacity 0.25s ease;
}
.drawer-enter-active aside,
.drawer-leave-active aside {
    transition: transform 0.3s ease;
}
.drawer-enter-from,
.drawer-leave-to {
    opacity: 0;
}
.drawer-enter-from aside,
.drawer-leave-to aside {
    transform: translateX(100%);
}
</style>
