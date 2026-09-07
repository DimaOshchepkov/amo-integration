export function formatPhoneInput(value: string): string {
    let digits = value.replace(/\D/g, '');

    if (digits.startsWith('8')) {
        digits = `7${digits.slice(1)}`;
    }

    digits = digits.replace(/^7/, '').slice(0, 10);


    let formatted = `+7 (${digits.slice(0, 3)}`;

    if (digits.length >= 4) {
        formatted += `) ${digits.slice(3, 6)}`;
    }

    if (digits.length >= 7) {
        formatted += `-${digits.slice(6, 8)}`;
    }

    if (digits.length >= 9) {
        formatted += `-${digits.slice(8, 10)}`;
    }

    return formatted;
}

export function normalizePhone(value: string): string {
    const digits = value.replace(/\D/g, '');

    if (digits.length === 11 && /^[78]/.test(digits)) {
        return `+7${digits.slice(1)}`;
    }

    if (digits.length === 10) {
        return `+7${digits}`;
    }

    return value.trim();
}
