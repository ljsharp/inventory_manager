<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import Card from 'primevue/card';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';

defineProps({
    totalProducts: Number,
    totalStock: Number,
    outOfStockProducts: Number,
    lowStockProducts: Number,
    totalWarehouses: Number,
    mostStockedWarehouse: Object,
    recentTransfers: Array,
    recentTransactions: Array,
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col flex-1 h-full gap-4 p-4 text-sm rounded-xl">
            <div class="p-6">
                <div class="grid grid-cols-3 gap-4">
                    <Card class="drop-shadow-md">
                        <template #title>Total Products</template>
                        <template #content>{{ totalProducts }}</template>
                    </Card>
                    <Card class="drop-shadow-md">
                        <template #title>Total Stock</template>
                        <template #content>{{ totalStock }}</template>
                    </Card>
                    <Card class="drop-shadow-md">
                        <template #title>Out of Stock</template>
                        <template #content>
                            <span class="text-red-500">{{ outOfStockProducts }}</span>
                        </template>
                    </Card>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-6">
                    <Card class="drop-shadow-md">
                        <template #title>Total Warehouses</template>
                        <template #content>{{ totalWarehouses }}</template>
                    </Card>
                    <Card class="drop-shadow-md">
                        <template #title>Most Stocked Warehouse</template>
                        <template #content>{{ mostStockedWarehouse?.name || 'N/A' }}</template>
                    </Card>
                </div>

                <div class="mt-6 shadow-md card">
                    <h2 class="text-xl font-semibold">Recent Stock Transfers</h2>
                    <DataTable :value="recentTransfers">
                        <Column field="source_warehouse_id" header="From" />
                        <Column field="destination_warehouse_id" header="To" />
                        <Column field="product_id" header="Product" />
                        <Column field="quantity" header="Qty" />
                        <Column field="created_at" header="Date" />
                    </DataTable>
                </div>

                <div class="mt-6 shadow-md card">
                    <h2 class="text-xl font-semibold">Recent Stock Transactions</h2>
                    <DataTable :value="recentTransactions">
                        <Column field="stock_id" header="Stock ID" />
                        <Column field="quantity" header="Qty" />
                        <Column field="stock_in" header="Stock In" />
                        <Column field="stock_out" header="Stock Out" />
                        <Column field="created_at" header="Date" />
                    </DataTable>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
