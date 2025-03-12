<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import Checkbox from 'primevue/checkbox';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import { onActivated, ref, watch } from 'vue';

const emits = defineEmits(['tabComponent']);
const stocks = ref([]);
const warehouses = ref([]);
const totalAvailableStock = ref(0);
const loading = ref(true);
const error = ref<string | null>(null);
const includeVariants = ref(false); // Checkbox state

const fetchStocks = async () => {
    loading.value = true;
    error.value = null;
    try {
        const response = await axios.get(route('admin.stock.availability'), {
            params: { is_variants: includeVariants.value ? 'yes' : 'no' },
        });
        stocks.value = response.data.stocks;
        warehouses.value = response.data.warehouses;
        totalAvailableStock.value = response.data.total_available_stock;
    } catch (err) {
        error.value = 'Failed to load stock data';
        console.error(err);
    } finally {
        loading.value = false;
    }
};

const navigateToComponent = (warehouse: string) => {
    emits('tabComponent', {
        data: {
            warehouse: warehouse,
            is_variants: includeVariants.value,
        },
        component: 'warehouse-based-stocks',
    });
};

// Watch for changes in checkbox state and fetch data accordingly
watch(includeVariants, fetchStocks);

onActivated(fetchStocks);
</script>

<template>
    <div class="mx-5 my-4 flex h-full w-full flex-1 flex-col gap-4 rounded-xl border-t-4 border-primary-400 p-4 shadow-lg">
        <Head title="Stock Availability" />
        <div class="p-4">
            <!-- Filters -->
            <div class="mb-4 flex items-center gap-3">
                <Checkbox v-model="includeVariants" binary />
                <label class="text-sm font-semibold">Include Variants</label>
            </div>

            <!-- Loading & Error Handling -->
            <div v-if="loading" class="text-center text-gray-500 dark:text-gray-300">Loading...</div>
            <div v-else-if="error" class="text-center text-red-500">{{ error }}</div>

            <!-- Stock DataTable -->
            <div v-else>
                <DataTable :value="stocks" :loading="loading" responsiveLayout="scroll" stripedRows class="p-datatable-sm">
                    <!-- Table Columns -->
                    <Column field="product" header="Product" />

                    <!-- Warehouse Columns -->
                    <Column v-for="warehouse in warehouses" :key="warehouse" :field="warehouse">
                        <template #header>
                            <span class="cursor-pointer text-primary-500 hover:underline" @click="navigateToComponent(warehouse)">
                                {{ warehouse }}
                            </span>
                        </template>
                    </Column>

                    <!-- Available Stock -->
                    <Column field="available_stock" header="Available Stock" class="text-center" />
                </DataTable>
            </div>

            <!-- Total Stock -->
            <div class="mt-4 text-lg font-semibold">
                Total Available Stock: <span class="dark:text-white">{{ totalAvailableStock }}</span>
            </div>
        </div>
    </div>
</template>
