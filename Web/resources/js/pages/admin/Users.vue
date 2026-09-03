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

type User = {
    id: number;
    name: string;
    email: string;
    whatsapp: string | null;
    alamat: string | null;
    role: 'admin' | 'customer';
};

const props = defineProps<{ users: { data: User[] } }>();
const isAddDialogOpen = ref(false);
const editingUser = ref<User | null>(null);

function openEditDialog(user: User): void {
    editingUser.value = user;
}

function closeEditDialog(): void {
    editingUser.value = null;
}

function deleteUser(user: User): void {
    if (window.confirm(`Hapus user ${user.name}?`)) {
        router.delete(`/admin/users/${user.id}`, {
            onSuccess: closeEditDialog,
        });
    }
}
</script>

<template>
    <Head title="Kelola User" />
    <div
        class="min-h-screen bg-[#fcfaf7] px-5 py-8 text-[#292724] sm:px-8 lg:py-12"
    >
        <div class="mx-auto max-w-6xl space-y-7">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p
                        class="mb-2 text-xs font-semibold tracking-[0.2em] text-[#c77e48] uppercase"
                    >
                        Cemil.in Admin
                    </p>
                    <h1
                        class="text-3xl font-bold tracking-tight text-[#292724]"
                    >
                        Kelola User
                    </h1>
                    <p class="mt-1 text-sm text-[#77716b]">
                        Kelola akun pelanggan dan admin Cemil.in.
                    </p>
                </div>
                <Dialog v-model:open="isAddDialogOpen">
                    <DialogTrigger as-child
                        ><Button
                            class="rounded-full bg-[#292724] px-5 text-white hover:bg-[#c77e48]"
                            >+ Add</Button
                        ></DialogTrigger
                    >
                    <DialogContent
                        class="border-[#eadfd4] bg-[#fffdfb] sm:max-w-lg"
                    >
                        <DialogHeader
                            ><DialogTitle>Tambah user</DialogTitle
                            ><DialogDescription
                                >Buat akun baru untuk pelanggan atau
                                admin.</DialogDescription
                            ></DialogHeader
                        >
                        <Form
                            action="/admin/users"
                            method="post"
                            class="grid gap-4"
                            v-slot="{ errors, processing }"
                            @success="isAddDialogOpen = false"
                        >
                            <div class="grid gap-2">
                                <Label for="add-user-name">Nama</Label
                                ><Input
                                    id="add-user-name"
                                    name="name"
                                    required
                                /><InputError :message="errors.name" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="add-user-email">Email</Label
                                ><Input
                                    id="add-user-email"
                                    name="email"
                                    type="email"
                                    required
                                /><InputError :message="errors.email" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="add-user-whatsapp">WhatsApp</Label
                                ><Input
                                    id="add-user-whatsapp"
                                    name="whatsapp"
                                /><InputError :message="errors.whatsapp" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="add-user-alamat">Alamat</Label
                                ><textarea
                                    id="add-user-alamat"
                                    name="alamat"
                                    rows="2"
                                    class="w-full rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-2 focus-visible:ring-[#dca477]/50"
                                ></textarea
                                ><InputError :message="errors.alamat" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="add-user-role">Role</Label
                                ><select
                                    id="add-user-role"
                                    name="role"
                                    class="h-9 rounded-md border bg-transparent px-3 text-sm"
                                >
                                    <option value="customer">Customer</option>
                                    <option value="admin">Admin</option></select
                                ><InputError :message="errors.role" />
                            </div>
                            <div class="grid gap-2 sm:grid-cols-2">
                                <div class="grid gap-2">
                                    <Label for="add-user-password"
                                        >Password</Label
                                    ><Input
                                        id="add-user-password"
                                        name="password"
                                        type="password"
                                        required
                                    /><InputError :message="errors.password" />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="add-user-password-confirmation"
                                        >Konfirmasi</Label
                                    ><Input
                                        id="add-user-password-confirmation"
                                        name="password_confirmation"
                                        type="password"
                                        required
                                    />
                                </div>
                            </div>
                            <DialogFooter
                                ><Button
                                    type="submit"
                                    :disabled="processing"
                                    class="bg-[#292724] text-white hover:bg-[#c77e48]"
                                    >{{
                                        processing
                                            ? 'Menyimpan...'
                                            : 'Simpan user'
                                    }}</Button
                                ></DialogFooter
                            >
                        </Form>
                    </DialogContent>
                </Dialog>
            </div>

            <div
                class="overflow-x-auto rounded-3xl border border-[#eadfd4] bg-white shadow-[0_8px_30px_rgba(82,59,38,0.06)]"
            >
                <table class="w-full min-w-[650px] text-left text-sm">
                    <thead>
                        <tr
                            class="border-b border-[#eadfd4] bg-[#292724] text-white"
                        >
                            <th class="p-4">Nama</th>
                            <th class="p-4">Email</th>
                            <th class="p-4">WhatsApp</th>
                            <th class="p-4">Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="user in props.users.data"
                            :key="user.id"
                            tabindex="0"
                            class="cursor-pointer border-b border-[#f0e7df] transition-colors last:border-0 hover:bg-[#fff3e8] focus:bg-[#fff3e8]"
                            @click="openEditDialog(user)"
                            @keydown.enter="openEditDialog(user)"
                        >
                            <td class="p-4 font-medium text-[#292724]">
                                {{ user.name }}
                            </td>
                            <td class="p-4 text-[#625c56]">{{ user.email }}</td>
                            <td class="p-4 text-[#625c56]">
                                {{ user.whatsapp || '-' }}
                            </td>
                            <td class="p-4">
                                <span
                                    class="rounded-full bg-[#fff3e8] px-3 py-1 text-xs font-semibold text-[#b66f3d] capitalize"
                                    >{{ user.role }}</span
                                >
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p
                    v-if="!props.users.data.length"
                    class="p-8 text-center text-sm text-[#77716b]"
                >
                    Belum ada user.
                </p>
            </div>
        </div>
    </div>

    <Dialog
        :open="editingUser !== null"
        @update:open="(open) => !open && closeEditDialog()"
    >
        <DialogContent
            v-if="editingUser"
            class="border-[#eadfd4] bg-[#fffdfb] sm:max-w-lg"
        >
            <DialogHeader
                ><DialogTitle>Edit user</DialogTitle
                ><DialogDescription
                    >Perbarui data dan role user.</DialogDescription
                ></DialogHeader
            >
            <Form
                :key="editingUser.id"
                :action="`/admin/users/${editingUser.id}`"
                method="put"
                class="grid gap-4"
                v-slot="{ errors, processing }"
                @success="closeEditDialog"
            >
                <div class="grid gap-2">
                    <Label :for="`edit-user-name-${editingUser.id}`">Nama</Label
                    ><Input
                        :id="`edit-user-name-${editingUser.id}`"
                        name="name"
                        :default-value="editingUser.name"
                        required
                    /><InputError :message="errors.name" />
                </div>
                <div class="grid gap-2">
                    <Label>Email</Label
                    ><Input :default-value="editingUser.email" disabled />
                </div>
                <div class="grid gap-2">
                    <Label :for="`edit-user-whatsapp-${editingUser.id}`"
                        >WhatsApp</Label
                    ><Input
                        :id="`edit-user-whatsapp-${editingUser.id}`"
                        name="whatsapp"
                        :default-value="editingUser.whatsapp ?? ''"
                    /><InputError :message="errors.whatsapp" />
                </div>
                <div class="grid gap-2">
                    <Label :for="`edit-user-alamat-${editingUser.id}`"
                        >Alamat</Label
                    ><textarea
                        :id="`edit-user-alamat-${editingUser.id}`"
                        name="alamat"
                        rows="2"
                        class="w-full rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-2 focus-visible:ring-[#dca477]/50"
                        >{{ editingUser.alamat ?? '' }}</textarea
                    ><InputError :message="errors.alamat" />
                </div>
                <div class="grid gap-2">
                    <Label :for="`edit-user-role-${editingUser.id}`">Role</Label
                    ><select
                        :id="`edit-user-role-${editingUser.id}`"
                        name="role"
                        class="h-9 rounded-md border bg-transparent px-3 text-sm"
                    >
                        <option
                            value="customer"
                            :selected="editingUser.role === 'customer'"
                        >
                            Customer
                        </option>
                        <option
                            value="admin"
                            :selected="editingUser.role === 'admin'"
                        >
                            Admin
                        </option></select
                    ><InputError :message="errors.role" />
                </div>
                <DialogFooter
                    class="flex-col-reverse gap-2 sm:flex-row sm:justify-between"
                    ><Button
                        type="button"
                        variant="destructive"
                        @click="deleteUser(editingUser)"
                        >Delete</Button
                    ><Button
                        type="submit"
                        :disabled="processing"
                        class="bg-[#292724] text-white hover:bg-[#c77e48]"
                        >{{
                            processing ? 'Menyimpan...' : 'Simpan perubahan'
                        }}</Button
                    ></DialogFooter
                >
            </Form>
        </DialogContent>
    </Dialog>
</template>
