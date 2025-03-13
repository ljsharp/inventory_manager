<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import Toast from 'primevue/toast';
import { getCurrentInstance, onMounted, ref, shallowRef } from 'vue';
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

const currentTab = shallowRef(null);
const payload = ref(null);
const { proxy } = getCurrentInstance();

onMounted(() => {
    if (proxy.$can(['view stock availability'])) {
        currentTab.value = StockAvailability;
    } else if (proxy.$can(['adjust stock'])) {
        currentTab.value = StockAdjustment;
    } else if (proxy.$can(['transfer stock'])) {
        currentTab.value = StockTransfers;
    }
});

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
        <div class="flex flex-col flex-1 w-full h-full gap-4 p-4 text-sm">
            <div class="p-2">
                <div class="flex items-center justify-center w-full mt-2 gap-x-4">
                    <button
                        v-if="can(['view stock availability'])"
                        @click="setCurrentTab(StockAvailability)"
                        class="px-4 py-2 text-white rounded bg-primary-400 drop-shadow-lg"
                        :class="{ 'bg-primary-500': currentTab === StockAvailability }"
                    >
                        Stock Availability
                    </button>

                    <button
                        v-if="can(['adjust stock'])"
                        @click="setCurrentTab(StockAdjustment)"
                        class="px-4 py-2 text-white rounded bg-primary-400 drop-shadow-lg"
                        :class="{ 'bg-primary-500': currentTab === StockAdjustment }"
                    >
                        Stock Adjustment
                    </button>

                    <button
                        v-if="can(['transfer stock'])"
                        @click="setCurrentTab(StockTransfers)"
                        class="px-4 py-2 text-white rounded bg-primary-400 drop-shadow-lg"
                        :class="{ 'bg-primary-500': currentTab === StockTransfers }"
                    >
                        Stock Transfers
                    </button>
                </div>
                <div class="flex items-center justify-center w-full">
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
