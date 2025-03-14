<script setup lang="ts">
import { loadToast } from '@/composables/loadToast';
import AppLayout from '@/layouts/AppLayout.vue';
import { Product, type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable, { DataTablePageEvent } from 'primevue/datatable';
import Dialog from 'primevue/dialog';
import Tag from 'primevue/tag';
import Toast from 'primevue/toast';
import { computed, reactive, ref } from 'vue';
import EditForm from './EditForm.vue';
import Form from './Form.vue';

const props = defineProps<{ products: any; filters: { search: string | null } }>();
const selectedProduct = ref<Partial<Product>>({});

loadToast();
const showDialog = ref(false);
const showEditDialog = ref(false);
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/admin/dashboard',
    },
];

const data = reactive({
    search: props.filters.search,
});

const openDialog = () => {
    selectedProduct.value = {};
    showDialog.value = true;
    showEditDialog.value = false;
};
const editProduct = (product: any) => {
    selectedProduct.value = product;
    showDialog.value = false;
    showEditDialog.value = true;
};

// Delete product
const deleteProduct = (id: number) => {
    if (confirm('Are you sure you want to delete this product?')) {
        router.delete(route('admin.products.destroy', id));
    }
};

const onClose = () => {
    selectedProduct.value = {};
    showDialog.value = false;
    showEditDialog.value = false;
};

// Handle pagination
const fetchProducts = (page: number) => {
    router.get(route('admin.products.index', { page }), {}, { preserveState: true });
};

const filterObject = computed(() => {
    return {
        search: data.search ?? '',
    };
});

const onPageChange = (event: DataTablePageEvent) => {
    fetchProducts(event.page + 1);
    router.get(route('memberships.index'), { page: event.page + 1, ...filterObject.value }, { preserveState: true });
};
</script>

<template>
    <Toast />
    <Head title="Products" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex flex-col flex-1 w-full h-full gap-4 p-4 mx-auto my-4 border-t-4 shadow-lg rounded-xl border-primary-400 dark:bg-surface-900 md:w-2/3"
        >
            <div class="p-2">
                <h2 class="mb-4 text-xl font-bold">Products</h2>

                <Button v-if="can(['create product'])" size="small" label="New Product" @click="openDialog" />

                <DataTable :value="products.data" paginator :rows="products.per_page" :totalRecords="products.total" @page="onPageChange">
                    <Column field="name" header="Product Name" />
                    <Column field="price" header="Price" />
                    <Column header="Variants">
                        <template #body="{ data }">
                            <span v-if="data.variants.length > 0">
                                <Tag class="mr-2" v-for="variant in data.variants" :key="variant.id">
                                    {{
                                        Object.values(variant.attributes)
                                            .map((attr) => attr)
                                            .join(', ')
                                    }}
                                    - GHS{{ variant.price }}
                                </Tag>
                            </span>
                            <span v-else>No variants</span>
                        </template>
                    </Column>
                    <Column header="Actions">
                        <template #body="{ data }">
                            <Button v-if="can(['update product'])" icon="pi pi-pencil" class="p-button-text" @click="editProduct(data)" />
                            <Button
                                v-if="can(['delete product'])"
                                icon="pi pi-trash"
                                class="p-button-text p-button-danger"
                                @click="deleteProduct(data.id)"
                            />
                        </template>
                    </Column>
                </DataTable>

                <Dialog
                    v-model:visible="showDialog"
                    modal
                    :draggable="false"
                    header="Product Form"
                    class="p-dialog-lg"
                    style="width: 30vw; max-width: 800px; height: auto"
                >
                    <Form :product="selectedProduct ?? null" @close="onClose" />
                </Dialog>

                <Dialog
                    v-model:visible="showEditDialog"
                    modal
                    :draggable="false"
                    header="Product Form"
                    class="p-dialog-lg"
                    style="width: 30vw; max-width: 800px; height: auto"
                >
                    <EditForm :product="selectedProduct ?? null" @close="onClose" />
                </Dialog>
            </div>
        </div>
    </AppLayout>
</template>
