import { Head, Link, router } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { useEffect, useState } from 'react';
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
        author?: string;
        publisher?: string;
        tag?: string;
        sort?: string;
        direction?: string;
    };
}

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Books', href: '/books' }];

export default function BooksIndex({ books, filters }: Props) {
    const { t } = useLaravelReactI18n();
    const [search, setSearch] = useState(filters.search || '');
    const [author, setAuthor] = useState(filters.author || '');
    const [publisher, setPublisher] = useState(filters.publisher || '');
    const [isSearching, setIsSearching] = useState(false);

    // Debounce search to avoid too many requests
    useEffect(() => {
        const timeout = setTimeout(() => {
            if (
                search !== filters.search ||
                author !== filters.author ||
                publisher !== filters.publisher
            ) {
                setIsSearching(true);
                router.get(
                    '/books',
                    {
                        search: search || undefined,
                        author: author || undefined,
                        publisher: publisher || undefined,
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
    }, [search, author, publisher]);

    const handleSort = (field: string) => {
        const direction =
            filters.sort === field && filters.direction === 'asc'
                ? 'desc'
                : 'asc';
        router.get(
            '/books',
            {
                ...filters,
                sort: field,
                direction,
            },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    const hasActiveFilters = search || author || publisher;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('Books')} />

            <div className="space-y-6">
                <div>
                    <h1 className="text-3xl font-bold">
                        {t('Book Collection')}
                    </h1>
                    <p className="mt-2 text-muted-foreground">
                        {t('Search and browse through our book collection')}
                    </p>
                </div>

                <div className="space-y-4">
                    <div className="grid gap-4 md:grid-cols-3">
                        <Input
                            type="text"
                            placeholder={t(
                                'Search by title, author, description...',
                            )}
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                        />

                        <Input
                            type="text"
                            placeholder={t('Filter by author...')}
                            value={author}
                            onChange={(e) => setAuthor(e.target.value)}
                        />

                        <Input
                            type="text"
                            placeholder={t('Filter by publisher...')}
                            value={publisher}
                            onChange={(e) => setPublisher(e.target.value)}
                        />
                    </div>

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
                                    setAuthor('');
                                    setPublisher('');
                                }}
                            >
                                {t('Clear filters')}
                            </Button>
                        </div>
                    )}
                </div>

                <div className="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>
                                    <button
                                        onClick={() => handleSort('title')}
                                        className="font-medium hover:underline"
                                    >
                                        {t('Title')}{' '}
                                        {filters.sort === 'title' &&
                                            (filters.direction === 'asc'
                                                ? '↑'
                                                : '↓')}
                                    </button>
                                </TableHead>
                                <TableHead>{t('Author')}</TableHead>
                                <TableHead>{t('Publisher')}</TableHead>
                                <TableHead>{t('Tags')}</TableHead>
                                <TableHead>
                                    <button
                                        onClick={() => handleSort('created_at')}
                                        className="font-medium hover:underline"
                                    >
                                        {t('Added')}{' '}
                                        {filters.sort === 'created_at' &&
                                            (filters.direction === 'asc'
                                                ? '↑'
                                                : '↓')}
                                    </button>
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {books.data.length === 0 ? (
                                <TableRow>
                                    <TableCell
                                        colSpan={5}
                                        className="text-center"
                                    >
                                        <div className="py-12">
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
                                                    ? t(
                                                          'No books found matching your search',
                                                      )
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
                                    </TableCell>
                                </TableRow>
                            ) : (
                                books.data.map((book) => (
                                    <TableRow key={book.id}>
                                        <TableCell className="font-medium">
                                            {book.title}
                                        </TableCell>
                                        <TableCell>
                                            {book.authors.length > 0
                                                ? book.authors
                                                      .map((a) => a.name)
                                                      .join(', ')
                                                : '—'}
                                        </TableCell>
                                        <TableCell>
                                            {book.publisher || '—'}
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex flex-wrap gap-1">
                                                {book.tags.map((tag) => (
                                                    <Badge
                                                        key={tag.id}
                                                        variant="outline"
                                                    >
                                                        {tag.name}
                                                    </Badge>
                                                ))}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            {new Date(
                                                book.created_at,
                                            ).toLocaleDateString()}
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                </div>

                {books.last_page > 1 && (
                    <div className="flex items-center justify-between">
                        <p className="text-sm text-muted-foreground">
                            {t('Showing :from-:to of :total books', {
                                from: (
                                    (books.current_page - 1) *
                                        books.per_page +
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
                                    href={`/books?page=${books.current_page - 1}`}
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
                                    href={`/books?page=${books.current_page + 1}`}
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
