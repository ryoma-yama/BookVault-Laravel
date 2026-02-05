import { Head, router, usePage } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

interface Author {
    id: number;
    name: string;
}

interface InventoryStatus {
    total_copies: number;
    borrowed_count: number;
    available_count: number;
}

interface Book {
    id: number;
    isbn_13: string;
    title: string;
    publisher: string;
    published_date: string;
    description: string;
    image_url?: string;
    authors: Author[];
    inventory_status: InventoryStatus;
}

interface Props {
    book: Book;
}

interface User {
    id: number;
    name: string;
    email: string;
}

interface PageProps {
    auth: {
        user: User | null;
    };
}

export default function BookShow({ book }: Props) {
    const { t } = useLaravelReactI18n();
    const { auth } = usePage<PageProps>().props;
    const isAuthenticated = !!auth.user;
    const canBorrow = book.inventory_status.available_count > 0;

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Books',
            href: '/',
        },
        {
            title: book.title,
            href: `/books/${book.id}`,
        },
    ];

    const handleBorrow = () => {
        if (!isAuthenticated) {
            // Use Inertia router for SPA navigation
            router.visit('/login');
        } else {
            // Borrow functionality will be implemented in a future PR
            // For now, this is a placeholder that shows the button is clickable
            alert(t('Borrow functionality coming soon'));
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={book.title} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex flex-col gap-6 md:flex-row">
                    {book.image_url && (
                        <div className="flex-shrink-0">
                            <img
                                src={book.image_url}
                                alt={book.title}
                                className="h-64 w-48 rounded-lg object-cover shadow-md"
                            />
                        </div>
                    )}
                    <div className="flex-1">
                        <h1 className="mb-2 text-3xl font-bold">
                            {book.title}
                        </h1>
                        <div className="space-y-2 text-muted-foreground">
                            <p>
                                <strong>{t('Authors')}:</strong>{' '}
                                {book.authors.map((a) => a.name).join(', ')}
                            </p>
                            <p>
                                <strong>{t('Publisher')}:</strong>{' '}
                                {book.publisher}
                            </p>
                            <p>
                                <strong>{t('Published')}:</strong>{' '}
                                {book.published_date}
                            </p>
                            <p>
                                <strong>ISBN-13:</strong> {book.isbn_13}
                            </p>
                        </div>

                        {/* Inventory Status */}
                        <div className="mt-4 rounded-lg border bg-muted/50 p-4">
                            <h3 className="mb-2 font-semibold">
                                {t('Availability')}
                            </h3>
                            <div className="space-y-1 text-sm">
                                <p>
                                    <span className="font-medium">
                                        {t('Available')}:
                                    </span>{' '}
                                    <span
                                        className={
                                            canBorrow
                                                ? 'text-green-600 dark:text-green-400'
                                                : 'text-red-600 dark:text-red-400'
                                        }
                                    >
                                        {book.inventory_status.available_count}{' '}
                                        / {book.inventory_status.total_copies}
                                    </span>
                                </p>
                                {book.inventory_status.borrowed_count > 0 && (
                                    <p className="text-muted-foreground">
                                        {t(':count currently borrowed', {
                                            count: book.inventory_status.borrowed_count.toString(),
                                        })}
                                    </p>
                                )}
                            </div>
                        </div>

                        {/* Borrow Button */}
                        <div className="mt-6">
                            <Button
                                onClick={handleBorrow}
                                disabled={!canBorrow}
                                size="lg"
                                className="w-full md:w-auto"
                            >
                                {!canBorrow
                                    ? t('Currently Unavailable')
                                    : isAuthenticated
                                      ? t('Borrow')
                                      : t('Login to Borrow')}
                            </Button>
                            {!canBorrow &&
                                book.inventory_status.total_copies === 0 && (
                                    <p className="mt-2 text-sm text-muted-foreground">
                                        {t('No copies available in the library')}
                                    </p>
                                )}
                        </div>

                        <div className="mt-6">
                            <h2 className="mb-2 text-xl font-semibold">
                                {t('Description')}
                            </h2>
                            <div className="prose max-w-none dark:prose-invert">
                                <p>{book.description}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
