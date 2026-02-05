import { Head, Link, router, usePage } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { ImageOff } from 'lucide-react';
import { useEffect, useState } from 'react';
import Heading from '@/components/heading';
import IsbnScanner from '@/components/isbn-scanner';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

interface Tag {
    id: number;
    name: string;
}

interface Author {
    id: number;
    name: string;
}

interface Book {
    id: number;
    title: string;
    authors: Author[];
    publisher: string | null;
    isbn_13: string | null;
    tags: Tag[];
    image_url?: string;
    created_at: string;
}

interface Props {
    books: {
        data: Book[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: {
        search?: string;
        sort?: string;
        direction?: string;
    };
}

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Books', href: '/' }];

export default function BooksIndex({ books, filters }: Props) {
    const { t } = useLaravelReactI18n();
    const { errors } = usePage().props;
    const [search, setSearch] = useState(filters.search || '');
    const [isSearching, setIsSearching] = useState(false);

    // Handle ISBN scan - redirect to dedicated ISBN lookup endpoint
    const handleIsbnScan = (isbn: string) => {
        // Navigate to ISBN lookup endpoint
        router.visit(`/books/isbn/${isbn}`);
    };

    // Debounce search to avoid too many requests
    useEffect(() => {
        // ISBNエラーがある場合は、自動検索を走らせない（エラー表示を維持するため）
        if (errors.isbn) return;

        const timeout = setTimeout(() => {
            if (search !== filters.search) {
                setIsSearching(true);
                router.get(
                    '/',
                    {
                        search: search || undefined,
                    },
                    {
                        preserveState: true,
                        preserveScroll: true,
                        onFinish: () => setIsSearching(false),
                    },
                );
            }
        }, 500); // 500ms debounce

        return () => clearTimeout(timeout);
    }, [search, filters.search, errors.isbn]);

    const hasActiveFilters = search;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('Books')} />

            <div className="space-y-6 px-4 py-6">
                <Heading title={t('Books')} />

                <div className="space-y-4">
                    <div className="flex gap-2">
                        <Input
                            type="text"
                            placeholder={t(
                                'Search by title, description, publisher, authors, tags...',
                            )}
                            value={search}
                            onChange={(e) => {
                                setSearch(e.target.value);
                                if (errors.isbn) {
                                    router.reload({ only: ['errors'] });
                                }
                            }}
                            className="flex-1"
                        />
                        <IsbnScanner
                            onScan={handleIsbnScan}
                            buttonVariant="outline"
                        />
                    </div>

                    {errors.isbn && (
                        <div className="rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-800 dark:border-red-800 dark:bg-red-950 dark:text-red-200">
                            {errors.isbn}
                        </div>
                    )}

                    {hasActiveFilters && (
                        <div className="flex items-center justify-between text-sm">
                            <p className="text-muted-foreground">
                                {isSearching ? (
                                    <span className="flex items-center gap-2">
                                        <svg
                                            className="h-4 w-4 animate-spin"
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                        >
                                            <circle
                                                className="opacity-25"
                                                cx="12"
                                                cy="12"
                                                r="10"
                                                stroke="currentColor"
                                                strokeWidth="4"
                                            ></circle>
                                            <path
                                                className="opacity-75"
                                                fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                            ></path>
                                        </svg>
                                        {t('Searching...')}
                                    </span>
                                ) : (
                                    t('Showing :count results', {
                                        count: books.total.toString(),
                                    })
                                )}
                            </p>
                            <Button
                                variant="ghost"
                                size="sm"
                                onClick={() => {
                                    setSearch('');
                                }}
                            >
                                {t('Clear filters')}
                            </Button>
                        </div>
                    )}
                </div>

                {books.data.length === 0 ? (
                    <div className="py-12 text-center">
                        <svg
                            className="mx-auto h-12 w-12 text-muted-foreground/50"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth={2}
                                d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        <p className="mt-4 text-lg font-medium text-muted-foreground">
                            {hasActiveFilters
                                ? t('No books found matching your search')
                                : t('No books found')}
                        </p>
                        {hasActiveFilters && (
                            <p className="mt-2 text-sm text-muted-foreground">
                                {t(
                                    'Try adjusting your filters or search terms',
                                )}
                            </p>
                        )}
                    </div>
                ) : (
                    <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
                        {books.data.map((book) => (
                            <Link
                                key={book.id}
                                href={`/books/${book.id}`}
                                className="group"
                            >
                                <Card className="overflow-hidden transition-all hover:shadow-lg">
                                    <CardContent className="p-0">
                                        <div className="relative aspect-[2/3] w-full overflow-hidden bg-muted">
                                            {book.image_url ? (
                                                <img
                                                    src={book.image_url}
                                                    alt={book.title}
                                                    className="h-full w-full object-cover transition-transform group-hover:scale-105"
                                                    loading="lazy"
                                                />
                                            ) : (
                                                <div className="flex h-full w-full flex-col items-center justify-center gap-2 bg-muted text-muted-foreground">
                                                    <ImageOff className="h-10 w-10 opacity-40" />
                                                    <span className="text-xs font-medium">
                                                        No Image
                                                    </span>
                                                </div>
                                            )}
                                        </div>
                                        <div className="p-3">
                                            <h3 className="line-clamp-2 text-sm font-medium">
                                                {book.title}
                                            </h3>
                                        </div>
                                    </CardContent>
                                </Card>
                            </Link>
                        ))}
                    </div>
                )}

                {books.last_page > 1 && (
                    <div className="flex items-center justify-between">
                        <p className="text-sm text-muted-foreground">
                            {t('Showing :from-:to of :total books', {
                                from: (
                                    (books.current_page - 1) * books.per_page +
                                    1
                                ).toString(),
                                to: Math.min(
                                    books.current_page * books.per_page,
                                    books.total,
                                ).toString(),
                                total: books.total.toString(),
                            })}
                        </p>
                        <div className="flex gap-2">
                            {books.current_page > 1 && (
                                <Link
                                    href={`/?page=${books.current_page - 1}`}
                                    preserveState
                                    preserveScroll
                                >
                                    <Button variant="outline">
                                        {t('Previous')}
                                    </Button>
                                </Link>
                            )}
                            {books.current_page < books.last_page && (
                                <Link
                                    href={`/?page=${books.current_page + 1}`}
                                    preserveState
                                    preserveScroll
                                >
                                    <Button variant="outline">
                                        {t('Next')}
                                    </Button>
                                </Link>
                            )}
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
