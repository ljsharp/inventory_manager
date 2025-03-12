<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import Toast from 'primevue/toast';
import { ref, shallowRef } from 'vue';
import StockAdjustment from './StockAdjustment.vue';
import StockAvailability from './StockAvailability.vue';
import StockTransactionHistories from './StockTransactionHistories.vue';
import StockTransfers from './StockTransfers.vue';
import WarehouseBasedStocks from './WarehouseBasedStocks.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/admin/dashboard',
    },
];

const currentTab = shallowRef(StockAvailability);
const payload = ref(null);

const tabComponent = (event: Record<string, any>) => {
    switch (event.component) {
        case 'stock-transaction-histories':
            payload.value = event.data;
            currentTab.value = StockTransactionHistories;
            break;
        case 'warehouse-based-stocks':
            payload.value = event.data;
            currentTab.value = WarehouseBasedStocks;
            break;
        default:
            payload.value = null;
            currentTab.value = StockAvailability;
            break;
    }
};

const setCurrentTab = (component: any) => {
    payload.value = null;
    currentTab.value = component;
};
</script>

<template>
    <Toast />
    <Head title="Stock Managements" />

    <AppLayout :breadcrumbs="breadcrumbs" :no-breadcrumbs="false">
        <div class="flex h-full w-full flex-1 flex-col gap-4 p-4 text-sm">
            <div class="p-2">
                <div class="mt-2 flex w-full items-center justify-center gap-x-4">
                    <button
                        @click="setCurrentTab(StockAvailability)"
                        class="rounded bg-primary-400 px-4 py-2 text-white drop-shadow-lg"
                        :class="{ 'bg-primary-500': currentTab === StockAvailability }"
                    >
                        Stock Availability
                    </button>

                    <button
                        @click="setCurrentTab(StockAdjustment)"
                        class="rounded bg-primary-400 px-4 py-2 text-white drop-shadow-lg"
                        :class="{ 'bg-primary-500': currentTab === StockAdjustment }"
                    >
                        Stock Adjustment
                    </button>

                    <button
                        @click="setCurrentTab(StockTransfers)"
                        class="rounded bg-primary-400 px-4 py-2 text-white drop-shadow-lg"
                        :class="{ 'bg-primary-500': currentTab === StockTransfers }"
                    >
                        Stock Transfers
                    </button>
                </div>
                <div class="flex w-full items-center justify-center">
                    <transition name="translate" mode="out-in">
                        <KeepAlive>
                            <component :is="currentTab" @tab-component="tabComponent($event)" :payload="payload"></component>
                        </KeepAlive>
                    </transition>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
