<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

import { loadToast } from '@/composables/loadToast';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import pkg from 'lodash';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Dialog from 'primevue/dialog';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import { reactive, ref, watch } from 'vue';
import Create from './Create.vue';
import Edit from './Edit.vue';
const { _, debounce, pickBy } = pkg;

const props = defineProps({
    title: String,
    filters: Object,
    permissions: Object,
    roles: Object,
    perPage: Number,
});

loadToast();

const deleteDialog = ref(false);
const form = useForm({});

const data = reactive({
    params: {
        search: props.filters.search,
        field: props.filters.field,
        order: props.filters.order,
        createOpen: false,
        editOpen: false,
    },
    permission: null,
});

const deleteData = () => {
    deleteDialog.value = false;

    form.delete(route('admin.permission.destroy', data.permission?.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
        onError: () => null,
        onFinish: () => null,
    });
};

const onPageChange = (event) => {
    router.get(route('admin.permission.index'), { page: event.page + 1 }, { preserveState: true });
};

watch(
    () => _.cloneDeep(data.params),
    debounce(() => {
        const params = pickBy(data.params);
        router.get(route('admin.permission.index'), params, {
            replace: true,
            preserveState: true,
            preserveScroll: true,
        });
    }, 150),
);
</script>

<template>
    <AppLayout :no-breadcrumbs="true">
        <div class="mx-auto mt-6 w-full rounded-xl border-t-4 border-primary-400 p-6 shadow-md dark:bg-surface-900 md:w-[60%]">
            <Create :show="data.createOpen" @close="data.createOpen = false" :title="props.title" />
            <Edit :show="data.editOpen" @close="data.editOpen = false" :permission="data.permission" :title="props.title" />
            <Button
                v-if="can(['create permission'])"
                size="small"
                v-show="can(['create permission'])"
                label="Create"
                @click="data.createOpen = true"
                icon="pi pi-plus"
            />
            <DataTable
                lazy
                :value="permissions.data"
                paginator
                :rows="permissions.per_page"
                :totalRecords="permissions.total"
                :first="(permissions.current_page - 1) * permissions.per_page"
                @page="onPageChange"
                tableStyle="min-width: 50rem"
            >
                <template #header>
                    <div class="flex justify-end">
                        <IconField>
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText size="small" v-model="data.params.search" placeholder="Keyword Search" />
                        </IconField>
                    </div>
                </template>
                <template #empty> No data found. </template>
                <template #loading> Loading data. Please wait. </template>

                <Column header="No">
                    <template #body="slotProps">
                        {{ slotProps.index + 1 }}
                    </template>
                </Column>

                <Column field="name" header="Name"></Column>
                <Column field="guard_name" header="Guard"></Column>
                <Column :exportable="false" style="min-width: 12rem">
                    <template #body="slotProps">
                        <Button
                            v-show="can(['update permission'])"
                            icon="pi pi-pencil"
                            outlined
                            rounded
                            class="mr-2"
                            @click="((data.editOpen = true), (data.permission = slotProps.data))"
                        />
                        <Button
                            v-show="can(['delete permission'])"
                            icon="pi pi-trash"
                            outlined
                            rounded
                            severity="danger"
                            @click="
                                deleteDialog = true;
                                data.permission = slotProps.data;
                            "
                        />
                    </template>
                </Column>
            </DataTable>

            <Dialog v-model:visible="deleteDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
                <div class="flex items-center gap-4">
                    <i class="pi pi-exclamation-triangle !text-3xl" />
                    <span v-if="data.permission"
                        >Are you sure you want to delete <b>{{ data.permission.name }}</b
                        >?</span
                    >
                </div>
                <template #footer>
                    <Button label="No" icon="pi pi-times" text @click="deleteDialog = false" />
                    <Button label="Yes" icon="pi pi-check" @click="deleteData" />
                </template>
            </Dialog>
        </div>
    </AppLayout>
</template>
