<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Calendar from 'primevue/calendar';
import Column from 'primevue/column';
import ColumnGroup from 'primevue/columngroup';
import DataTable from 'primevue/datatable';
import Row from 'primevue/row';
import { onActivated, ref } from 'vue';

const props = defineProps({
    payload: Object,
});

const emits = defineEmits(['tabComponent']);
const stocks = ref([]);
const totalStockIn = ref(0);
const totalStockOut = ref(0);
const totalStockBalance = ref(0);
const loading = ref(true);
const error = ref<string | null>(null);

const filters = ref({
    start_date: null,
    end_date: null,
    is_variants: props.payload?.is_variants ?? false,
});

const fetchWarehouseBasedStocks = async () => {
    loading.value = true;
    error.value = null;
    try {
        const params = {
            start_date: filters.value.start_date ? filters.value.start_date.toISOString().split('T')[0] : null,
            end_date: filters.value.end_date ? filters.value.end_date.toISOString().split('T')[0] : null,
            is_variants: filters.value.is_variants ? 'yes' : 'no',
        };

        const { data } = await axios.get(route('admin.warehouse.based.stocks', props.payload.warehouse), { params });

        stocks.value = data.stocks;
        totalStockIn.value = data.total_stock_in;
        totalStockOut.value = data.total_stock_out;
        totalStockBalance.value = data.total_stock_balance;
    } catch (err) {
        error.value = 'Failed to load stock balance data';
        console.error(err);
    } finally {
        loading.value = false;
    }
};

const navigateToComponent = (stock: any) => {
    emits('tabComponent', {
        data: {
            warehouse: props.payload!.warehouse,
            is_variants: props.payload!.is_variants,
            start_date: filters.value.start_date ? filters.value.start_date.toISOString().split('T')[0] : null,
            end_date: filters.value.end_date ? filters.value.end_date.toISOString().split('T')[0] : null,
            stock: stock,
        },
        component: 'stock-transaction-histories',
    });
};

onActivated(() => {
    if (!props.payload) return;
    filters.value.is_variants = props.payload.is_variants;
    fetchWarehouseBasedStocks();
});
</script>

<template>
    <div class="flex flex-col flex-1 w-full h-full gap-4 p-4 mx-5 my-4 border-t-4 shadow-lg rounded-xl border-primary-400 dark:bg-surface-900">
        <Head title="Warehouse Based Stocks" />
        <div class="p-4">
            <div class="grid grid-cols-1 gap-4 mb-4 md:grid-cols-4">
                <!-- Date Range Filter -->
                <div>
                    <label class="block mb-1 text-sm font-semibold">Start Date</label>
                    <Calendar v-model="filters.start_date" dateFormat="yy-mm-dd" class="w-full" @date-select="fetchWarehouseBasedStocks" />
                </div>
                <div>
                    <label class="block mb-1 text-sm font-semibold">End Date</label>
                    <Calendar v-model="filters.end_date" dateFormat="yy-mm-dd" class="w-full" @date-select="fetchWarehouseBasedStocks" />
                </div>
            </div>

            <!-- Loading & Error Handling -->
            <div v-if="loading" class="text-center text-gray-500 dark:text-gray-300">Loading...</div>
            <div v-else-if="error" class="text-center text-red-500">{{ error }}</div>

            <!-- Stock Balance Table -->
            <div v-else>
                <DataTable :value="stocks" responsiveLayout="scroll" stripedRows class="p-datatable-sm">
                    <ColumnGroup type="header">
                        <Row>
                            <Column header="Product" />
                            <Column header="Total Stock In" style="text-align: center" />
                            <Column header="Total Stock Out" style="text-align: center" />
                            <Column header="Current Balance" style="text-align: center" />
                        </Row>
                    </ColumnGroup>

                    <Column field="product_name" header="Product">
                        <template #body="{ data }">
                            <span class="cursor-pointer text-primary-500 hover:underline" @click="navigateToComponent(data)">
                                {{ data.product_name }}
                            </span>
                        </template>
                    </Column>

                    <Column field="total_stock_in" header="Total Stock In" style="text-align: left">
                        <template #body="{ data }">
                            <span class="text-green-600 dark:text-green-400">
                                {{ data.total_stock_in }}
                            </span>
                        </template>
                    </Column>

                    <Column field="total_stock_out" header="Total Stock Out" style="text-align: left">
                        <template #body="{ data }">
                            <span class="text-red-600 dark:text-red-400">
                                {{ data.total_stock_out }}
                            </span>
                        </template>
                    </Column>

                    <Column field="current_balance" header="Current Balance" style="text-align: left">
                        <template #body="{ data }">
                            <span class="text-blue-600 dark:text-blue-400">
                                {{ data.current_balance }}
                            </span>
                        </template>
                    </Column>

                    <ColumnGroup type="footer">
                        <Row>
                            <Column footer="Total" />
                            <Column footer-class="text-green-600" :footer="totalStockIn" />
                            <Column footer-class="text-red-600" :footer="totalStockOut" />
                            <Column footer-class="text-blue-600" :footer="totalStockBalance" />
                        </Row>
                    </ColumnGroup>
                </DataTable>
            </div>
        </div>
    </div>
</template>
