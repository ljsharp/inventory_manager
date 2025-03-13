<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import Button from 'primevue/button';
import Column from 'primevue/column';
import ColumnGroup from 'primevue/columngroup';
import DataTable, { DataTablePageEvent } from 'primevue/datatable';
import Row from 'primevue/row';
import { onActivated, ref, watch } from 'vue';

const props = defineProps<{
    payload: {
        stock: any | null;
        warehouse: string;
        start_date: string | null;
        end_date: string | null;
        is_variants: boolean;
    };
}>();

const emits = defineEmits(['tabComponent']);
const stockTransactions = ref([]);
const totalRecords = ref(0);
const loading = ref(false);
const openingBalance = ref(0);
const closingBalance = ref(0);
const totalStockIn = ref(0);
const totalStockOut = ref(0);
const currentPage = ref(1);

const fetchStockTransactions = async () => {
    loading.value = true;
    try {
        const response = await axios.get(route('admin.stock.transactions', props.payload.stock.product_id), {
            params: {
                warehouse: props.payload.warehouse,
                start_date: props.payload.start_date,
                end_date: props.payload.end_date,
                is_variants: props.payload.is_variants ? 'yes' : 'no',
                product_variant_id: props.payload.stock.product_variant_id ?? '',
                page: currentPage.value,
            },
        });

        stockTransactions.value = response.data.stocks.data;
        totalRecords.value = response.data.stocks.total;
        openingBalance.value = response.data.stock_metrics.opening_balance;
        closingBalance.value = response.data.stock_metrics.closing_balance;
        totalStockIn.value = response.data.stock_metrics.total_stock_in;
        totalStockOut.value = response.data.stock_metrics.total_stock_out;
    } catch (error) {
        console.error('Error fetching stock transactions:', error);
    } finally {
        loading.value = false;
    }
};

onActivated(fetchStockTransactions);
watch(() => props.payload, fetchStockTransactions, { deep: true });

const onPageChange = (event: DataTablePageEvent) => {
    currentPage.value = event.page + 1;
    fetchStockTransactions();
};

const onBack = () => {
    emits('tabComponent', {
        data: {
            warehouse: props.payload.warehouse,
            is_variants: props.payload.is_variants,
        },
        component: 'warehouse-based-stocks',
    });
};
</script>

<template>
    <div class="mx-5 my-4 flex h-full flex-1 flex-col gap-4 rounded-xl border-t-4 border-primary-400 p-4 shadow-lg dark:bg-surface-900 md:w-[80px]">
        <Head title="Stock Transactions" />
        <div class="flex items-center gap-5">
            <Button icon="pi pi-arrow-left" severity="success" aria-label="Back" @click="onBack" />
            <h2 class="text-lg font-bold">Stock Transactions</h2>
        </div>

        <DataTable
            :value="stockTransactions"
            :loading="loading"
            paginator
            :rows="10"
            :totalRecords="totalRecords"
            responsiveLayout="scroll"
            @page="onPageChange"
        >
            <!-- Table Header -->
            <ColumnGroup type="header">
                <Row>
                    <Column :colspan="3" />
                    <Column header="Opening Balance" :colspan="1" header-style="text-align:right" />
                    <Column :colspan="1" :header="`${openingBalance}`" header-class="text-align:center" />
                </Row>
                <Row>
                    <Column header="Date" field="date" />
                    <Column header="Product Name" field="product_name" />
                    <Column header="Stock In" field="stock_in" />
                    <Column header="Stock Out" field="stock_out" />
                    <Column header="Current Balance" field="current_balance" />
                </Row>
            </ColumnGroup>

            <Column field="date" header="Date" />
            <Column field="product_name" header="Product Name" />
            <Column field="stock_in" header="Stock In" />
            <Column field="stock_out" header="Stock Out" />
            <Column field="current_balance" header="Current Balance" />
            <!-- <Column field="warehouse.name" header="Warehouse" /> -->

            <!-- Table Footer -->
            <!-- <template #footer>
                <tr class="font-semibold">
                    <td colspan="2" class="pr-4 text-right">Total:</td>
                    <td class="text-center">{{ totalStockIn }}</td>
                    <td class="text-center">{{ totalStockOut }}</td>
                    <td colspan="2" class="pr-4 text-right">Closing Balance: {{ closingBalance }}</td>
                </tr>
            </template> -->
            <ColumnGroup type="footer">
                <Row>
                    <Column footer="Totals:" :colspan="2" footerStyle="text-align:right" />
                    <Column :footer="`${totalStockIn}`" />
                    <Column :footer="`${totalStockOut}`" />
                    <Column />
                </Row>
                <Row>
                    <Column :colspan="4" footer="Closing Balance" footerStyle="text-align:right" />
                    <Column :footer="`${closingBalance}`" />
                </Row>
            </ColumnGroup>
        </DataTable>
    </div>
</template>
