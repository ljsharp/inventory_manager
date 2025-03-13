<script setup lang="ts">
import { Category, Product } from '@/types';
import { useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import Select from 'primevue/select';
import Textarea from 'primevue/textarea';
import { computed, onMounted, ref } from 'vue';

const props = defineProps<{
    product?: Product;
}>();

const productForm = useForm<Product>({
    id: '',
    name: '',
    description: '',
    category_id: null,
    price: null,
    variants: [],
    attributes: {} as Record<string, any>,
});

const categories = ref<Category[]>();
const attributeName = ref('');
const attributeValues = ref('');

const addAttribute = () => {
    if (attributeName.value && attributeValues.value) {
        productForm.attributes[attributeName.value] = attributeValues.value.split(',');
        const attribute: Record<string, any> = {};
        attribute[attributeName.value] = attributeValues.value.split(',');
        console.log(attribute);
        productForm.variants?.forEach((variant) => {
            variant.attributes = { ...variant.attributes, [attributeName.value]: null };
        });
        attributeName.value = '';
        attributeValues.value = '';
    }

    // productForm.variants = computedVariants.value;
};

const addVariant = () => {
    const newAttributes = Object.keys(productForm.attributes).reduce(
        (acc, key) => {
            acc[key] = null;
            return acc;
        },
        {} as Record<string, any>,
    );

    console.log(newAttributes);

    productForm.variants?.push({
        id: null,
        name: '',
        attributes: newAttributes,
        product_id: null,
        price: 0,
        sku: null,
    });
};

// Remove variant
const removeVariant = (index: number) => {
    productForm.variants?.splice(index, 1);
};

const onSelectedAttributeValue = (index: number, key: string) => {
    // const beforeVariant = productForm.variants?[index] ?? null;
    // console.log("before", beforeVariant);
    console.log(index, key);
    // console.log(productForm.variants[index]);
    // productForm.variants?[index].variant_combination[key] =
    //     productForm.variants?[index][key];
    // console.log("after", productForm.variants?[index]);
};

// Generate product variants dynamically
const computedVariants = computed(() => {
    const { attributes } = productForm;

    const keys = Object.keys(attributes);
    if (keys.length === 0) return [];

    const combinations: any[] = [];

    function generateCombinations(prefix: Record<string, string>, depth: number) {
        if (depth === keys.length) {
            console.log('prefix', prefix);
            const name = Object.values(prefix).join(' ');
            combinations.push({ attributes: { ...prefix }, sku: '', name: name.toUpperCase(), price: 0 });
            return;
        }
        console.log('outside prefix', prefix);
        const key = keys[depth];
        for (const value of attributes[key]) {
            generateCombinations({ ...prefix, [key]: value }, depth + 1);
        }
    }

    generateCombinations({}, 0);
    return combinations;
});

const emit = defineEmits(['close']);
onMounted(() => {
    if (props.product?.id) {
        axios.get(route('admin.products.edit', props.product.id)).then((response: { data: { product: Product } }) => {
            console.log(response.data.product);
            productForm.id = response.data.product.id;
            productForm.name = response.data.product.name;
            productForm.description = response.data.product.description;
            productForm.category_id = response.data.product.category_id;
            productForm.price = response.data.product.price;
            productForm.variants = response.data.product.variants;
            productForm.attributes = response.data.product.attributes;
        });
    }

    axios.get(route('admin.categories.create')).then((response: { data: { categories: Category[] | undefined } }) => {
        categories.value = response.data.categories;
    });
});

const submit = () => {
    console.log(productForm.data());

    if (productForm.id) {
        productForm.put(route('admin.products.update', productForm.id), {
            onSuccess: () => {
                emit('close');
            },
        });
    } else {
        productForm.post(route('admin.products.store'), {
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
                <InputText size="small" v-model="productForm.name" class="w-full" />
                <Message size="small" v-if="productForm.errors.name" severity="error">{{ productForm.errors.name }}</Message>
            </div>

            <div class="flex flex-col items-start gap-1">
                <label>Price</label>
                <InputNumber v-model="productForm.price" class="w-full" />
                <Message size="small" v-if="productForm.errors.price" severity="error">{{ productForm.errors.price }}</Message>
            </div>

            <!-- Description -->
            <div class="flex flex-col items-start gap-1">
                <label class="block text-sm font-medium">Description</label>
                <Textarea v-model="productForm.description" class="w-full" rows="3" />
            </div>

            <!-- Category Selection -->
            <div class="flex flex-col items-start gap-1">
                <label class="block text-sm font-medium">Category</label>
                <Select
                    v-model="productForm.category_id"
                    :options="categories"
                    optionLabel="name"
                    optionValue="id"
                    placeholder="Select a category"
                    class="w-full"
                />
                <Message size="small" v-if="productForm.errors.category_id" severity="error">{{ productForm.errors.category_id }}</Message>
            </div>

            <div class="flex w-full flex-col items-start gap-3">
                <div class="flex w-full items-center gap-3">
                    <InputText size="small" v-model="attributeName" placeholder="(e.g., color)" class="w-full" />
                    <InputText size="small" v-model="attributeValues" placeholder="(e.g., red,blue,white)" class="w-full" />
                </div>
                <Button @click.prevent="addAttribute" size="small">Add Attribute</Button>
            </div>

            <ul>
                <li v-for="(values, name) in productForm.attributes" :key="name">{{ name }}: {{ values.join(', ') }}</li>
            </ul>

            <!-- Variants Section -->
            <div class="flex flex-col items-start gap-1">
                <h3>Variants</h3>
                <Button size="small" label="Add Variant" @click="addVariant" class="mb-2" />
                <div v-for="(variant, index) in productForm.variants" :key="index" class="flex w-full flex-col gap-3 border p-2">
                    <div class="flex flex-col items-start gap-1">
                        <!-- <label>Attribute</label> -->
                        <div class="flex items-center gap-3">
                            <template v-for="key in Object.keys(variant.attributes ?? {})">
                                <div class="flex items-center">
                                    <div class="flex flex-col gap-2">
                                        <label for="name" class="font-size capitalize">{{ key }}</label>
                                        <select
                                            class="p-select p-component p-inputwrapper p-inputwrapper-filled w-full p-2"
                                            v-model="variant.attributes[key]"
                                            required
                                            @change="onSelectedAttributeValue(index, key)"
                                        >
                                            <option :value="value" v-for="value in productForm.attributes[key]" :key="value">
                                                {{ value }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="flex flex-col items-start gap-1">
                        <label>Variant Price</label>
                        <InputNumber v-model="variant.price" class="w-full" />
                        <Message size="small" v-if="productForm.errors[`variants.${index}.price`]" severity="error">
                            {{ productForm.errors[`variants.${index}.price`] }}
                        </Message>
                    </div>

                    <span><Button size="small" label="Remove" severity="danger" @click="removeVariant(index)" class="mt-2" /></span>
                </div>
            </div>

            <Button size="small" label="Save" icon="pi pi-save" class="mt-4" type="submit" />
        </div>
    </form>
</template>
