<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { Blocks, ChartBarStacked, FolderLock, LayoutGrid, ShieldCheck, ShoppingBasket, Users, Warehouse } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/admin/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Products',
        href: '/admin/products',
        icon: ShoppingBasket,
    },
    {
        title: 'Categories',
        href: '/admin/categories',
        icon: ChartBarStacked,
    },
    {
        title: 'Warehouses',
        href: '/admin/warehouses',
        icon: Warehouse,
    },
    {
        title: 'Stocks',
        href: '/admin/stocks',
        icon: Blocks,
    },
    {
        title: 'Roles',
        href: '/admin/role',
        icon: ShieldCheck,
    },
    {
        title: 'Permissions',
        href: '/admin/permission',
        icon: FolderLock,
    },
    {
        title: 'Users',
        href: '/admin/user',
        icon: Users,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
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
