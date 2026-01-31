import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
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
    google_id?: string;
    image_url?: string;
    authors: Author[];
}

interface Props {
    book?: Book;
}

export default function AdminBookForm({ book }: Props) {
    const isEditing = !!book;
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Admin',
            href: '/admin/books',
        },
        {
            title: 'Books',
            href: '/admin/books',
        },
        {
            title: isEditing ? 'Edit Book' : 'New Book',
            href: isEditing ? `/admin/books/${book.id}/edit` : '/admin/books/create',
        },
    ];

    const { data, setData, post, put, processing, errors } = useForm({
        isbn_13: book?.isbn_13 || '',
        title: book?.title || '',
        publisher: book?.publisher || '',
        published_date: book?.published_date || '',
        description: book?.description || '',
        google_id: book?.google_id || '',
        image_url: book?.image_url || '',
        authors: book?.authors.map((a) => a.name) || [''],
    });

    const handleAddAuthor = () => {
        setData('authors', [...data.authors, '']);
    };

    const handleRemoveAuthor = (index: number) => {
        setData(
            'authors',
            data.authors.filter((_, i) => i !== index)
        );
    };

    const handleAuthorChange = (index: number, value: string) => {
        const newAuthors = [...data.authors];
        newAuthors[index] = value;
        setData('authors', newAuthors);
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        if (isEditing) {
            put(`/admin/books/${book.id}`);
        } else {
            post('/admin/books');
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={isEditing ? 'Edit Book' : 'New Book'} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <h1 className="text-2xl font-bold">
                    {isEditing ? 'Edit Book' : 'Add New Book'}
                </h1>
                <form onSubmit={submit} className="max-w-2xl space-y-4">
                    <div>
                        <Label htmlFor="isbn_13">ISBN-13</Label>
                        <Input
                            id="isbn_13"
                            type="text"
                            value={data.isbn_13}
                            onChange={(e) => setData('isbn_13', e.target.value)}
                            maxLength={13}
                            required
                        />
                        {errors.isbn_13 && (
                            <p className="text-sm text-red-500">{errors.isbn_13}</p>
                        )}
                    </div>

                    <div>
                        <Label htmlFor="title">Title</Label>
                        <Input
                            id="title"
                            type="text"
                            value={data.title}
                            onChange={(e) => setData('title', e.target.value)}
                            required
                        />
                        {errors.title && (
                            <p className="text-sm text-red-500">{errors.title}</p>
                        )}
                    </div>

                    <div>
                        <Label htmlFor="publisher">Publisher</Label>
                        <Input
                            id="publisher"
                            type="text"
                            value={data.publisher}
                            onChange={(e) => setData('publisher', e.target.value)}
                            required
                        />
                        {errors.publisher && (
                            <p className="text-sm text-red-500">{errors.publisher}</p>
                        )}
                    </div>

                    <div>
                        <Label htmlFor="published_date">Published Date</Label>
                        <Input
                            id="published_date"
                            type="text"
                            value={data.published_date}
                            onChange={(e) => setData('published_date', e.target.value)}
                            placeholder="YYYY-MM-DD"
                            required
                        />
                        {errors.published_date && (
                            <p className="text-sm text-red-500">{errors.published_date}</p>
                        )}
                    </div>

                    <div>
                        <Label htmlFor="description">Description</Label>
                        <Textarea
                            id="description"
                            value={data.description}
                            onChange={(e) => setData('description', e.target.value)}
                            rows={5}
                            required
                        />
                        {errors.description && (
                            <p className="text-sm text-red-500">{errors.description}</p>
                        )}
                    </div>

                    <div>
                        <Label htmlFor="image_url">Cover Image URL</Label>
                        <Input
                            id="image_url"
                            type="text"
                            value={data.image_url}
                            onChange={(e) => setData('image_url', e.target.value)}
                        />
                        {errors.image_url && (
                            <p className="text-sm text-red-500">{errors.image_url}</p>
                        )}
                    </div>

                    <div>
                        <div className="flex items-center justify-between mb-2">
                            <Label>Authors</Label>
                            <Button type="button" variant="outline" onClick={handleAddAuthor}>
                                Add Author
                            </Button>
                        </div>
                        {data.authors.map((author, index) => (
                            <div key={index} className="flex gap-2 mb-2">
                                <Input
                                    type="text"
                                    value={author}
                                    onChange={(e) => handleAuthorChange(index, e.target.value)}
                                    placeholder="Author name"
                                />
                                {data.authors.length > 1 && (
                                    <Button
                                        type="button"
                                        variant="destructive"
                                        onClick={() => handleRemoveAuthor(index)}
                                    >
                                        Remove
                                    </Button>
                                )}
                            </div>
                        ))}
                    </div>

                    <div className="flex gap-2">
                        <Button type="submit" disabled={processing}>
                            {isEditing ? 'Update Book' : 'Create Book'}
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => window.history.back()}
                        >
                            Cancel
                        </Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
