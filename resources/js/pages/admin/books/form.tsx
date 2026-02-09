import { Head, useForm } from '@inertiajs/react';
import axios, { AxiosError } from 'axios';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import type { FormEventHandler } from 'react';
import { useState, useCallback } from 'react';
import IsbnScanner from '@/components/isbn-scanner';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import type { Author, Tag } from '@/types/domain';

function extractErrorMessage(
    error: unknown,
    t: (key: string) => string,
): string {
    if (error instanceof AxiosError && error.response?.data?.error) {
        return error.response.data.error;
    }
    return t('Failed to fetch book information');
}

interface BookCopy {
    id: number | null;
    acquired_date?: string;
    discarded_date?: string | null;
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
    tags?: Tag[];
    copies?: BookCopy[];
}

interface Props {
    book?: Book;
}

export default function AdminBookForm({ book }: Props) {
    const { t } = useLaravelReactI18n();
    const isEditing = !!book;
    const [isSearching, setIsSearching] = useState(false);
    const [searchError, setSearchError] = useState<string | null>(null);

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('Admin'),
            href: '/admin/books',
        },
        {
            title: t('Books'),
            href: '/admin/books',
        },
        {
            title: isEditing ? t('Edit Book') : t('Add New Book'),
            href: isEditing
                ? `/admin/books/${book.id}/edit`
                : '/admin/books/create',
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
        authors: book?.authors?.map((a) => a.name) || [''],
        tags: book?.tags?.map((t) => t.name) || [],
        book_copies: book?.copies?.map((c) => ({ id: c.id })) || [],
    });

    const handleSearchByIsbn = useCallback(async () => {
        if (!data.isbn_13) {
            setSearchError(t('Please enter an ISBN first'));
            return;
        }

        setIsSearching(true);
        setSearchError(null);

        try {
            // Call Google Books API - it will check for duplicates first
            const response = await axios.post(
                '/admin/api/google-books/search',
                {
                    isbn: data.isbn_13,
                },
            );

            const bookInfo = response.data;

            setData({
                isbn_13: bookInfo.isbn_13 || data.isbn_13,
                title: bookInfo.title || data.title,
                publisher: bookInfo.publisher || data.publisher,
                published_date: bookInfo.published_date || data.published_date,
                description: bookInfo.description || data.description,
                google_id: bookInfo.google_id || data.google_id,
                image_url: bookInfo.image_url || data.image_url,
                authors:
                    bookInfo.authors && bookInfo.authors.length > 0
                        ? bookInfo.authors
                        : data.authors,
                tags: data.tags,
                book_copies: data.book_copies,
            });
        } catch (error) {
            setSearchError(extractErrorMessage(error, t));
        } finally {
            setIsSearching(false);
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [data.isbn_13, setData, t]);

    const handleAddAuthor = () => {
        setData('authors', [...data.authors, '']);
    };

    const handleRemoveAuthor = (index: number) => {
        setData(
            'authors',
            data.authors.filter((_, i) => i !== index),
        );
    };

    const handleAuthorChange = (index: number, value: string) => {
        const newAuthors = [...data.authors];
        newAuthors[index] = value;
        setData('authors', newAuthors);
    };

    // Tag handlers
    const handleAddTag = () => {
        setData('tags', [...data.tags, '']);
    };

    const handleRemoveTag = (index: number) => {
        setData(
            'tags',
            data.tags.filter((_, i) => i !== index),
        );
    };

    const handleTagChange = (index: number, value: string) => {
        const newTags = [...data.tags];
        newTags[index] = value;
        setData('tags', newTags);
    };

    // BookCopy handlers
    const handleAddBookCopy = () => {
        setData('book_copies', [...data.book_copies, { id: null }]);
    };

    const handleRemoveBookCopy = (index: number) => {
        setData(
            'book_copies',
            data.book_copies.filter((_, i) => i !== index),
        );
    };

    const handleIsbnScan = (isbn: string) => {
        setData('isbn_13', isbn);
        // Automatically search for book info after scanning
        handleSearchByIsbn();
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
            <Head title={isEditing ? t('Edit Book') : t('Add New Book')} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <h1 className="text-2xl font-bold">
                    {isEditing ? t('Edit Book') : t('Add New Book')}
                </h1>
                <form onSubmit={submit} className="max-w-2xl space-y-4">
                    <div>
                        <Label htmlFor="isbn_13">{t('ISBN-13')}</Label>
                        <div className="flex gap-2">
                            <Input
                                id="isbn_13"
                                type="text"
                                value={data.isbn_13}
                                onChange={(e) =>
                                    setData('isbn_13', e.target.value)
                                }
                                maxLength={13}
                                required
                            />
                            <Button
                                type="button"
                                variant="outline"
                                onClick={handleSearchByIsbn}
                                disabled={isSearching || !data.isbn_13}
                            >
                                {isSearching
                                    ? t('Searching...')
                                    : t('Search ISBN')}
                            </Button>
                            <IsbnScanner
                                onScan={handleIsbnScan}
                                buttonVariant="outline"
                            />
                        </div>
                        {errors.isbn_13 && (
                            <p className="text-sm text-red-500">
                                {errors.isbn_13}
                            </p>
                        )}
                        {searchError && (
                            <p className="text-sm text-red-500">
                                {searchError}
                            </p>
                        )}
                    </div>

                    <div>
                        <Label htmlFor="title">{t('Title')}</Label>
                        <Input
                            id="title"
                            type="text"
                            value={data.title}
                            onChange={(e) => setData('title', e.target.value)}
                            required
                        />
                        {errors.title && (
                            <p className="text-sm text-red-500">
                                {errors.title}
                            </p>
                        )}
                    </div>

                    <div>
                        <Label htmlFor="publisher">{t('Publisher')}</Label>
                        <Input
                            id="publisher"
                            type="text"
                            value={data.publisher}
                            onChange={(e) =>
                                setData('publisher', e.target.value)
                            }
                            required
                        />
                        {errors.publisher && (
                            <p className="text-sm text-red-500">
                                {errors.publisher}
                            </p>
                        )}
                    </div>

                    <div>
                        <Label htmlFor="published_date">{t('Published')}</Label>
                        <Input
                            id="published_date"
                            type="text"
                            value={data.published_date}
                            onChange={(e) =>
                                setData('published_date', e.target.value)
                            }
                            placeholder="YYYY-MM-DD"
                            required
                        />
                        {errors.published_date && (
                            <p className="text-sm text-red-500">
                                {errors.published_date}
                            </p>
                        )}
                    </div>

                    <div>
                        <Label htmlFor="description">{t('Description')}</Label>
                        <Textarea
                            id="description"
                            value={data.description}
                            onChange={(e) =>
                                setData('description', e.target.value)
                            }
                            rows={5}
                            required
                        />
                        {errors.description && (
                            <p className="text-sm text-red-500">
                                {errors.description}
                            </p>
                        )}
                    </div>

                    <div>
                        <Label htmlFor="image_url">
                            {t('Cover Image URL')}
                        </Label>
                        <Input
                            id="image_url"
                            type="text"
                            value={data.image_url}
                            onChange={(e) =>
                                setData('image_url', e.target.value)
                            }
                        />
                        {errors.image_url && (
                            <p className="text-sm text-red-500">
                                {errors.image_url}
                            </p>
                        )}
                    </div>

                    <div>
                        <div className="mb-2 flex items-center justify-between">
                            <Label>{t('Authors')}</Label>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={handleAddAuthor}
                            >
                                {t('Add Author')}
                            </Button>
                        </div>
                        {data.authors.map((author, index) => (
                            <div key={index} className="mb-2 flex gap-2">
                                <Input
                                    type="text"
                                    value={author}
                                    onChange={(e) =>
                                        handleAuthorChange(
                                            index,
                                            e.target.value,
                                        )
                                    }
                                    placeholder={t('Author name')}
                                />
                                {data.authors.length > 1 && (
                                    <Button
                                        type="button"
                                        variant="destructive"
                                        onClick={() =>
                                            handleRemoveAuthor(index)
                                        }
                                    >
                                        {t('Remove')}
                                    </Button>
                                )}
                            </div>
                        ))}
                    </div>

                    {/* Tags Section */}
                    <div>
                        <div className="mb-2 flex items-center justify-between">
                            <Label>{t('Tags')}</Label>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={handleAddTag}
                            >
                                {t('Add Tag')}
                            </Button>
                        </div>
                        {data.tags.length === 0 && (
                            <p className="text-sm text-muted-foreground">
                                {t(
                                    'No tags added yet. Click "Add Tag" to add one.',
                                )}
                            </p>
                        )}
                        {data.tags.map((tag, index) => (
                            <div key={index} className="mb-2 flex gap-2">
                                <Input
                                    type="text"
                                    value={tag}
                                    onChange={(e) =>
                                        handleTagChange(index, e.target.value)
                                    }
                                    placeholder={t('Tag name')}
                                    maxLength={50}
                                />
                                <Button
                                    type="button"
                                    variant="destructive"
                                    onClick={() => handleRemoveTag(index)}
                                >
                                    {t('Remove')}
                                </Button>
                            </div>
                        ))}
                    </div>

                    {/* BookCopies Section - Only show in edit mode */}
                    {isEditing && (
                        <div>
                            <div className="mb-2 flex items-center justify-between">
                                <Label>{t('Book Copies (Inventory)')}</Label>
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={handleAddBookCopy}
                                >
                                    {t('Add Copy')}
                                </Button>
                            </div>
                            <p className="mb-2 text-sm text-muted-foreground">
                                {t(
                                    'Manage physical copies of this book. New copies will be acquired today. Removing a copy will mark it as discarded.',
                                )}
                            </p>
                            {data.book_copies.length === 0 && (
                                <p className="text-sm text-muted-foreground">
                                    {t(
                                        'No active copies. Click "Add Copy" to add one.',
                                    )}
                                </p>
                            )}
                            {data.book_copies.map((copy, index) => (
                                <div key={index} className="mb-2 flex gap-2">
                                    <Input
                                        type="text"
                                        value={
                                            copy.id
                                                ? t('Copy #:id', {
                                                      id: copy.id,
                                                  })
                                                : t(
                                                      'New Copy (will be acquired today)',
                                                  )
                                        }
                                        disabled
                                        className="flex-1"
                                    />
                                    <Button
                                        type="button"
                                        variant="destructive"
                                        onClick={() =>
                                            handleRemoveBookCopy(index)
                                        }
                                    >
                                        {copy.id ? t('Discard') : t('Remove')}
                                    </Button>
                                </div>
                            ))}
                        </div>
                    )}

                    <div className="flex gap-2">
                        <Button type="submit" disabled={processing}>
                            {isEditing ? t('Update Book') : t('Create Book')}
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => window.history.back()}
                        >
                            {t('Cancel')}
                        </Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
