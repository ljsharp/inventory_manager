<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Dropdown from 'primevue/dropdown';
import InputNumber from 'primevue/inputnumber';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import { computed, onActivated, onDeactivated, ref, watch } from 'vue';

const toast = useToast();

const warehouses = ref<{ id: number; name: string; products: any[] }[]>([]);
const transfers = ref<{ product_id: number | null; product_variant_id: number | null; quantity: number }[]>([]);

const form = useForm({
    source_warehouse_id: null,
    destination_warehouse_id: null,
    transfers: [],
});

// Fetch warehouses and their products
const fetchData = async () => {
    const response = await axios.get(route('admin.get.product.warehouses'));
    warehouses.value = response.data.warehouses;
};

// Filter products based on selected source warehouse
const filteredProducts = computed(() => {
    if (!form.source_warehouse_id) return [];
    const warehouse = warehouses.value.find((w) => w.id === form.source_warehouse_id);
    return warehouse ? warehouse.products : [];
});

// Filter destination warehouses (excluding selected source)
const filteredDestinationWarehouses = computed(() => {
    return warehouses.value.filter((w) => w.id !== form.source_warehouse_id);
});

// Reset transfers when changing source warehouse
watch(
    () => form.source_warehouse_id,
    (newSource) => {
        transfers.value = []; // Clear previous transfers
        form.destination_warehouse_id = null; // Reset destination warehouse
    },
);

// Add new transfer row
const addTransfer = () => {
    transfers.value.push({ product_id: null, product_variant_id: null, quantity: 1 });
};

// Remove transfer row
const removeTransfer = (index: number) => {
    transfers.value.splice(index, 1);
};

// Reset variants when product changes
const onProductSelected = (index: number) => {
    const selectedProduct = filteredProducts.value.find((p) => p.id === transfers.value[index].product_id);
    transfers.value[index].product_variant_id = selectedProduct?.variants?.length ? null : undefined;
};

// Prevent duplicate product and variant selections
const hasDuplicates = () => {
    const seen = new Set();
    for (const transfer of transfers.value) {
        const key = `${transfer.product_id}-${transfer.product_variant_id}`;
        if (seen.has(key)) return true;
        seen.add(key);
    }
    return false;
};

// Submit form
const submitForm = () => {
    if (!form.source_warehouse_id || !form.destination_warehouse_id) {
        toast.add({ severity: 'warn', summary: 'Validation Error', detail: 'Select both warehouses', life: 3000 });
        return;
    }

    if (hasDuplicates()) {
        toast.add({ severity: 'error', summary: 'Validation Error', detail: 'Duplicate products/variants not allowed', life: 3000 });
        return;
    }

    form.transfers = transfers.value.map((t) => ({
        ...t,
        source_warehouse_id: form.source_warehouse_id,
        destination_warehouse_id: form.destination_warehouse_id,
    }));

    form.post(route('admin.stock.transfers'), {
        onSuccess: () => {
            toast.add({ severity: 'success', summary: 'Success', detail: 'Stock transferred successfully', life: 3000 });
            form.reset();
            transfers.value = [];
        },
        onError: (error) => {
            console.error(error);
            toast.add({ severity: 'error', summary: 'Error', detail: error.message, life: 3000 });
        },
    });
};

onActivated(fetchData);

onDeactivated(() => {
    form.reset();
});
</script>

<template>
    <div class="mx-auto mt-6 w-full max-w-4xl rounded-xl border-t-4 border-primary-400 bg-white p-6 shadow-md dark:bg-surface-900">
        <!-- Warehouse Selection -->
        <div class="mb-4 grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Source Warehouse</label>
                <Dropdown
                    v-model="form.source_warehouse_id"
                    :options="warehouses"
                    optionLabel="name"
                    optionValue="id"
                    placeholder="Select Warehouse"
                    class="w-full"
                />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Destination Warehouse</label>
                <Dropdown
                    v-model="form.destination_warehouse_id"
                    :options="filteredDestinationWarehouses"
                    optionLabel="name"
                    optionValue="id"
                    placeholder="Select Warehouse"
                    class="w-full"
                />
            </div>
        </div>

        <!-- Transfer Table -->
        <DataTable :value="transfers" class="p-datatable-sm">
            <Column field="product_id" header="Product">
                <template #body="{ data, index }">
                    <Dropdown
                        v-model="transfers[index].product_id"
                        :options="filteredProducts"
                        optionLabel="name"
                        optionValue="id"
                        placeholder="Select Product"
                        class="w-full"
                        @change="onProductSelected(index)"
                    />
                </template>
            </Column>

            <Column field="product_variant_id" header="Variant">
                <template #body="{ data, index }">
                    <Dropdown
                        v-model="transfers[index].product_variant_id"
                        :options="filteredProducts.find((p) => p.id === transfers[index].product_id)?.variants || []"
                        optionLabel="name"
                        optionValue="id"
                        placeholder="Select Variant"
                        class="w-full"
                    />
                </template>
            </Column>

            <Column field="quantity" header="Quantity">
                <template #body="{ data, index }">
                    <InputNumber v-model="transfers[index].quantity" :min="1" class="w-full" />
                </template>
            </Column>

            <Column>
                <template #body="{ index }">
                    <Button icon="pi pi-trash" class="p-button-danger p-button-sm" @click="removeTransfer(index)" />
                </template>
            </Column>
        </DataTable>

        <!-- Add Row Button -->
        <div class="mt-4 flex justify-between">
            <Button label="Add Transfer Row" icon="pi pi-plus" class="p-button-outlined" @click="addTransfer" />
        </div>

        <!-- Submit Button -->
        <Button label="Submit Transfers" class="mt-4 w-full" @click="submitForm" />

        <!-- Toast Notifications -->
        <Toast />
    </div>
</template>
