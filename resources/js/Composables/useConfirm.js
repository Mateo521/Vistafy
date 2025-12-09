import { ref } from 'vue';

const confirmState = ref({
    show: false,
    title: '',
    message: '',
    confirmText: 'Confirmar',
    cancelText: 'Cancelar',
    type: 'danger',
    resolver: null, // 🆕 Cambio importante
});

export function useConfirm() {
    const confirm = ({
        title = 'Confirmar acción',
        message,
        confirmText = 'Confirmar',
        cancelText = 'Cancelar',
        type = 'danger',
    }) => {
        return new Promise((resolve) => {
            confirmState.value = {
                show: true,
                title,
                message,
                confirmText,
                cancelText,
                type,
                resolver: resolve, // 🆕 Guardamos el resolver
            };
        });
    };

    const handleConfirm = () => {
        if (confirmState.value.resolver) {
            confirmState.value.resolver(true);
        }
        confirmState.value.show = false;
    };

    const handleCancel = () => {
        if (confirmState.value.resolver) {
            confirmState.value.resolver(false);
        }
        confirmState.value.show = false;
    };

    return {
        confirmState,
        confirm,
        handleConfirm,
        handleCancel,
    };
}
