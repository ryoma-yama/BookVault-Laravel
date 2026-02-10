import { Head, Link, router, usePage } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { ImageOff } from 'lucide-react';
import { useEffect, useState } from 'react';
import Heading from '@/components/heading';
import IsbnScanner from '@/components/isbn-scanner';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationLink,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import type { BookListItem } from '@/types/domain';

interface Props {
    books: {
        data: BookListItem[];
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
                    <div className="grid grid-cols-2 gap-4 sm:grid-cols-2 lg:grid-cols-6">
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
                                            <h3 className="min-h-[2.5rem] line-clamp-3 text-sm font-medium leading-5">
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
                    <div className="flex flex-col items-center gap-4">
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
                        <Pagination>
                            <PaginationContent>
                                <PaginationItem>
                                    {books.current_page > 1 ? (
                                        <Link
                                            href={`/?page=${books.current_page - 1}${filters.search ? `&search=${filters.search}` : ''}`}
                                            preserveState
                                            preserveScroll
                                        >
                                            <PaginationPrevious>
                                                {t('Previous')}
                                            </PaginationPrevious>
                                        </Link>
                                    ) : (
                                        <PaginationPrevious className="pointer-events-none opacity-50">
                                            {t('Previous')}
                                        </PaginationPrevious>
                                    )}
                                </PaginationItem>

                                {/* Page numbers */}
                                {(() => {
                                    const pages = [];
                                    const showPages = 5; // Show at most 5 page numbers
                                    let startPage = Math.max(
                                        1,
                                        books.current_page - Math.floor(showPages / 2),
                                    );
                                    let endPage = Math.min(
                                        books.last_page,
                                        startPage + showPages - 1,
                                    );

                                    // Adjust start if we're near the end
                                    if (endPage - startPage < showPages - 1) {
                                        startPage = Math.max(1, endPage - showPages + 1);
                                    }

                                    // Show first page + ellipsis
                                    if (startPage > 1) {
                                        pages.push(
                                            <PaginationItem key="1">
                                                <Link
                                                    href={`/?page=1${filters.search ? `&search=${filters.search}` : ''}`}
                                                    preserveState
                                                    preserveScroll
                                                >
                                                    <PaginationLink>1</PaginationLink>
                                                </Link>
                                            </PaginationItem>,
                                        );
                                        if (startPage > 2) {
                                            pages.push(
                                                <PaginationItem key="ellipsis-start">
                                                    <PaginationEllipsis />
                                                </PaginationItem>,
                                            );
                                        }
                                    }

                                    // Show page numbers
                                    for (let i = startPage; i <= endPage; i++) {
                                        pages.push(
                                            <PaginationItem key={i}>
                                                <Link
                                                    href={`/?page=${i}${filters.search ? `&search=${filters.search}` : ''}`}
                                                    preserveState
                                                    preserveScroll
                                                >
                                                    <PaginationLink
                                                        isActive={i === books.current_page}
                                                    >
                                                        {i}
                                                    </PaginationLink>
                                                </Link>
                                            </PaginationItem>,
                                        );
                                    }

                                    // Show ellipsis + last page
                                    if (endPage < books.last_page) {
                                        if (endPage < books.last_page - 1) {
                                            pages.push(
                                                <PaginationItem key="ellipsis-end">
                                                    <PaginationEllipsis />
                                                </PaginationItem>,
                                            );
                                        }
                                        pages.push(
                                            <PaginationItem key={books.last_page}>
                                                <Link
                                                    href={`/?page=${books.last_page}${filters.search ? `&search=${filters.search}` : ''}`}
                                                    preserveState
                                                    preserveScroll
                                                >
                                                    <PaginationLink>
                                                        {books.last_page}
                                                    </PaginationLink>
                                                </Link>
                                            </PaginationItem>,
                                        );
                                    }

                                    return pages;
                                })()}

                                <PaginationItem>
                                    {books.current_page < books.last_page ? (
                                        <Link
                                            href={`/?page=${books.current_page + 1}${filters.search ? `&search=${filters.search}` : ''}`}
                                            preserveState
                                            preserveScroll
                                        >
                                            <PaginationNext>
                                                {t('Next')}
                                            </PaginationNext>
                                        </Link>
                                    ) : (
                                        <PaginationNext className="pointer-events-none opacity-50">
                                            {t('Next')}
                                        </PaginationNext>
                                    )}
                                </PaginationItem>
                            </PaginationContent>
                        </Pagination>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
