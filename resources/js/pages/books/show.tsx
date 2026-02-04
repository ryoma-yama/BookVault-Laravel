import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

interface Author {
    id: number;
    name: string;
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
}

interface Props {
    book: Book;
}

export default function BookShow({ book }: Props) {
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

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={book.title} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex gap-6">
                    {book.image_url && (
                        <div className="flex-shrink-0">
                            <img
                                src={book.image_url}
                                alt={book.title}
                                className="h-64 w-48 rounded-lg object-cover"
                            />
                        </div>
                    )}
                    <div className="flex-1">
                        <h1 className="mb-2 text-3xl font-bold">
                            {book.title}
                        </h1>
                        <div className="space-y-2 text-muted-foreground">
                            <p>
                                <strong>Authors:</strong>{' '}
                                {book.authors.map((a) => a.name).join(', ')}
                            </p>
                            <p>
                                <strong>Publisher:</strong> {book.publisher}
                            </p>
                            <p>
                                <strong>Published:</strong>{' '}
                                {book.published_date}
                            </p>
                            <p>
                                <strong>ISBN-13:</strong> {book.isbn_13}
                            </p>
                        </div>
                        <div className="mt-6">
                            <h2 className="mb-2 text-xl font-semibold">
                                Description
                            </h2>
                            <div className="prose dark:prose-invert max-w-none">
                                <p>{book.description}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
