import { useLaravelReactI18n } from 'laravel-react-i18n';
import { ThumbsUp } from 'lucide-react';
import type { Review } from '@/types/domain';

interface Props {
    reviews: Review[];
}

export default function ReviewList({ reviews }: Props) {
    const { t, currentLocale } = useLaravelReactI18n();

    const formatDate = (dateString: string) => {
        const date = new Date(dateString);
        if (currentLocale() === 'ja') {
            return date.toLocaleDateString('ja-JP', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
            });
        }
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        });
    };

    if (reviews.length === 0) {
        return (
            <div className="rounded-lg border border-dashed p-8 text-center text-muted-foreground">
                {t('No reviews yet')}
            </div>
        );
    }

    return (
        <div className="space-y-4">
            {reviews.map((review) => (
                <div
                    key={review.id}
                    className="rounded-lg border bg-card p-4 shadow-sm"
                >
                    <div className="mb-2 flex items-start justify-between">
                        <div className="flex flex-col gap-1">
                            <div className="flex items-center gap-2">
                                <span className="font-semibold text-sm">
                                    {review.user.name}
                                </span>
                                {review.is_recommended && (
                                    <span className="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                        <ThumbsUp className="h-3 w-3" />
                                        {t('Recommended!')}
                                    </span>
                                )}
                            </div>
                            <time className="text-xs text-muted-foreground">
                                {formatDate(review.created_at)}
                            </time>
                        </div>
                    </div>
                    <p className="whitespace-pre-wrap text-sm leading-relaxed">
                        {review.comment}
                    </p>
                </div>
            ))}
        </div>
    );
}
