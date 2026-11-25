export const formatNumber = (value: number): string => {
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(value);
};

export const formatDate = (value: string | null | undefined): string => {
    if (!value) {
        return '-';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return '-';
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(date);
};
