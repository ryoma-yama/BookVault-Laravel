import { Head, router, useForm } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Loader2 } from 'lucide-react';
import { useState } from 'react';
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

interface Review {
    id: number;
    comment: string;
    is_recommended: boolean;
    book: Book;
}

interface Props {
    review: Review;
}

export default function ReviewEdit({ review }: Props) {
    const { t } = useLaravelReactI18n();
    const [showDeleteDialog, setShowDeleteDialog] = useState(false);

    const { data, setData, put, processing, errors } = useForm({
        comment: review.comment,
        is_recommended: review.is_recommended,
    });

    const { delete: destroy, processing: deleting } = useForm();

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('Books'),
            href: '/',
        },
        {
            title: review.book.title,
            href: `/books/${review.book.id}`,
        },
        {
            title: t('Edit Review'),
            href: `/reviews/${review.id}/edit`,
        },
    ];

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/reviews/${review.id}`);
    };

    const handleDelete = () => {
        destroy(`/reviews/${review.id}`, {
            onSuccess: () => setShowDeleteDialog(false),
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('Edit Review')} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="mx-auto w-full max-w-2xl">
                    <h1 className="mb-6 text-2xl font-bold">
                        {t('Edit Review')}
                    </h1>

                    <div className="mb-6 rounded-lg border bg-muted/50 p-4">
                        <h2 className="font-semibold">{review.book.title}</h2>
                        <p className="text-sm text-muted-foreground">
                            {review.book.authors.map((a) => a.name).join(', ')}
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
                                    className="text-base leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
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
                                placeholder={t(
                                    'Share your thoughts about this book...',
                                )}
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
                                        <span>
                                            {t('Maximum 400 characters')}
                                        </span>
                                    )}
                                </span>
                                <span>{data.comment.length}/400</span>
                            </div>
                        </div>

                        <div className="mt-8 flex flex-col-reverse gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <Button
                                type="button"
                                variant="ghost"
                                onClick={() => setShowDeleteDialog(true)}
                                disabled={processing || deleting}
                                className="w-full text-red-600 hover:bg-red-50 hover:text-red-700 sm:w-auto"
                            >
                                {t('Delete Review')}
                            </Button>

                            <div className="flex w-full flex-col-reverse gap-3 sm:w-auto sm:flex-row">
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() =>
                                        router.visit(`/books/${review.book.id}`)
                                    }
                                    disabled={processing}
                                    className="w-full sm:w-auto"
                                >
                                    {t('Cancel')}
                                </Button>
                                <Button
                                    type="submit"
                                    disabled={processing}
                                    className="w-full sm:w-auto"
                                >
                                    {processing && (
                                        <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                    )}
                                    {t('Update Review')}
                                </Button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {/* Delete Confirmation Dialog */}
            <AlertDialog
                open={showDeleteDialog}
                onOpenChange={setShowDeleteDialog}
            >
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>
                            {t('Delete Review?')}
                        </AlertDialogTitle>
                        <AlertDialogDescription>
                            {t(
                                'Are you sure you want to delete this review? This action cannot be undone.',
                            )}
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel disabled={deleting}>
                            {t('Cancel')}
                        </AlertDialogCancel>
                        <AlertDialogAction
                            onClick={handleDelete}
                            disabled={deleting}
                            className="bg-red-600 hover:bg-red-700"
                        >
                            {deleting && (
                                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                            )}
                            {t('Delete')}
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </AppLayout>
    );
}
