import { Head, router, useForm, usePage } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { ImageOff, Loader2 } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem, SharedData } from '@/types';
import type { Review, UserReview } from '@/types/domain';
import ReviewItem from '@/components/review-item';

interface Author {
    id: number;
    name: string;
}

interface Tag {
    id: number;
    name: string;
}

interface InventoryStatus {
    total_copies: number;
    borrowed_count: number;
    available_count: number;
}

interface CurrentLoan {
    user: {
        id: number;
        name: string;
    };
    borrowed_date: string; // Format: Y/m/d
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
    tags: Tag[];
    inventory_status: InventoryStatus;
    current_loans: CurrentLoan[];
}

interface Props {
    book: Book;
    reviews: Review[];
    userReview: UserReview | null;
}

export default function BookShow({ book, reviews, userReview }: Props) {
    const { t, currentLocale } = useLaravelReactI18n();
    const { auth } = usePage<SharedData>().props;
    const isAuthenticated = !!auth.user;
    const canBorrow = book.inventory_status.available_count > 0;
    const [showDialog, setShowDialog] = useState(false);

    const { post, processing } = useForm({
        book_id: book.id,
    });

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

    // Format loan information based on locale
    const formatLoanInfo = (loan: CurrentLoan) => {
        if (currentLocale() === 'ja') {
            return `${loan.user.name} / ${loan.borrowed_date} 〜`;
        }
        return `${loan.user.name} / since ${loan.borrowed_date}`;
    };

    const handleBorrowClick = () => {
        if (!isAuthenticated) {
            router.visit('/login');
        } else {
            setShowDialog(true);
        }
    };

    const handleBorrowConfirm = () => {
        post('/loans', {
            preserveScroll: true,
            onSuccess: () => {
                setShowDialog(false);
                toast.success(t('Book borrowed successfully!'));
                router.reload({ only: ['book'] });
            },
            onError: (errors) => {
                setShowDialog(false);
                const errorMessage =
                    errors.book_id ||
                    errors.book_copy_id ||
                    t('This book is not available for borrowing.');
                toast.error(errorMessage);
            },
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={book.title} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex flex-col gap-6 md:flex-row">
                    <div className="flex-shrink-0">
                        {book.image_url ? (
                            <img
                                src={book.image_url}
                                alt={book.title}
                                className="h-64 w-48 rounded-lg object-cover shadow-md"
                            />
                        ) : (
                            <div className="flex h-64 w-48 flex-col items-center justify-center gap-3 rounded-lg border-2 border-dashed bg-muted text-muted-foreground">
                                <ImageOff className="h-12 w-12 opacity-40" />
                                <span className="text-xs font-medium opacity-60">
                                    No Image
                                </span>
                            </div>
                        )}
                    </div>
                    <div className="flex-1">
                        <h1 className="mb-2 text-3xl font-bold">
                            {book.title}
                        </h1>
                        <div className="space-y-2 text-muted-foreground">
                            <p>
                                <strong>{t('Authors')}:</strong>{' '}
                                {book.authors.map((a) => a.name).join(', ')}
                            </p>
                            <p>
                                <strong>{t('Publisher')}:</strong>{' '}
                                {book.publisher}
                            </p>
                            <p>
                                <strong>{t('Published')}:</strong>{' '}
                                {new Date(
                                    book.published_date,
                                ).toLocaleDateString()}
                            </p>
                            <p>
                                <strong>ISBN-13:</strong> {book.isbn_13}
                            </p>
                        </div>

                        {/* Inventory Status */}
                        <div className="mt-4 rounded-lg border bg-muted/50 p-4">
                            <h3 className="mb-2 font-semibold">
                                {t('Availability')}
                            </h3>
                            <div className="space-y-1 text-sm">
                                <p>
                                    <span className="font-medium">
                                        {t('Available')}:
                                    </span>{' '}
                                    <span
                                        className={
                                            canBorrow
                                                ? 'text-green-600 dark:text-green-400'
                                                : 'text-red-600 dark:text-red-400'
                                        }
                                    >
                                        {book.inventory_status.available_count}{' '}
                                        / {book.inventory_status.total_copies}
                                    </span>
                                </p>
                                {book.inventory_status.borrowed_count > 0 && (
                                    <p className="text-muted-foreground">
                                        {t(':count currently borrowed', {
                                            count: book.inventory_status.borrowed_count.toString(),
                                        })}
                                    </p>
                                )}
                                {book.current_loans.length > 0 && (
                                    <div className="mt-3 space-y-1 border-t pt-2">
                                        <p className="font-medium text-foreground">
                                            {t('Currently borrowed by')}:
                                        </p>
                                        {book.current_loans.map((loan, index) => (
                                            <p
                                                key={index}
                                                className="text-muted-foreground"
                                            >
                                                {formatLoanInfo(loan)}
                                            </p>
                                        ))}
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Borrow Button */}
                        <div className="mt-6">
                            <Button
                                onClick={handleBorrowClick}
                                disabled={!canBorrow || processing}
                                size="lg"
                                className="w-full md:w-auto"
                            >
                                {processing && (
                                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                )}
                                {!canBorrow
                                    ? t('Currently Unavailable')
                                    : isAuthenticated
                                        ? t('Borrow')
                                        : t('Login to Borrow')}
                            </Button>
                            {!canBorrow &&
                                book.inventory_status.total_copies === 0 && (
                                    <p className="mt-2 text-sm text-muted-foreground">
                                        {t(
                                            'No copies available in the library',
                                        )}
                                    </p>
                                )}
                        </div>

                        <div className="mt-6">
                            <h2 className="mb-2 text-xl font-semibold">
                                {t('Description')}
                            </h2>
                            {/* 
                              * Admin-only content: This HTML is trusted and sanitized on the server side (Laravel).
                              * Only administrators can edit this description, so it's safe to render directly.
                              */}
                            <div
                                className="prose dark:prose-invert max-w-none"
                                dangerouslySetInnerHTML={{ __html: book.description }}
                            />
                        </div>

                        {/* Tags Display */}
                        {book.tags && book.tags.length > 0 && (
                            <div className="mt-4">
                                <h3 className="mb-2 text-sm font-medium text-muted-foreground">
                                    {t('Tags')}
                                </h3>
                                <div className="flex flex-wrap gap-2">
                                    {book.tags.map((tag) => (
                                        <Badge key={tag.id} variant="secondary">
                                            {tag.name}
                                        </Badge>
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>
                </div>

                {/* Reviews Section */}
                <div className="mt-8 border-t pt-8">
                    <div className="mb-6 flex items-center justify-between">
                        <h2 className="text-2xl font-bold">
                            {t('Reviews')}
                        </h2>
                        {isAuthenticated && (
                            <div>
                                {userReview ? (
                                    <Button
                                        onClick={() =>
                                            router.visit(
                                                `/reviews/${userReview.id}/edit`,
                                            )
                                        }
                                        variant="outline"
                                    >
                                        {t('Edit Your Review')}
                                    </Button>
                                ) : (
                                    <Button
                                        onClick={() =>
                                            router.visit(
                                                `/books/${book.id}/reviews/create`,
                                            )
                                        }
                                    >
                                        {t('Add Review')}
                                    </Button>
                                )}
                            </div>
                        )}
                    </div>
                    <div className="space-y-4">
                        {reviews.length === 0 ? (
                            <div className="rounded-lg border border-dashed p-8 text-center text-muted-foreground">
                                {t('No reviews yet')}
                            </div>
                        ) : (
                            reviews.map((review) => (
                                <ReviewItem
                                    key={review.id}
                                    review={review}
                                />
                            ))
                        )}
                    </div>
                </div>
            </div>

            {/* Borrow Confirmation Dialog */}
            <AlertDialog open={showDialog} onOpenChange={setShowDialog}>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>
                            {t('Borrow this book?')}
                        </AlertDialogTitle>
                        <AlertDialogDescription>
                            {t('Are you sure you want to borrow this book?')}
                            <br />
                            <strong className="mt-2 block">{book.title}</strong>
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel disabled={processing}>
                            {t('Cancel')}
                        </AlertDialogCancel>
                        <AlertDialogAction
                            onClick={handleBorrowConfirm}
                            disabled={processing}
                        >
                            {processing && (
                                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                            )}
                            {t('Confirm')}
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </AppLayout>
    );
}
