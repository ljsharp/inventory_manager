<script setup lang="ts">
import { Product, Warehouse } from '@/types';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import Button from 'primevue/button';
import Dropdown from 'primevue/dropdown';
import InputNumber from 'primevue/inputnumber';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import { computed, onActivated, ref } from 'vue';

const toast = useToast();

const warehouses = ref<Warehouse[]>([]);
const products = ref<Product[]>([]);
const form = useForm({
    product_id: null,
    product_variant_id: null,
    warehouse_id: null,
    quantity: 1,
    stock_type: 'stock_in',
});

const fetchData = async () => {
    try {
        const response = await axios.get(route('admin.get.product.warehouses'));
        warehouses.value = response.data.warehouses;
        products.value = response.data.products;
    } catch (error) {
        console.error('Error fetching data:', error);
    }
};

// Compute product variants dynamically instead of manually updating
const variants = computed(() => {
    return products.value.find((p) => p.id === form.product_id)?.variants ?? [];
});

onActivated(fetchData);

const submitForm = () => {
    if (!form.product_id || !form.warehouse_id) {
        toast.add({
            severity: 'warn',
            summary: 'Validation Error',
            detail: 'Product and Warehouse are required',
            life: 3000,
        });
        return;
    }

    form.post(route('admin.stock.adjustments'), {
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Stock transaction recorded successfully',
                life: 3000,
            });
            form.reset();
        },
        onError: (error) => {
            console.error(error);
        },
    });
};
</script>

<template>
    <div class="w-full max-w-lg p-6 mx-auto mt-6 bg-white border-t-4 shadow-md rounded-xl border-primary-400 dark:bg-surface-900">
        <!-- Warehouse Selection -->
        <div class="mb-4">
            <label class="block mb-1 text-sm font-medium text-gray-700">Warehouse</label>
            <Dropdown
                v-model="form.warehouse_id"
                :options="warehouses"
                optionLabel="name"
                optionValue="id"
                placeholder="Select Warehouse"
                class="w-full"
            />
        </div>

        <!-- Product Selection -->
        <div class="mb-4">
            <label class="block mb-1 text-sm font-medium text-gray-700">Product</label>
            <Dropdown v-model="form.product_id" :options="products" optionLabel="name" optionValue="id" placeholder="Select Product" class="w-full" />
        </div>

        <!-- Product Variant Selection -->
        <div class="mb-4" v-if="variants.length">
            <label class="block mb-1 text-sm font-medium text-gray-700">Product Variant</label>
            <Dropdown
                v-model="form.product_variant_id"
                :options="variants"
                optionLabel="name"
                optionValue="id"
                placeholder="Select Variant"
                class="w-full"
            />
        </div>

        <!-- Quantity Selection -->
        <div class="mb-4">
            <label class="block mb-1 text-sm font-medium text-gray-700">Quantity</label>
            <InputNumber v-model="form.quantity" :min="1" class="w-full" />
        </div>

        <!-- Stock Type Selection -->
        <div class="mb-4">
            <label class="block mb-1 text-sm font-medium text-gray-700">Stock Type</label>
            <Dropdown
                v-model="form.stock_type"
                :options="[
                    { label: 'Stock In', value: 'stock_in' },
                    { label: 'Stock Out', value: 'stock_out' },
                ]"
                optionLabel="label"
                optionValue="value"
                placeholder="Select Stock Type"
                class="w-full"
            />
        </div>

        <!-- Submit Button -->
        <Button label="Submit" class="w-full" @click="submitForm" />

        <!-- Toast Notifications -->
        <Toast />
    </div>
</template>
