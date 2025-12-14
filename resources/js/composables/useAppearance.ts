export function initializeTheme() {
    if (typeof window === 'undefined') {
        return;
    }

    // Always use dark theme
    document.documentElement.classList.add('dark');
}
