import { Head, Link, router } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { useState } from 'react';
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
import type { BookFilters, BookListItem, BreadcrumbItem, PaginatedResponse } from '@/types';

interface Props {
    books: PaginatedResponse<BookListItem>;
    filters: BookFilters;
}

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Books', href: '/books' }];

export default function BooksIndex({ books, filters }: Props) {
    const { t } = useLaravelReactI18n();
    const [search, setSearch] = useState(filters.search || '');
    const [author, setAuthor] = useState(filters.author || '');
    const [publisher, setPublisher] = useState(filters.publisher || '');

    const handleSearch = () => {
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
            },
        );
    };

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

                <div className="grid gap-4 md:grid-cols-3">
                    <Input
                        type="text"
                        placeholder={t('Search by title...')}
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                    />

                    <Input
                        type="text"
                        placeholder={t('Filter by author...')}
                        value={author}
                        onChange={(e) => setAuthor(e.target.value)}
                        onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                    />

                    <Input
                        type="text"
                        placeholder={t('Filter by publisher...')}
                        value={publisher}
                        onChange={(e) => setPublisher(e.target.value)}
                        onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                    />
                </div>

                <Button onClick={handleSearch}>{t('Search')}</Button>

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
                                        className="text-center text-muted-foreground"
                                    >
                                        {t('No books found')}
                                    </TableCell>
                                </TableRow>
                            ) : (
                                books.data.map((book) => (
                                    <TableRow key={book.id}>
                                        <TableCell className="font-medium">
                                            {book.title}
                                        </TableCell>
                                        <TableCell>
                                            {book.author || '—'}
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
                            {t('Showing :count of :total books', {
                                count: books.data.length.toString(),
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
