<script setup lang="ts">
import { Category } from '@/types';
import { useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import Textarea from 'primevue/textarea';
import { onMounted } from 'vue';

const props = defineProps<{
    category?: Category;
}>();

const categoryForm = useForm<Category>({
    id: null,
    name: '',
    description: '',
});

const emit = defineEmits(['close']);
onMounted(() => {
    if (props.category) {
        categoryForm.id = props.category.id;
        categoryForm.name = props.category.name;
        categoryForm.description = props.category.description;
    }
});

const submit = () => {
    console.log(categoryForm.data());
    if (categoryForm.id) {
        categoryForm.put(route('admin.categories.update', categoryForm.id), {
            onSuccess: () => {
                emit('close');
            },
        });
    } else {
        categoryForm.post(route('admin.categories.store'), {
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
                <InputText v-model="categoryForm.name" class="w-full" />
                <Message size="small" v-if="categoryForm.errors.name" severity="error">{{ categoryForm.errors.name }}</Message>
            </div>

            <div class="flex flex-col items-start gap-1">
                <label>Description</label>
                <Textarea v-model="categoryForm.description" class="w-full" />
                <Message size="small" v-if="categoryForm.errors.description" severity="error">{{ categoryForm.errors.description }}</Message>
            </div>

            <Button label="Save" icon="pi pi-save" class="mt-4" type="submit" />
        </div>
    </form>
</template>
