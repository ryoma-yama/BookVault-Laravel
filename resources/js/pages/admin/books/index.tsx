import { Head, Link } from '@inertiajs/react';
import { router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import type { Book, BreadcrumbItem, PaginatedResponse } from '@/types';

interface Props {
    books: PaginatedResponse<Book>;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: '/admin/books',
    },
    {
        title: 'Books',
        href: '/admin/books',
    },
];

export default function AdminBooksIndex({ books }: Props) {
    const handleDelete = (id: number) => {
        if (confirm('Are you sure you want to delete this book?')) {
            router.delete(`/admin/books/${id}`);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Books Management" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Books Management</h1>
                    <Link href="/admin/books/create">
                        <Button>Add New Book</Button>
                    </Link>
                </div>
                <div className="overflow-x-auto">
                    <table className="w-full border-collapse">
                        <thead>
                            <tr className="border-b">
                                <th className="p-2 text-left">Title</th>
                                <th className="p-2 text-left">Authors</th>
                                <th className="p-2 text-left">Publisher</th>
                                <th className="p-2 text-left">ISBN-13</th>
                                <th className="p-2 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {books.data.map((book) => (
                                <tr key={book.id} className="border-b">
                                    <td className="p-2">{book.title}</td>
                                    <td className="p-2">
                                        {book.authors
                                            .map((a) => a.name)
                                            .join(', ')}
                                    </td>
                                    <td className="p-2">{book.publisher}</td>
                                    <td className="p-2">{book.isbn_13}</td>
                                    <td className="p-2">
                                        <div className="flex gap-2">
                                            <Link
                                                href={`/admin/books/${book.id}/edit`}
                                            >
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                >
                                                    Edit
                                                </Button>
                                            </Link>
                                            <Button
                                                variant="destructive"
                                                size="sm"
                                                onClick={() =>
                                                    handleDelete(book.id)
                                                }
                                            >
                                                Delete
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </AppLayout>
    );
}
