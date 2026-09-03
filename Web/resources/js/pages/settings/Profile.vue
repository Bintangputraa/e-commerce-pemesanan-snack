<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';
import type { User } from '@/types/auth';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Profile settings',
                href: edit(),
            },
        ],
    },
});

const page = usePage();
const user = computed<User>(() => page.props.auth.user);
</script>

<template>
    <Head title="Profile settings" />

    <h1 class="sr-only">Profile settings</h1>

    <div
        class="min-h-screen bg-[#fcfaf7] px-5 py-8 text-[#292724] sm:px-8 lg:py-12"
    >
        <div class="mx-auto max-w-3xl space-y-7">
            <div class="rounded-3xl bg-[#292724] p-7 text-white">
                <p
                    class="text-xs font-semibold tracking-[0.2em] text-[#dca477] uppercase"
                >
                    Cemil.in
                </p>
                <h1 class="mt-3 text-2xl font-bold">Pengaturan akun</h1>
                <p class="mt-2 text-sm text-[#d8ccc0]">
                    Kelola informasi profil dan alamat pengirimanmu.
                </p>
            </div>
            <Heading
                variant="small"
                title="Profile"
                description="Kelola data pengiriman dan informasi akunmu"
            />

            <Form
                v-bind="ProfileController.update.form()"
                class="space-y-6"
                v-slot="{ errors, processing }"
            >
                <div
                    class="grid gap-2 rounded-2xl border border-[#eadfd4] bg-white p-4 shadow-sm"
                >
                    <Label for="name">Name</Label>
                    <Input
                        id="name"
                        class="mt-1 block w-full"
                        name="name"
                        :default-value="user.name"
                        required
                        autocomplete="name"
                        placeholder="Full name"
                    />
                    <InputError class="mt-2" :message="errors.name" />
                </div>

                <div
                    class="grid gap-2 rounded-2xl border border-[#eadfd4] bg-white p-4 shadow-sm"
                >
                    <Label for="email"
                        >Email address (tidak dapat diubah)</Label
                    >
                    <Input
                        id="email"
                        type="email"
                        class="mt-1 block w-full"
                        name="email"
                        :default-value="user.email"
                        required
                        disabled
                        autocomplete="username"
                        placeholder="Email address"
                    />
                    <InputError class="mt-2" :message="errors.email" />
                </div>

                <div
                    class="grid gap-2 rounded-2xl border border-[#eadfd4] bg-white p-4 shadow-sm"
                >
                    <Label for="whatsapp">Nomor WhatsApp</Label>
                    <Input
                        id="whatsapp"
                        type="tel"
                        name="whatsapp"
                        :default-value="user.whatsapp"
                        required
                        autocomplete="tel"
                        placeholder="08xxxxxxxxxx"
                    />
                    <InputError class="mt-2" :message="errors.whatsapp" />
                </div>

                <div
                    class="grid gap-2 rounded-2xl border border-[#eadfd4] bg-white p-4 shadow-sm"
                >
                    <Label for="alamat">Alamat pengiriman</Label>
                    <Input
                        id="alamat"
                        name="alamat"
                        :default-value="user.alamat"
                        required
                        autocomplete="street-address"
                        placeholder="Alamat lengkap"
                    />
                    <InputError class="mt-2" :message="errors.alamat" />
                </div>

                <div
                    v-if="page.props.mustVerifyEmail && !user.email_verified_at"
                >
                    <p class="text-muted-foreground -mt-4 text-sm">
                        Your email address is unverified.
                        <Link
                            :href="send()"
                            as="button"
                            class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                        >
                            Click here to re-send the verification email.
                        </Link>
                    </p>

                    <div
                        v-if="page.props.status === 'verification-link-sent'"
                        class="mt-2 text-sm font-medium text-green-600"
                    >
                        A new verification link has been sent to your email
                        address.
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <Button
                        :disabled="processing"
                        data-test="update-profile-button"
                        >Save</Button
                    >
                </div>
            </Form>
        </div>
    </div>

    <DeleteUser />
</template>
