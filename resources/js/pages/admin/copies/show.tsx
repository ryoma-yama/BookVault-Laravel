import { Form, Head } from '@inertiajs/react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

type Book = {
    id: number;
    title: string;
    isbn_13: string;
    publisher: string;
    published_date: string;
};

type BookCopy = {
    id: number;
    book_id: number;
    acquired_date: string;
    discarded_date: string | null;
    created_at: string;
    updated_at: string;
};

type Props = {
    book: Book;
    copies: BookCopy[];
};

export default function Show({ book, copies }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: '蔵書管理',
            href: '#',
        },
        {
            title: book.title,
            href: `/admin/copies/${book.id}`,
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`${book.title} の蔵書管理`} />

            <div className="space-y-6 p-4">
                <div>
                    <h1 className="text-2xl font-bold">
                        {book.title} の蔵書管理
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        ISBN: {book.isbn_13} | 出版社: {book.publisher}
                    </p>
                </div>

                {/* Add new copy form */}
                <div className="rounded-lg border bg-card p-4">
                    <h2 className="mb-4 text-lg font-semibold">
                        新しい蔵書を追加
                    </h2>
                    <Form
                        method="post"
                        action={`/admin/copies/${book.id}`}
                        className="space-y-4"
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="acquired_date">
                                        取得日
                                    </Label>
                                    <Input
                                        id="acquired_date"
                                        name="acquired_date"
                                        type="date"
                                        required
                                        className="w-full max-w-xs"
                                    />
                                    <InputError
                                        message={errors.acquired_date}
                                    />
                                </div>

                                <Button type="submit" disabled={processing}>
                                    蔵書を追加
                                </Button>
                            </>
                        )}
                    </Form>
                </div>

                {/* List of copies */}
                <div className="space-y-4">
                    <h2 className="text-lg font-semibold">
                        現在の蔵書 ({copies.length}冊)
                    </h2>

                    {copies.length === 0 ? (
                        <p className="text-sm text-muted-foreground">
                            まだ蔵書が登録されていません。
                        </p>
                    ) : (
                        <div className="space-y-3">
                            {copies.map((copy) => (
                                <CopyEditForm
                                    key={copy.id}
                                    copy={copy}
                                    bookId={book.id}
                                />
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}

type CopyEditFormProps = {
    copy: BookCopy;
    bookId: number;
};

function CopyEditForm({ copy, bookId }: CopyEditFormProps) {
    const [isDeleting, setIsDeleting] = useState(false);

    const handleDelete = (e: React.FormEvent) => {
        if (!confirm('この蔵書を削除してもよろしいですか？')) {
            e.preventDefault();
        } else {
            setIsDeleting(true);
        }
    };

    return (
        <div className="rounded-lg border bg-card p-4">
            <Form
                method="put"
                action={`/admin/copies/${bookId}/${copy.id}`}
                className="space-y-4"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-4 md:grid-cols-2">
                            <div className="grid gap-2">
                                <Label htmlFor={`acquired_date_${copy.id}`}>
                                    取得日
                                </Label>
                                <Input
                                    id={`acquired_date_${copy.id}`}
                                    name="acquired_date"
                                    type="date"
                                    defaultValue={copy.acquired_date}
                                    required
                                />
                                <InputError message={errors.acquired_date} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor={`discarded_date_${copy.id}`}>
                                    廃棄日
                                </Label>
                                <Input
                                    id={`discarded_date_${copy.id}`}
                                    name="discarded_date"
                                    type="date"
                                    defaultValue={copy.discarded_date || ''}
                                />
                                <InputError message={errors.discarded_date} />
                            </div>
                        </div>

                        <div className="flex gap-2">
                            <Button
                                type="submit"
                                variant="secondary"
                                disabled={processing}
                            >
                                更新
                            </Button>
                        </div>
                    </>
                )}
            </Form>

            <Form
                method="delete"
                action={`/admin/copies/${bookId}/${copy.id}`}
                onSubmit={handleDelete}
                className="mt-2"
            >
                <Button
                    type="submit"
                    variant="destructive"
                    size="sm"
                    disabled={isDeleting}
                >
                    削除
                </Button>
            </Form>
        </div>
    );
}
