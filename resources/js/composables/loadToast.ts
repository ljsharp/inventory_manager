import { usePage } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import { computed, watch } from 'vue';

type ToastSeverity = 'success' | 'info' | 'warn' | 'error' | 'secondary' | 'contrast';

interface FlashMessage {
    [key: string]: string;
}

export function loadToast() {
    const toast = useToast();

    const flashMessage = computed<FlashMessage>(() => usePage().props.flash as FlashMessage);

    watch(
        flashMessage,
        (newValue) => {
            if (newValue) {
                for (const [key, value] of Object.entries(newValue) as [ToastSeverity, string][]) {
                    if (value) {
                        toast.add({
                            severity: key,
                            summary: 'Successful',
                            detail: value,
                            life: 3000,
                        });
                    }
                }
            }
        },
        { immediate: true },
    );

    return {
        toast,
    };
}
