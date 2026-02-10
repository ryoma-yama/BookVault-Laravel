import { Head, Link } from '@inertiajs/react';
import { router } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import type { Book } from '@/types/domain';
import AppCommonLayout from '@/layouts/app-common-layout';

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

export default function AdminBooksIndex({ books }: Props) {
    const { t } = useLaravelReactI18n();
    const handleDelete = (id: number) => {
        if (confirm('Are you sure you want to delete this book?')) {
            router.delete(`/admin/books/${id}`);
        }
    };

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

    return (
        <AppCommonLayout title={t('Books')} breadcrumbs={breadcrumbs}>
            <div className="flex ">
                <Link href="/admin/books/create">
                    <Button>{t('Add New Book')}</Button>
                </Link>
            </div>
            <div className="overflow-x-auto">
                <table className="w-full border-collapse">
                    <thead>
                        <tr className="border-b">
                            <th className="p-2 text-left">{t('Title')}</th>
                            <th className="p-2 text-left">
                                {t('Authors')}
                            </th>
                            <th className="p-2 text-left">
                                {t('Publisher')}
                            </th>
                            <th className="p-2 text-left">
                                {t('ISBN-13')}
                            </th>
                            <th className="p-2 text-left">
                                {t('Actions')}
                            </th>
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
                                                {t('Edit')}
                                            </Button>
                                        </Link>
                                        <Button
                                            variant="destructive"
                                            size="sm"
                                            onClick={() =>
                                                handleDelete(book.id)
                                            }
                                        >
                                            {t('Delete')}
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AppCommonLayout >
    );
}
