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
    users: Object,
    roles: Object,
    perPage: Number,
});

loadToast();

const deleteDialog = ref(false);
const form = useForm({});

const data = reactive({
    params: {
        search: props.filters?.search,
        field: props.filters?.field,
        order: props.filters?.order,
        // perPage: props.perPage,
        createOpen: false,
        editOpen: false,
    },
    user: null,
});

const deleteData = () => {
    deleteDialog.value = false;

    form.delete(route('admin.user.destroy', data.user?.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
        onError: () => null,
        onFinish: () => null,
    });
};

const roles = props.roles?.map((role) => ({
    name: role.name,
    code: role.name,
}));

const onPageChange = (event) => {
    router.get(route('admin.user.index'), { page: event.page + 1 }, { preserveState: true });
};

watch(
    () => _.cloneDeep(data.params),
    debounce(() => {
        const params = pickBy(data.params);
        router.get(route('admin.user.index'), params, {
            replace: true,
            preserveState: true,
            preserveScroll: true,
        });
    }, 150),
);
</script>

<template>
    <AppLayout>
        <div class="mx-auto mt-6 w-full rounded-xl border-t-4 border-primary-400 p-6 shadow-md dark:bg-surface-900 md:w-[65%]">
            <Create :show="data.createOpen" @close="data.createOpen = false" :roles="roles" :title="props.title" />
            <Edit :show="data.editOpen" @close="data.editOpen = false" :roles="roles" :user="data.user" :title="props.title" />
            <Button size="small" v-show="can(['create user'])" label="Create" @click="data.createOpen = true" icon="pi pi-plus" />
            <DataTable
                lazy
                :value="users.data"
                paginator
                :rows="users.per_page"
                :totalRecords="users.total"
                :first="(users.current_page - 1) * users.per_page"
                @page="onPageChange"
                tableStyle="min-width: 50rem"
            >
                <template #header>
                    <div class="flex justify-end">
                        <IconField>
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText v-model="data.params.search" placeholder="Keyword Search" />
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
                <Column field="email" header="Email"></Column>
                <Column header="Role">
                    <template #body="slotProps">
                        {{ slotProps.data.roles[0].name }}
                    </template>
                </Column>
                <Column :exportable="false" style="min-width: 12rem">
                    <template #body="slotProps">
                        <Button
                            v-show="can(['update user'])"
                            icon="pi pi-pencil"
                            outlined
                            rounded
                            class="mr-2"
                            @click="((data.editOpen = true), (data.user = slotProps.data))"
                        />
                        <Button
                            v-show="can(['delete user'])"
                            icon="pi pi-trash"
                            outlined
                            rounded
                            severity="danger"
                            @click="
                                deleteDialog = true;
                                data.user = slotProps.data;
                            "
                        />
                    </template>
                </Column>
            </DataTable>

            <Dialog v-model:visible="deleteDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
                <div class="flex items-center gap-4">
                    <i class="pi pi-exclamation-triangle !text-3xl" />
                    <span v-if="data.user"
                        >Are you sure you want to delete <b>{{ data.user.name }}</b
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

<style scoped lang="scss"></style>
