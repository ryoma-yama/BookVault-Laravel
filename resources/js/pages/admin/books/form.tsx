import { Head, useForm } from '@inertiajs/react';
import { BrowserBarcodeReader } from '@zxing/library';
import type { AxiosError } from 'axios';
import axios from 'axios';
import type {
    FormEventHandler} from 'react';
import {
    useState,
    useRef,
    useEffect,
    useCallback,
} from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import type { ApiErrorResponse, Book, BookFormData, BreadcrumbItem, GoogleBooksApiResponse } from '@/types';

function isISBN13(code: string): boolean {
    return (
        code.length === 13 && (code.startsWith('978') || code.startsWith('979'))
    );
}

interface Props {
    book?: Book;
}

export default function AdminBookForm({ book }: Props) {
    const isEditing = !!book;
    const [isSearching, setIsSearching] = useState(false);
    const [searchError, setSearchError] = useState<string | null>(null);
    const [scanning, setScanning] = useState(false);
    const [scannerError, setScannerError] = useState<string | null>(null);
    const scannerRef = useRef<HTMLDivElement>(null);

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
            href: isEditing
                ? `/admin/books/${book.id}/edit`
                : '/admin/books/create',
        },
    ];

    const { data, setData, post, put, processing, errors } = useForm<BookFormData>({
        isbn_13: book?.isbn_13 || '',
        title: book?.title || '',
        publisher: book?.publisher || '',
        published_date: book?.published_date || '',
        description: book?.description || '',
        google_id: book?.google_id || '',
        image_url: book?.image_url || '',
        authors: book?.authors.map((a) => a.name) || [''],
    });

    const handleSearchByIsbn = useCallback(async () => {
        if (!data.isbn_13) {
            setSearchError('Please enter an ISBN first');
            return;
        }

        setIsSearching(true);
        setSearchError(null);

        try {
            const response = await axios.post<GoogleBooksApiResponse>(
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
            });
        } catch (error) {
            const axiosError = error as AxiosError<ApiErrorResponse>;
            const errorMessage =
                axiosError.response?.data?.error || 'Failed to fetch book information';
            setSearchError(errorMessage);
        } finally {
            setIsSearching(false);
        }
    }, [data, setData]);

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

    useEffect(() => {
        if (!scanning || !scannerRef.current) return;

        let scanner: BrowserBarcodeReader | null = null;
        let isStarted = false;

        const startScanner = async () => {
            scanner = new BrowserBarcodeReader();
            setScannerError(null);

            try {
                await scanner.decodeFromVideoDevice(
                    null, // デバイスID（nullで背面カメラを優先）
                    scannerRef.current!.id,
                    (result) => {
                        if (result) {
                            const code = result.getText();
                            if (isISBN13(code)) {
                                // カメラ停止とISBN処理
                                scanner?.reset();
                                setScanning(false);
                                setData('isbn_13', code);
                                handleSearchByIsbn();
                            }
                        }
                    },
                );
                isStarted = true;
            } catch (err) {
                console.error('Scanner error:', err);
                const errorMsg =
                    err instanceof Error
                        ? err.message.includes('Permission') ||
                          err.message.includes('NotAllowedError')
                            ? 'カメラへのアクセスが拒否されました。ブラウザの設定を確認してください。'
                            : 'カメラの起動に失敗しました。'
                        : 'カメラの起動に失敗しました。';
                setScannerError(errorMsg);
                setScanning(false);
            }
        };

        startScanner();

        return () => {
            if (scanner && isStarted) {
                scanner.reset();
            }
        };
    }, [scanning, handleSearchByIsbn, setData]);

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
                                {isSearching ? 'Searching...' : 'Search ISBN'}
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => setScanning(true)}
                            >
                                カメラで読み取る
                            </Button>
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
                        {scannerError && (
                            <p className="text-sm text-red-500">
                                {scannerError}
                            </p>
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
                            <p className="text-sm text-red-500">
                                {errors.title}
                            </p>
                        )}
                    </div>

                    <div>
                        <Label htmlFor="publisher">Publisher</Label>
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
                        <Label htmlFor="published_date">Published Date</Label>
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
                        <Label htmlFor="description">Description</Label>
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
                        <Label htmlFor="image_url">Cover Image URL</Label>
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
                            <Label>Authors</Label>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={handleAddAuthor}
                            >
                                Add Author
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
                                    placeholder="Author name"
                                />
                                {data.authors.length > 1 && (
                                    <Button
                                        type="button"
                                        variant="destructive"
                                        onClick={() =>
                                            handleRemoveAuthor(index)
                                        }
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

                {scanning && (
                    <div className="bg-opacity-80 fixed inset-0 z-50 flex flex-col items-center justify-center bg-black">
                        <div className="mb-2 text-white">
                            カメラをISBNバーコードに向けてください
                        </div>
                        <div
                            id="isbn-scanner"
                            ref={scannerRef}
                            className="h-[300px] w-[300px] bg-white"
                        />
                        <button
                            onClick={() => setScanning(false)}
                            className="mt-4 text-white underline"
                        >
                            閉じる
                        </button>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
