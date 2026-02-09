import { useLaravelReactI18n } from 'laravel-react-i18n';
import { ChevronDown, ChevronUp, ThumbsUp } from 'lucide-react';
import { useLayoutEffect, useRef, useState } from 'react';
import type { Review } from '@/types/domain';
import { Button } from './ui/button';

interface Props {
    review: Review;
}

export default function ReviewItem({ review }: Props) {
    const { t } = useLaravelReactI18n();
    const [isExpanded, setIsExpanded] = useState(false);
    const [shouldShowButton, setShouldShowButton] = useState(false);
    const contentRef = useRef<HTMLParagraphElement>(null);

    useLayoutEffect(() => {
        const el = contentRef.current;
        if (el) {
            // 実際の高さ(scrollHeight)が、表示されている高さ(clientHeight)を超えているか判定
            requestAnimationFrame(() => {
                if (el.scrollHeight > el.clientHeight) {
                    setShouldShowButton(true);
                }
            });
        }
    }, [review.comment]);
    return (
        <div
            key={review.id}
            className="rounded-lg border bg-card p-4 shadow-sm"
        >
            <div className="mb-2 flex items-start justify-between">
                <div className="flex flex-col gap-1">
                    <div className="flex items-center gap-2">
                        <span className="text-sm font-semibold">
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
                        {new Date(review.created_at).toLocaleDateString()}
                    </time>
                </div>
            </div>
            <div>
                <p
                    ref={contentRef}
                    className={`text-sm leading-relaxed whitespace-pre-wrap ${!isExpanded && 'line-clamp-3'}`}
                >
                    {review.comment}
                </p>

                {shouldShowButton && (
                    <Button
                        variant="ghost"
                        size="sm"
                        className="mt-1 h-8 px-2 text-xs text-muted-foreground hover:text-foreground"
                        onClick={() => setIsExpanded(!isExpanded)}
                    >
                        {isExpanded ? (
                            <>
                                {t('Read Less')}{' '}
                                <ChevronUp className="ml-1 h-3 w-3" />
                            </>
                        ) : (
                            <>
                                {t('Read More')}{' '}
                                <ChevronDown className="ml-1 h-3 w-3" />
                            </>
                        )}
                    </Button>
                )}
            </div>
        </div>
    );
}
