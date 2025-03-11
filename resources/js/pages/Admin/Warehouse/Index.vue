<script setup lang="ts">
import { loadToast } from '@/composables/loadToast';
import AppLayout from '@/layouts/AppLayout.vue';
import { Warehouse, type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import { ref } from 'vue';
import Form from './Form.vue';

defineProps<{
    warehouses: Warehouse[];
}>();

loadToast();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/admin/dashboard',
    },
];

const dialogVisible = ref(false);
const warehouseForm = ref<Partial<Warehouse>>({});

const openDialog = (warehouse?: Warehouse) => {
    warehouseForm.value = warehouse ? { ...warehouse } : { name: '', location: '', capacity: 1 };
    dialogVisible.value = true;
};

const deleteWarehouse = (id: number) => {
    router.delete(route('admin.warehouses.destroy', id), {
        onBefore: () => confirm('Are you sure you want to delete this warehouse?'),
    });
};
</script>

<template>
    <Toast />
    <Head title="Warehouses" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto my-4 flex h-full w-full flex-1 flex-col gap-4 rounded-xl border-t-4 border-primary-400 p-4 shadow-lg md:w-2/3">
            <div class="p-2">
                <h2 class="mb-4 text-xl font-bold">Warehouses</h2>

                <Button label="Add Warehouse" icon="pi pi-plus" size="small" class="mb-4" @click="openDialog()" />

                <DataTable :value="warehouses" responsiveLayout="scroll">
                    <Column field="name" header="Name"></Column>
                    <Column field="location" header="Location"></Column>
                    <Column field="contact_info" header="Contact Info"></Column>
                    <Column field="capacity" header="Capacity"></Column>
                    <Column>
                        <template #body="{ data }">
                            <Button icon="pi pi-pencil" class="p-button-text" @click="openDialog(data)" />
                            <Button icon="pi pi-trash" class="p-button-text p-button-danger" @click="deleteWarehouse(data.id)" />
                        </template>
                    </Column>
                </DataTable>

                <Dialog
                    v-model:visible="dialogVisible"
                    modal
                    :closable="true"
                    :draggable="false"
                    header="Warehouse Form"
                    class="p-dialog-lg"
                    style="width: 30vw; max-width: 800px; height: auto"
                >
                    <Form :warehouse="warehouseForm" @close="dialogVisible = false" />
                </Dialog>
            </div>
        </div>
    </AppLayout>
</template>
