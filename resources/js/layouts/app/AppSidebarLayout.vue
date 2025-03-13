<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
    noBreadcrumbs?: boolean;
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
    noBreadcrumbs: () => true,
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" v-if="noBreadcrumbs" />
            <slot />
        </AppContent>
    </AppShell>
</template>
