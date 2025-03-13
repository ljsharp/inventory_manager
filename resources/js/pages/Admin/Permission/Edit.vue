<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import { watchEffect } from 'vue';

const props = defineProps({
    show: Boolean,
    title: String,
    permission: Object,
});

const emit = defineEmits(['close']);

const form = useForm({
    name: '',
    _method: 'PUT',
});

const update = () => {
    console.log(form.data());
    form.post(route('admin.permission.update', props.permission?.id), {
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
            form.reset();
        },
        onError: () => null,
        onFinish: () => null,
    });
};

watchEffect(() => {
    if (props.show) {
        form.errors = {};
        form.name = props.permission?.name;
    }
});
</script>

<template>
    <Dialog v-model:visible="props.show" position="center" modal :header="'Update ' + title" :style="{ width: '30rem' }" :closable="false">
        <form @submit.prevent="update">
            <div class="flex flex-col gap-4">
                <div class="flex flex-col gap-2">
                    <label for="name">Name</label>
                    <InputText id="name" v-model="form.name" class="flex-auto" autocomplete="off" placeholder="Name" />
                    <Message size="small" v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</Message>
                </div>
                <div class="flex justify-end gap-2">
                    <Button size="small" type="button" label="Cancel" severity="secondary" @click="emit('close')"></Button>
                    <Button size="small" type="submit" label="Update"></Button>
                </div>
            </div>
        </form>
    </Dialog>
</template>
