<script setup lang="ts">
import { Warehouse } from '@/types';
import { useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputMask from 'primevue/inputmask';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import { onMounted } from 'vue';

const props = defineProps<{
    warehouse?: Warehouse;
}>();

const warehouseForm = useForm<Warehouse>({
    id: null,
    name: '',
    contact_info: '',
    location: '',
    capacity: 1,
});

const emit = defineEmits(['close']);
onMounted(() => {
    if (props.warehouse) {
        warehouseForm.id = props.warehouse.id;
        warehouseForm.name = props.warehouse.name;
        warehouseForm.contact_info = props.warehouse.contact_info;
        warehouseForm.location = props.warehouse.location;
        warehouseForm.capacity = props.warehouse.capacity;
    }
});

const submit = () => {
    console.log(warehouseForm.data());
    if (warehouseForm.id) {
        warehouseForm.put(route('admin.warehouses.update', warehouseForm.id), {
            onSuccess: () => {
                emit('close');
            },
        });
    } else {
        warehouseForm.post(route('admin.warehouses.store'), {
            onSuccess: () => {
                emit('close');
            },
        });
    }
};
</script>

<template>
    <form @submit.prevent="submit">
        <div class="flex flex-col gap-4">
            <div class="flex flex-col items-start gap-1">
                <label>Name</label>
                <InputText v-model="warehouseForm.name" class="w-full" />
                <Message v-if="warehouseForm.errors.name" severity="error">{{ warehouseForm.errors.name }}</Message>
            </div>

            <div class="flex flex-col items-start gap-1">
                <label>Location</label>
                <InputText v-model="warehouseForm.location" class="w-full" />
                <Message v-if="warehouseForm.errors.location" severity="error">{{ warehouseForm.errors.location }}</Message>
            </div>

            <div class="flex flex-col items-start gap-1">
                <label>Contact Info</label>
                <InputMask id="phone" v-model="warehouseForm.contact_info" mask="9999999999" placeholder="0540000009" fluid />
                <Message v-if="warehouseForm.errors.contact_info" severity="error">{{ warehouseForm.errors.contact_info }}</Message>
            </div>

            <div class="flex flex-col items-start gap-1">
                <label>Capacity</label>
                <InputText type="number" v-model="warehouseForm.capacity" class="w-full" />
                <Message v-if="warehouseForm.errors.capacity" severity="error">{{ warehouseForm.errors.capacity }}</Message>
            </div>

            <Button label="Save" icon="pi pi-save" class="mt-4" type="submit" />
        </div>
    </form>
</template>
