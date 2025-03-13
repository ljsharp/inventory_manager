<script setup lang="ts">
import { loadToast } from '@/composables/loadToast';
import AppLayout from '@/layouts/AppLayout.vue';
import { Category, type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import { ref } from 'vue';
import Form from './Form.vue';

defineProps<{
    categories: Category[];
}>();

loadToast();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/admin/dashboard',
    },
];

const dialogVisible = ref(false);
const categoryForm = ref<Partial<Category>>({});

const openDialog = (category?: Category) => {
    categoryForm.value = category ? { ...category } : { name: '', description: '' };
    dialogVisible.value = true;
};

const deleteCategory = (id: number) => {
    router.delete(route('admin.categories.destroy', id), {
        onBefore: () => confirm('Are you sure you want to delete this category?'),
    });
};
</script>

<template>
    <Toast />
    <Head title="Categories" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex flex-col flex-1 w-full h-full gap-4 p-4 mx-auto my-4 border-t-4 shadow-lg rounded-xl border-primary-400 dark:bg-surface-900 md:w-2/3"
        >
            <div class="p-2">
                <h2 class="mb-4 text-xl font-bold">Categories</h2>

                <Button v-if="can(['create category'])" label="Add Category" icon="pi pi-plus" size="small" class="mb-4" @click="openDialog()" />

                <DataTable :value="categories" responsiveLayout="scroll">
                    <Column field="name" header="Name"></Column>
                    <Column field="description" header="Description"></Column>
                    <Column>
                        <template #body="{ data }">
                            <Button v-if="can(['update category'])" icon="pi pi-pencil" class="p-button-text" @click="openDialog(data)" />
                            <Button
                                v-if="can(['delete category'])"
                                icon="pi pi-trash"
                                class="p-button-text p-button-danger"
                                @click="deleteCategory(data.id)"
                            />
                        </template>
                    </Column>
                </DataTable>

                <Dialog
                    v-model:visible="dialogVisible"
                    modal
                    :closable="true"
                    :draggable="false"
                    header="Category Form"
                    class="p-dialog-lg"
                    style="width: 30vw; max-width: 800px; height: auto"
                >
                    <Form :category="categoryForm" @close="dialogVisible = false" />
                </Dialog>
            </div>
        </div>
    </AppLayout>
</template>
