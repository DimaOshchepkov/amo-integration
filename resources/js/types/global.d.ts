declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            flash?: {
                error?: string | null;
                success?: string | null;
            };
            [key: string]: unknown;
        };
    }
}
