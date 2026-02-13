'use no memo';
import { Link, router } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { useEffect, useRef, useState } from 'react';
import { PageNumbersPagination } from '@/components/page-numbers-pagination';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppCommonLayout from '@/layouts/app-common-layout';

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
    tags: Tag[];
    copies_count: number;
}

interface PaginatedBooks {
    data: Book[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Props {
    books: PaginatedBooks;
    filters: {
        search?: string;
    };
}

export default function AdminBooksIndex({ books, filters }: Props) {
    const { t } = useLaravelReactI18n();

    // Track the last search value that was actually submitted to prevent infinite loops
    const lastSubmittedSearch = useRef(filters.search || '');

    // Initialize search from filters
    const [search, setSearch] = useState(filters.search || '');
    const [isSearching, setIsSearching] = useState(false);

    // Debounce search to avoid too many requests
    useEffect(() => {
        const timeout = setTimeout(() => {
            // Only trigger search if the search value has actually changed from what was last submitted
            if (search !== lastSubmittedSearch.current) {
                setIsSearching(true);
                lastSubmittedSearch.current = search;
                router.get(
                    '/admin/books',
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
    }, [search]);

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('Admin'),
            href: '/admin/books',
        },
        {
            title: t('Books'),
            href: '/admin/books',
        },
    ];

    const hasActiveFilters = search;

    return (
        <AppCommonLayout title={t('Books')} breadcrumbs={breadcrumbs}>
            <div className="mb-4 flex items-center gap-2">
                <Input
                    placeholder={t(
                        'Search by title, description, publisher, authors, tags...',
                    )}
                    value={search}
                    onChange={(event) => setSearch(event.target.value)}
                    className="flex-1"
                />
                <Link href="/admin/books/create">
                    <Button>{t('Add New Book')}</Button>
                </Link>
            </div>

            {hasActiveFilters && (
                <div className="mb-4 flex items-center justify-between text-sm">
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

            <div className="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>{t('Title')}</TableHead>
                            <TableHead>{t('Tags')}</TableHead>
                            <TableHead className="text-center">
                                {t('Inventory')}
                            </TableHead>
                            <TableHead>{t('Actions')}</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {books.data.length > 0 ? (
                            books.data.map((book) => (
                                <TableRow key={book.id}>
                                    <TableCell>
                                        <div className="font-medium">
                                            {book.title}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        {book.tags.length === 0 ? (
                                            <span className="text-muted-foreground">
                                                {t('No tags')}
                                            </span>
                                        ) : (
                                            <div className="flex flex-wrap gap-1">
                                                {book.tags.map((tag) => (
                                                    <Badge
                                                        key={tag.id}
                                                        variant="secondary"
                                                    >
                                                        #{tag.name}
                                                    </Badge>
                                                ))}
                                            </div>
                                        )}
                                    </TableCell>
                                    <TableCell className="text-center">
                                        <span
                                            className={
                                                book.copies_count > 0
                                                    ? 'text-foreground'
                                                    : 'text-muted-foreground'
                                            }
                                        >
                                            {book.copies_count}
                                        </span>
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex gap-2">
                                            <Link
                                                href={`/admin/books/${book.id}/edit`}
                                            >
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                >
                                                    {t('Edit')}
                                                </Button>
                                            </Link>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))
                        ) : (
                            <TableRow>
                                <TableCell
                                    colSpan={4}
                                    className="h-24 text-center"
                                >
                                    {hasActiveFilters
                                        ? t(
                                              'No books found matching your search',
                                          )
                                        : t('No books found')}
                                </TableCell>
                            </TableRow>
                        )}
                    </TableBody>
                </Table>
            </div>

            {books.last_page > 1 && (
                <div className="mt-4">
                    <PageNumbersPagination
                        currentPage={books.current_page}
                        lastPage={books.last_page}
                        perPage={books.per_page}
                        total={books.total}
                        basePath="/admin/books"
                        filters={{ search }}
                    />
                </div>
            )}
        </AppCommonLayout>
    );
}
