import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

interface Author {
    id: number;
    name: string;
}

interface Book {
    id: number;
    title: string;
    publisher: string;
    published_date: string;
    description: string;
    image_url?: string;
    authors: Author[];
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
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Books',
        href: '/books',
    },
];

export default function BooksIndex({ books }: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Books" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <h1 className="text-2xl font-bold">Books</h1>
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    {books.data.map((book) => (
                        <Link
                            key={book.id}
                            href={`/books/${book.id}`}
                            className="block overflow-hidden rounded-xl border border-sidebar-border/70 p-4 transition hover:border-sidebar-border dark:border-sidebar-border"
                        >
                            <div className="flex gap-4">
                                {book.image_url && (
                                    <img
                                        src={book.image_url}
                                        alt={book.title}
                                        className="h-32 w-24 object-cover"
                                    />
                                )}
                                <div className="flex-1">
                                    <h2 className="font-semibold">{book.title}</h2>
                                    <p className="text-sm text-muted-foreground">
                                        {book.authors.map((a) => a.name).join(', ')}
                                    </p>
                                    <p className="text-sm text-muted-foreground">
                                        {book.publisher} ({book.published_date})
                                    </p>
                                </div>
                            </div>
                        </Link>
                    ))}
                </div>
            </div>
        </AppLayout>
    );
}
