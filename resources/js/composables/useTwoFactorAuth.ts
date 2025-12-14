import { qrCode, recoveryCodes, secretKey } from '@/routes/two-factor';
import { createFetch } from '@vueuse/core';
import { computed, ref } from 'vue';

const useApiFetch = createFetch({
    options: {
        immediate: false,
        headers: {
            Accept: 'application/json',
        },
    },
    fetchOptions: {
        credentials: 'same-origin',
    },
});

const errors = ref<string[]>([]);
const manualSetupKey = ref<string | null>(null);
const qrCodeSvg = ref<string | null>(null);
const recoveryCodesList = ref<string[]>([]);

const hasSetupData = computed<boolean>(
    () => qrCodeSvg.value !== null && manualSetupKey.value !== null,
);

export const useTwoFactorAuth = () => {
    const fetchQrCode = async (): Promise<void> => {
        const { data, error } = await useApiFetch(qrCode.url()).json<{
            svg: string;
            url: string;
        }>();

        if (error.value) {
            errors.value.push('Failed to fetch QR code');
            qrCodeSvg.value = null;
            return;
        }

        qrCodeSvg.value = data.value?.svg ?? null;
    };

    const fetchSetupKey = async (): Promise<void> => {
        const { data, error } = await useApiFetch(secretKey.url()).json<{
            secretKey: string;
        }>();

        if (error.value) {
            errors.value.push('Failed to fetch a setup key');
            manualSetupKey.value = null;
            return;
        }

        manualSetupKey.value = data.value?.secretKey ?? null;
    };

    const clearSetupData = (): void => {
        manualSetupKey.value = null;
        qrCodeSvg.value = null;
        clearErrors();
    };

    const clearErrors = (): void => {
        errors.value = [];
    };

    const clearTwoFactorAuthData = (): void => {
        clearSetupData();
        clearErrors();
        recoveryCodesList.value = [];
    };

    const fetchRecoveryCodes = async (): Promise<void> => {
        clearErrors();

        const { data, error } = await useApiFetch(recoveryCodes.url()).json<
            string[]
        >();

        if (error.value) {
            errors.value.push('Failed to fetch recovery codes');
            recoveryCodesList.value = [];
            return;
        }

        recoveryCodesList.value = data.value ?? [];
    };

    const fetchSetupData = async (): Promise<void> => {
        clearErrors();
        await Promise.all([fetchQrCode(), fetchSetupKey()]);
    };

    return {
        qrCodeSvg,
        manualSetupKey,
        recoveryCodesList,
        errors,
        hasSetupData,
        clearSetupData,
        clearErrors,
        clearTwoFactorAuthData,
        fetchQrCode,
        fetchSetupKey,
        fetchSetupData,
        fetchRecoveryCodes,
    };
};
