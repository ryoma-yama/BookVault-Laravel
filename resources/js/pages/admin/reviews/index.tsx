import { Head, router, usePage } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Pencil, Trash2 } from 'lucide-react';
import ReviewItem from '@/components/review-item';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem, SharedData } from '@/types';

interface Author {
    id: number;
    name: string;
}

interface Book {
    id: number;
    title: string;
    authors: Author[];
}

interface User {
    id: number;
    name: string;
}

interface Review {
    id: number;
    comment: string;
    is_recommended: boolean;
    created_at: string;
    book: Book;
    user: User;
}

interface Props {
    reviews: Review[];
}

export default function AdminReviewIndex({ reviews }: Props) {
    const { t } = useLaravelReactI18n();
    const { auth } = usePage<SharedData>().props;
    const isAdmin = auth.user?.role === 'admin';

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('Review Management'),
            href: '/admin/reviews',
        },
    ];

    const handleEdit = (reviewId: number) => {
        router.visit(`/reviews/${reviewId}/edit`);
    };

    const handleDelete = (reviewId: number, bookId: number) => {
        if (confirm(t('Are you sure you want to delete this review?'))) {
            router.delete(`/reviews/${reviewId}`, {
                preserveScroll: true,
                onSuccess: () => {
                    router.reload({ only: ['reviews'] });
                },
            });
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('Review Management')} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="mb-4">
                    <h1 className="text-3xl font-bold">
                        {t('Review Management')}
                    </h1>
                    <p className="text-muted-foreground">
                        {t('Manage all user reviews')}
                    </p>
                </div>

                {reviews.length === 0 ? (
                    <div className="rounded-lg border border-dashed p-8 text-center text-muted-foreground">
                        {t('No reviews found')}
                    </div>
                ) : (
                    <div className="space-y-6">
                        {reviews.map((review) => (
                            <div key={review.id} className="space-y-3">
                                <div className="flex items-center justify-between">
                                    <div className="text-sm text-muted-foreground">
                                        <a
                                            href={`/books/${review.book.id}`}
                                            className="font-semibold text-foreground hover:underline"
                                        >
                                            {review.book.title}
                                        </a>
                                        {' by '}
                                        {review.book.authors
                                            .map((a) => a.name)
                                            .join(', ')}
                                    </div>
                                    {isAdmin && (
                                        <div className="flex gap-2">
                                            <Button
                                                onClick={() =>
                                                    handleEdit(review.id)
                                                }
                                                variant="outline"
                                                size="sm"
                                            >
                                                <Pencil className="mr-2 h-4 w-4" />
                                                {t('Edit')}
                                            </Button>
                                        </div>
                                    )}
                                </div>
                                <ReviewItem review={review} />
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
