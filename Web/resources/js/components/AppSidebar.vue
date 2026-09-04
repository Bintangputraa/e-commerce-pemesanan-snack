<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    ClipboardList,
    Heart,
    LayoutGrid,
    Package,
    ShoppingBag,
    Users,
} from '@lucide/vue';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import admin from '@/routes/admin';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { home } from '@/routes';
import { edit as profileEdit } from '@/routes/profile';
import type { NavItem } from '@/types';

const page = usePage();
const user = computed(() => page.props.auth.user);
const mainNavItems = computed<NavItem[]>(() =>
    user.value.role === 'admin'
        ? [
              {
                 title: 'Admin Dashboard',
                 href: admin.dashboard(),
                 icon: LayoutGrid,
              },
              {
                 title: 'Kelola Pesanan',
                 href: admin.orders(),
                 icon: ClipboardList,
              },
              { title: 'Kelola Menu', href: admin.items(), icon: Package },
              { title: 'Kelola User', href: admin.users(), icon: Users },
          ]
        : [
              {
                  title: 'History Ordered',
                  href: '/orders/history',
                  icon: ClipboardList,
              },
              {
                  title: 'On Process',
                  href: '/orders/on-process',
                  icon: ShoppingBag,
              },
              { title: 'My Favorite', href: '/orders/favorites', icon: Heart },
              {
                  title: 'Pengaturan Akun',
                  href: profileEdit(),
                  icon: LayoutGrid,
              },
          ],
);
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="home()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
