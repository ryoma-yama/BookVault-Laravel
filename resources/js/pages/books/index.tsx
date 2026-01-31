import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
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

interface Book {
    id: number;
    title: string;
    author: string | null;
    publisher: string | null;
    isbn: string | null;
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

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Books', href: '/books' },
];

export default function BooksIndex({ books, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [author, setAuthor] = useState(filters.author || '');
    const [publisher, setPublisher] = useState(filters.publisher || '');

    const handleSearch = () => {
        router.get('/books', {
            search: search || undefined,
            author: author || undefined,
            publisher: publisher || undefined,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleSort = (field: string) => {
        const direction = filters.sort === field && filters.direction === 'asc' ? 'desc' : 'asc';
        router.get('/books', {
            ...filters,
            sort: field,
            direction,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Books" />

            <div className="space-y-6">
                <div>
                    <h1 className="text-3xl font-bold">Book Collection</h1>
                    <p className="text-muted-foreground mt-2">
                        Search and browse through our book collection
                    </p>
                </div>

                <div className="grid gap-4 md:grid-cols-3">
                    <Input
                        type="text"
                        placeholder="Search by title..."
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                    />

                    <Input
                        type="text"
                        placeholder="Filter by author..."
                        value={author}
                        onChange={(e) => setAuthor(e.target.value)}
                        onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                    />

                    <Input
                        type="text"
                        placeholder="Filter by publisher..."
                        value={publisher}
                        onChange={(e) => setPublisher(e.target.value)}
                        onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                    />
                </div>

                <Button onClick={handleSearch}>Search</Button>

                <div className="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>
                                    <button
                                        onClick={() => handleSort('title')}
                                        className="font-medium hover:underline"
                                    >
                                        Title {filters.sort === 'title' && (filters.direction === 'asc' ? '↑' : '↓')}
                                    </button>
                                </TableHead>
                                <TableHead>Author</TableHead>
                                <TableHead>Publisher</TableHead>
                                <TableHead>Tags</TableHead>
                                <TableHead>
                                    <button
                                        onClick={() => handleSort('created_at')}
                                        className="font-medium hover:underline"
                                    >
                                        Added {filters.sort === 'created_at' && (filters.direction === 'asc' ? '↑' : '↓')}
                                    </button>
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {books.data.length === 0 ? (
                                <TableRow>
                                    <TableCell colSpan={5} className="text-center text-muted-foreground">
                                        No books found
                                    </TableCell>
                                </TableRow>
                            ) : (
                                books.data.map((book) => (
                                    <TableRow key={book.id}>
                                        <TableCell className="font-medium">{book.title}</TableCell>
                                        <TableCell>{book.author || '—'}</TableCell>
                                        <TableCell>{book.publisher || '—'}</TableCell>
                                        <TableCell>
                                            <div className="flex flex-wrap gap-1">
                                                {book.tags.map((tag) => (
                                                    <Badge key={tag.id} variant="outline">
                                                        {tag.name}
                                                    </Badge>
                                                ))}
                                            </div>
                                        </TableCell>
                                        <TableCell>{new Date(book.created_at).toLocaleDateString()}</TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                </div>

                {books.last_page > 1 && (
                    <div className="flex items-center justify-between">
                        <p className="text-sm text-muted-foreground">
                            Showing {books.data.length} of {books.total} books
                        </p>
                        <div className="flex gap-2">
                            {books.current_page > 1 && (
                                <Link
                                    href={`/books?page=${books.current_page - 1}`}
                                    preserveState
                                    preserveScroll
                                >
                                    <Button variant="outline">Previous</Button>
                                </Link>
                            )}
                            {books.current_page < books.last_page && (
                                <Link
                                    href={`/books?page=${books.current_page + 1}`}
                                    preserveState
                                    preserveScroll
                                >
                                    <Button variant="outline">Next</Button>
                                </Link>
                            )}
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
