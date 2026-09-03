<script setup lang="ts">
import { Form, Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';

defineOptions({ layout: AppLayout });

type Item = {
    id: number;
    nama: string;
    deskripsi: string | null;
    kategori: string;
    harga: string | number;
    gambar: string | null;
};

const props = defineProps<{ items: { data: Item[] } }>();
const isAddDialogOpen = ref(false);
const editingItem = ref<Item | null>(null);

function openEditDialog(item: Item): void {
    editingItem.value = item;
}

function closeEditDialog(): void {
    editingItem.value = null;
}

function deleteItem(item: Item): void {
    if (window.confirm(`Hapus menu ${item.nama}?`)) {
        router.delete(`/admin/items/${item.id}`, {
            onSuccess: closeEditDialog,
        });
    }
}
</script>

<template>
    <Head title="Kelola Menu" />
    <div
        class="min-h-screen bg-[#fcfaf7] px-5 py-8 text-[#292724] sm:px-8 lg:py-12"
    >
        <div class="mx-auto max-w-6xl space-y-7">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h1
                        class="text-2xl font-bold tracking-tight text-[#292724]"
                    >
                        Kelola Menu Snack
                    </h1>
                    <p class="mt-1 text-sm text-[#77716b]">
                        Atur katalog camilan Cemil.in.
                    </p>
                </div>
                <Dialog v-model:open="isAddDialogOpen">
                    <DialogTrigger as-child>
                        <Button
                            class="rounded-full bg-[#292724] px-5 text-white hover:bg-[#c77e48]"
                            >+ Add</Button
                        >
                    </DialogTrigger>
                    <DialogContent
                        class="border-[#eadfd4] bg-[#fffdfb] sm:max-w-lg"
                    >
                        <DialogHeader>
                            <DialogTitle>Tambah menu snack</DialogTitle>
                            <DialogDescription
                                >Masukkan detail menu baru untuk ditampilkan di
                                katalog.</DialogDescription
                            >
                        </DialogHeader>
                        <Form
                            action="/admin/items"
                            method="post"
                            class="grid gap-4"
                            v-slot="{ errors, processing }"
                            @success="isAddDialogOpen = false"
                        >
                            <div class="grid gap-2">
                                <Label for="add-nama">Nama menu</Label
                                ><Input
                                    id="add-nama"
                                    name="nama"
                                    required
                                    placeholder="Contoh: Basreng Daun Jeruk"
                                /><InputError :message="errors.nama" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="add-kategori">Kategori</Label
                                ><Input
                                    id="add-kategori"
                                    name="kategori"
                                    required
                                    placeholder="Contoh: Pedas"
                                /><InputError :message="errors.kategori" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="add-harga">Harga</Label
                                ><Input
                                    id="add-harga"
                                    name="harga"
                                    type="number"
                                    min="0"
                                    required
                                    placeholder="25000"
                                /><InputError :message="errors.harga" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="add-gambar"
                                    >URL gambar (opsional)</Label
                                ><Input
                                    id="add-gambar"
                                    name="gambar"
                                    placeholder="https://..."
                                /><InputError :message="errors.gambar" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="add-deskripsi"
                                    >Deskripsi (opsional)</Label
                                ><textarea
                                    id="add-deskripsi"
                                    name="deskripsi"
                                    rows="3"
                                    class="w-full rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-2 focus-visible:ring-[#dca477]/50"
                                    placeholder="Ceritakan rasa dan keunggulan menu"
                                ></textarea
                                ><InputError :message="errors.deskripsi" />
                            </div>
                            <DialogFooter
                                ><Button
                                    type="submit"
                                    :disabled="processing"
                                    class="bg-[#292724] text-white hover:bg-[#c77e48]"
                                    >{{
                                        processing
                                            ? 'Menyimpan...'
                                            : 'Simpan menu'
                                    }}</Button
                                ></DialogFooter
                            >
                        </Form>
                    </DialogContent>
                </Dialog>
            </div>

            <div
                v-if="props.items.data.length"
                class="grid gap-5 md:grid-cols-2 xl:grid-cols-3"
            >
                <button
                    v-for="item in props.items.data"
                    :key="item.id"
                    type="button"
                    class="group rounded-3xl border border-[#eadfd4] bg-white p-5 text-left shadow-[0_8px_30px_rgba(82,59,38,0.06)] transition-all hover:-translate-y-1 hover:border-[#dca477] hover:shadow-[0_14px_35px_rgba(82,59,38,0.12)]"
                    @click="openEditDialog(item)"
                >
                    <div
                        class="mb-4 h-28 rounded-2xl bg-gradient-to-br from-[#f8ead8] to-[#fff3e8] p-4 text-4xl"
                    >
                        🍿
                    </div>
                    <h2
                        class="font-semibold text-[#292724] group-hover:text-[#c77e48]"
                    >
                        {{ item.nama }}
                    </h2>
                    <p class="text-sm text-[#77716b]">{{ item.kategori }}</p>
                    <div
                        class="mt-4 flex justify-between text-sm text-[#625c56]"
                    >
                        <span class="text-[#c77e48]">Pre-order tersedia</span
                        ><strong
                            >Rp
                            {{
                                Number(item.harga).toLocaleString('id-ID')
                            }}</strong
                        >
                    </div>
                </button>
            </div>
            <div
                v-else
                class="rounded-3xl border border-dashed border-[#decdbd] bg-white/70 p-12 text-center text-[#77716b]"
            >
                Belum ada menu snack.
            </div>
        </div>
    </div>

    <Dialog
        :open="editingItem !== null"
        @update:open="(open) => !open && closeEditDialog()"
    >
        <DialogContent
            v-if="editingItem"
            class="border-[#eadfd4] bg-[#fffdfb] sm:max-w-lg"
        >
            <DialogHeader>
                <DialogTitle>Edit menu snack</DialogTitle>
                <DialogDescription
                    >Perbarui informasi menu atau hapus menu ini dari
                    katalog.</DialogDescription
                >
            </DialogHeader>
            <Form
                :key="editingItem.id"
                :action="`/admin/items/${editingItem.id}`"
                method="put"
                class="grid gap-4"
                v-slot="{ errors, processing }"
                @success="closeEditDialog"
            >
                <div class="grid gap-2">
                    <Label :for="`edit-nama-${editingItem.id}`">Nama menu</Label
                    ><Input
                        :id="`edit-nama-${editingItem.id}`"
                        name="nama"
                        :default-value="editingItem.nama"
                        required
                    /><InputError :message="errors.nama" />
                </div>
                <div class="grid gap-2">
                    <Label :for="`edit-kategori-${editingItem.id}`"
                        >Kategori</Label
                    ><Input
                        :id="`edit-kategori-${editingItem.id}`"
                        name="kategori"
                        :default-value="editingItem.kategori"
                        required
                    /><InputError :message="errors.kategori" />
                </div>
                <div class="grid gap-2">
                    <Label :for="`edit-harga-${editingItem.id}`">Harga</Label
                    ><Input
                        :id="`edit-harga-${editingItem.id}`"
                        name="harga"
                        type="number"
                        min="0"
                        :default-value="editingItem.harga"
                        required
                    /><InputError :message="errors.harga" />
                </div>
                <div class="grid gap-2">
                    <Label :for="`edit-gambar-${editingItem.id}`"
                        >URL gambar (opsional)</Label
                    ><Input
                        :id="`edit-gambar-${editingItem.id}`"
                        name="gambar"
                        :default-value="editingItem.gambar ?? ''"
                    /><InputError :message="errors.gambar" />
                </div>
                <div class="grid gap-2">
                    <Label :for="`edit-deskripsi-${editingItem.id}`"
                        >Deskripsi (opsional)</Label
                    ><textarea
                        :id="`edit-deskripsi-${editingItem.id}`"
                        name="deskripsi"
                        rows="3"
                        class="w-full rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-2 focus-visible:ring-[#dca477]/50"
                        >{{ editingItem.deskripsi ?? '' }}</textarea
                    ><InputError :message="errors.deskripsi" />
                </div>
                <DialogFooter
                    class="flex-col-reverse gap-2 sm:flex-row sm:justify-between"
                >
                    <Button
                        type="button"
                        variant="destructive"
                        @click="deleteItem(editingItem)"
                        >Delete</Button
                    >
                    <Button
                        type="submit"
                        :disabled="processing"
                        class="bg-[#292724] text-white hover:bg-[#c77e48]"
                        >{{
                            processing ? 'Menyimpan...' : 'Simpan perubahan'
                        }}</Button
                    >
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
