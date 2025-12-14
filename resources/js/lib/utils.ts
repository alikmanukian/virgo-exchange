import { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function urlIsActive(
    urlToCheck: NonNullable<InertiaLinkProps['href']>,
    currentUrl: string,
) {
    return toUrl(urlToCheck) === currentUrl;
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}

/**
 * Format a number as USD currency with 2 decimal places
 */
export function formatUsd(value: string | number): string {
    const num = typeof value === 'string' ? parseFloat(value) : value;
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(num);
}

/**
 * Format a number with 6 decimal places (for crypto amounts)
 */
export function formatAmount(value: string | number): string {
    const num = typeof value === 'string' ? parseFloat(value) : value;
    return num.toFixed(6);
}

/**
 * Format a crypto amount with variable precision
 * Shows 8 decimals for small values, 4 for larger ones
 */
export function formatCrypto(value: string | number, showFull = false): string {
    const num = typeof value === 'string' ? parseFloat(value) : value;
    if (showFull || num < 0.01) {
        return num.toFixed(8).replace(/\.?0+$/, '');
    }
    return num.toFixed(4);
}

/**
 * Format a date string for display
 */
export function formatDate(date: string): string {
    return new Intl.DateTimeFormat('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(date));
}
