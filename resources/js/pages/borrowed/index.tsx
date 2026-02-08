import { Head, router } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { ArrowUpDown } from 'lucide-react';
import { useState } from 'react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

interface Book {
    id: number;
    title: string;
    isbn_13: string;
}

interface BookCopy {
    id: number;
    book: Book;
}

interface Loan {
    id: number;
    book_copy_id: number;
    user_id: number;
    borrowed_date: string;
    returned_date: string | null;
    bookCopy: BookCopy;
}

interface Props {
    loans: Loan[];
}

type SortField = 'title' | 'borrowed_date' | 'status';
type SortOrder = 'asc' | 'desc';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Borrowed Books', href: '/borrowed' },
];

interface SortButtonProps {
    field: SortField;
    children: React.ReactNode;
    onClick: (field: SortField) => void;
}

function SortButton({ field, children, onClick }: SortButtonProps) {
    return (
        <Button
            variant="ghost"
            onClick={() => onClick(field)}
            className="h-auto p-0 font-semibold hover:bg-transparent"
        >
            {children}
            <ArrowUpDown className="ml-2 h-4 w-4" />
        </Button>
    );
}

export default function BorrowedIndex({ loans }: Props) {
    const { t } = useLaravelReactI18n();
    const [sortField, setSortField] = useState<SortField>('borrowed_date');
    const [sortOrder, setSortOrder] = useState<SortOrder>('desc');

    const handleSort = (field: SortField) => {
        if (sortField === field) {
            setSortOrder(sortOrder === 'asc' ? 'desc' : 'asc');
        } else {
            setSortField(field);
            setSortOrder('asc');
        }
    };

    const sortedLoans = [...loans].sort((a, b) => {
        let comparison = 0;

        if (sortField === 'title') {
            comparison = a.bookCopy.book.title.localeCompare(
                b.bookCopy.book.title,
            );
        } else if (sortField === 'borrowed_date') {
            comparison =
                new Date(a.borrowed_date).getTime() -
                new Date(b.borrowed_date).getTime();
        } else if (sortField === 'status') {
            const aStatus = a.returned_date ? 'returned' : 'borrowed';
            const bStatus = b.returned_date ? 'returned' : 'borrowed';
            comparison = aStatus.localeCompare(bStatus);
        }

        return sortOrder === 'asc' ? comparison : -comparison;
    });

    const handleReturn = (loanId: number) => {
        if (confirm(t('Are you sure you want to return this book?'))) {
            router.put(
                `/loans/${loanId}`,
                {},
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        // Refresh the page
                        router.reload();
                    },
                },
            );
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('Borrowed Books')} />

            <div className="space-y-6 px-4 py-6">
                <Heading title={t('Borrowed Books')} />

                <div className="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>
                                    <SortButton
                                        field="title"
                                        onClick={handleSort}
                                    >
                                        {t('Title')}
                                    </SortButton>
                                </TableHead>
                                <TableHead>
                                    <SortButton
                                        field="borrowed_date"
                                        onClick={handleSort}
                                    >
                                        {t('Borrowed Date')}
                                    </SortButton>
                                </TableHead>
                                <TableHead>
                                    <SortButton
                                        field="status"
                                        onClick={handleSort}
                                    >
                                        {t('Status')}
                                    </SortButton>
                                </TableHead>
                                <TableHead>{t('Actions')}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {sortedLoans.length === 0 ? (
                                <TableRow>
                                    <TableCell
                                        colSpan={4}
                                        className="text-center text-muted-foreground"
                                    >
                                        {t('No borrowed books')}
                                    </TableCell>
                                </TableRow>
                            ) : (
                                sortedLoans.map((loan) => (
                                    <TableRow key={loan.id}>
                                        <TableCell className="font-medium">
                                            {loan.bookCopy.book.title}
                                        </TableCell>
                                        <TableCell>
                                            {new Date(
                                                loan.borrowed_date,
                                            ).toLocaleDateString()}
                                        </TableCell>
                                        <TableCell>
                                            <Badge
                                                variant={
                                                    loan.returned_date
                                                        ? 'secondary'
                                                        : 'default'
                                                }
                                            >
                                                {loan.returned_date
                                                    ? t('Returned')
                                                    : t('Borrowed')}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            {!loan.returned_date && (
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    onClick={() =>
                                                        handleReturn(loan.id)
                                                    }
                                                >
                                                    {t('Return')}
                                                </Button>
                                            )}
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                </div>
            </div>
        </AppLayout>
    );
}
