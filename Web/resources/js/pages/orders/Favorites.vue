<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';

defineOptions({ layout: AppLayout });

type Favorite = {
    id: number;
    item: {
        id: number;
        nama: string;
        deskripsi: string | null;
        kategori: string;
        harga: string | number;
        gambar: string | null;
    };
};

const props = defineProps<{ favorites: Favorite[] }>();

function removeFavorite(itemId: number): void {
    router.post(`/favorites/${itemId}/toggle`, {}, { preserveScroll: true });
}
</script>

<template>
    <Head title="My Favorite" />
    <div
        class="min-h-screen overflow-x-clip bg-[#fcfaf7] px-5 py-8 text-[#292724] sm:px-8 lg:py-12"
    >
        <div class="mx-auto max-w-5xl space-y-8">
            <div class="flex items-end justify-between">
                <div>
                    <p
                        class="mb-2 text-xs font-semibold tracking-[0.2em] text-[#c77e48] uppercase"
                    >
                        Cemil.in
                    </p>
                    <Heading
                        variant="default"
                        title="My Favorite"
                        description="Menu snack favoritmu, siap dipesan kapan saja."
                    />
                </div>
                <span
                    class="hidden rounded-full bg-[#292724] px-4 py-2 text-xs font-semibold text-white sm:block"
                    >{{ props.favorites.length }} menu</span
                >
            </div>
            <div
                v-if="props.favorites.length"
                class="grid gap-4 sm:grid-cols-2"
            >
                <article
                    v-for="favorite in props.favorites"
                    :key="favorite.id"
                    class="group overflow-hidden rounded-3xl border border-[#eadfd4] bg-white shadow-[0_8px_30px_rgba(82,59,38,0.06)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_14px_35px_rgba(82,59,38,0.12)]"
                >
                    <img
                        v-if="favorite.item.gambar"
                        :src="favorite.item.gambar"
                        :alt="favorite.item.nama"
                        class="h-40 w-full object-cover"
                    />
                    <div
                        v-else
                        class="flex h-40 items-center justify-center bg-[#f3e7da] text-6xl"
                    >
                        🍿
                    </div>
                    <div class="p-5">
                        <p
                            class="text-xs font-semibold tracking-wider text-[#c77e48] uppercase"
                        >
                            {{ favorite.item.kategori }}
                        </p>
                        <h2 class="mt-1 font-semibold text-[#292724]">
                            {{ favorite.item.nama }}
                        </h2>
                        <p class="mt-2 line-clamp-2 text-sm text-[#77716b]">
                            {{
                                favorite.item.deskripsi ||
                                'Camilan pilihan Cemil.in.'
                            }}
                        </p>
                        <div
                            class="mt-4 flex items-center justify-between border-t border-[#f0e7de] pt-4"
                        >
                            <strong class="text-lg text-[#c77e48]"
                                >Rp
                                {{
                                    Number(favorite.item.harga).toLocaleString(
                                        'id-ID',
                                    )
                                }}</strong
                            >
                            <button
                                type="button"
                                class="rounded-full border border-[#e5ddd4] px-3 py-1.5 text-xs font-semibold text-[#77716b] transition-colors hover:border-[#dca477] hover:text-[#c77e48]"
                                @click="removeFavorite(favorite.item.id)"
                            >
                                Hapus favorit
                            </button>
                        </div>
                    </div>
                </article>
            </div>
            <div
                v-else
                class="rounded-3xl border border-dashed border-[#decdbd] bg-white/70 p-12 text-center text-[#77716b]"
            >
                <div class="mx-auto mb-4 text-4xl">♡</div>
                Belum ada menu yang ditambahkan ke favorit.
            </div>
        </div>
    </div>
</template>
