import { createInertiaApp } from '@inertiajs/react';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

void createInertiaApp({
    title: (title: string) => (title ? `${title} - ${appName}` : appName),
    layout: () => null,
    strictMode: true,
    progress: {
        color: '#4B5563',
    },
});
