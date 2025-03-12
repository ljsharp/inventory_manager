<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
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
    <AppLayout :breadcrumbs="breadcrumbs" :no-breadcrumbs="noBreadcrumbs">
        <slot />
    </AppLayout>
</template>
