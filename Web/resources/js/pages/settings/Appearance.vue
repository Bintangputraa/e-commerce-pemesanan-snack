<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import Heading from '@/components/Heading.vue';
import { edit } from '@/routes/appearance';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Appearance settings',
                href: edit(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Appearance settings" />

    <h1 class="sr-only">Appearance settings</h1>

    <div
        class="min-h-screen bg-[#fcfaf7] px-5 py-8 text-[#292724] sm:px-8 lg:py-12"
    >
        <div
            class="mx-auto max-w-3xl space-y-7 rounded-3xl border border-[#eadfd4] bg-white p-6 shadow-[0_8px_30px_rgba(82,59,38,0.06)] sm:p-8"
        >
            <Heading
                variant="small"
                title="Appearance settings"
                description="Update the appearance settings for your account"
            />
            <AppearanceTabs />
        </div>
    </div>
</template>
