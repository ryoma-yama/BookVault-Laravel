import { Head, router } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Pencil } from 'lucide-react';
import ReviewItem from '@/components/review-item';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem, ReviewWithBook } from '@/types';
import Heading from '@/components/heading';

interface Props {
    reviews: ReviewWithBook[];
}

export default function ReviewIndex({ reviews }: Props) {
    const { t } = useLaravelReactI18n();

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('My Reviews'),
            href: '/reviews',
        },
    ];

    const handleEdit = (reviewId: number) => {
        router.visit(`/reviews/${reviewId}/edit`);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('My Reviews')} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <Heading title={t('My Reviews')} />

                {reviews.length === 0 ? (
                    <div className="rounded-lg border border-dashed p-8 text-center text-muted-foreground">
                        {t('You have not posted any reviews yet')}
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
