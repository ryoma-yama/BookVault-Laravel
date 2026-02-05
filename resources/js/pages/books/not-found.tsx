import { Head } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

interface Props {
    error?: string;
    statusCode?: number;
}

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Books', href: '/' }];

export default function BookNotFound({ error }: Props) {
    const { t } = useLaravelReactI18n();

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('Book Not Found')} />

            <div className="flex min-h-[400px] flex-col items-center justify-center px-4 py-12">
                <div className="text-center">
                    <svg
                        className="mx-auto h-24 w-24 text-muted-foreground/50"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth={2}
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                        />
                    </svg>
                    <h1 className="mt-6 text-3xl font-bold">
                        {t('Book Not Found')}
                    </h1>
                    {error && (
                        <p className="mt-3 text-lg text-muted-foreground">
                            {error}
                        </p>
                    )}
                    <p className="mt-2 text-muted-foreground">
                        {t('The book you are looking for does not exist.')}
                    </p>
                    <div className="mt-8">
                        <Button
                            onClick={() => window.history.back()}
                            variant="outline"
                            className="mr-2"
                        >
                            {t('Go Back')}
                        </Button>
                        <Button onClick={() => (window.location.href = '/')}>
                            {t('Browse Books')}
                        </Button>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
