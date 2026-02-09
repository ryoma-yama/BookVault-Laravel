import { Head, router, useForm } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Loader2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import type { Author, BreadcrumbItem } from '@/types';

interface Book {
    id: number;
    title: string;
    authors: Author[];
}

interface Props {
    book: Book;
}

export default function ReviewCreate({ book }: Props) {
    const { t } = useLaravelReactI18n();

    const { data, setData, post, processing, errors } = useForm({
        book_id: book.id,
        comment: '',
        is_recommended: false,
    });

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('Books'),
            href: '/',
        },
        {
            title: book.title,
            href: `/books/${book.id}`,
        },
        {
            title: t('Add Review'),
            href: `/books/${book.id}/reviews/create`,
        },
    ];

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/reviews');
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('Add Review')} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="mx-auto w-full max-w-2xl">
                    <h1 className="mb-6 text-2xl font-bold">
                        {t('Add Review')}
                    </h1>

                    <div className="mb-6 rounded-lg border bg-muted/50 p-4">
                        <h2 className="font-semibold">{book.title}</h2>
                        <p className="text-sm text-muted-foreground">
                            {book.authors.map((a) => a.name).join(', ')}
                        </p>
                    </div>

                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div className="space-y-2">
                            <div className="flex items-center space-x-2">
                                <Checkbox
                                    id="is_recommended"
                                    checked={data.is_recommended}
                                    onCheckedChange={(checked) =>
                                        setData(
                                            'is_recommended',
                                            checked === true,
                                        )
                                    }
                                />
                                <Label
                                    htmlFor="is_recommended"
                                    className="text-base font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                >
                                    {t('I recommend this book')}
                                </Label>
                            </div>
                            {errors.is_recommended && (
                                <p className="text-sm text-red-600 dark:text-red-400">
                                    {errors.is_recommended}
                                </p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="comment">
                                {t('Your Review')} *
                            </Label>
                            <Textarea
                                id="comment"
                                value={data.comment}
                                onChange={(e) =>
                                    setData('comment', e.target.value)
                                }
                                placeholder={t('Share your thoughts about this book...')}
                                rows={6}
                                maxLength={400}
                                className="resize-none"
                            />
                            <div className="flex justify-between text-sm text-muted-foreground">
                                <span>
                                    {errors.comment ? (
                                        <span className="text-red-600 dark:text-red-400">
                                            {errors.comment}
                                        </span>
                                    ) : (
                                        <span>{t('Maximum 400 characters')}</span>
                                    )}
                                </span>
                                <span>
                                    {data.comment.length}/400
                                </span>
                            </div>
                        </div>

                        <div className="flex gap-3">
                            <Button type="submit" disabled={processing}>
                                {processing && (
                                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                )}
                                {t('Submit Review')}
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => router.visit(`/books/${book.id}`)}
                                disabled={processing}
                            >
                                {t('Cancel')}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}
