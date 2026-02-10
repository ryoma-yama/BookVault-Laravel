'use no memo'; // React Compilerの最適化をこのファイルで無効化
import { Head, router } from '@inertiajs/react';
import {
    type ColumnDef,
    flexRender,
    getCoreRowModel,
    getSortedRowModel,
    type SortingState,
    useReactTable,
} from '@tanstack/react-table';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { ArrowUpDown } from 'lucide-react';
import { useMemo, useState } from 'react';
import Heading from '@/components/heading';
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
import AppCommonLayout from '@/layouts/app-common-layout';

interface Book {
    id: number;
    title: string;
    isbn13: string;
}

interface BookCopy {
    id: number;
    book: Book;
}

interface User {
    id: number;
    name: string;
    email: string;
}

interface Loan {
    id: number;
    bookCopyId: number;
    userId: number;
    borrowedDate: string;
    returnedDate: string | null;
    bookCopy: BookCopy;
    user: User;
}

interface Props {
    loans: Loan[];
}

export default function AdminLoansIndex({ loans }: Props) {
    const { t } = useLaravelReactI18n();
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('Admin'),
            href: '/admin/loans',
        },
        {
            title: t('Loans'),
            href: '/admin/loans',
        },
    ];

    const [sorting, setSorting] = useState<SortingState>([
        { id: 'borrowedDate', desc: true },
    ]);
    const [returnLoanId, setReturnLoanId] = useState<number | null>(null);

    const calculateDaysElapsed = (borrowedDate: string): number => {
        const borrowed = new Date(borrowedDate);
        const now = new Date();
        const diff = now.getTime() - borrowed.getTime();
        return Math.floor(diff / (1000 * 60 * 60 * 24));
    };

    const handleReturnClick = (loanId: number) => {
        setReturnLoanId(loanId);
    };

    const handleReturnConfirm = () => {
        if (returnLoanId !== null) {
            router.put(
                `/loans/${returnLoanId}`,
                {},
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        router.reload();
                        setReturnLoanId(null);
                    },
                },
            );
        }
    };

    const columns = useMemo<ColumnDef<Loan>[]>(
        () => [
            {
                accessorKey: 'bookCopy.book.title',
                id: 'title',
                header: ({ column }) => {
                    return (
                        <Button
                            variant="ghost"
                            onClick={() =>
                                column.toggleSorting(
                                    column.getIsSorted() === 'asc',
                                )
                            }
                            className="h-auto p-0 font-semibold hover:bg-transparent"
                        >
                            {t('Title')}
                            <ArrowUpDown className="ml-2 h-4 w-4" />
                        </Button>
                    );
                },
                cell: ({ row }) => (
                    <div className="font-medium">
                        {row.original.bookCopy.book.title}
                    </div>
                ),
            },
            {
                accessorKey: 'user.name',
                id: 'user',
                header: ({ column }) => {
                    return (
                        <Button
                            variant="ghost"
                            onClick={() =>
                                column.toggleSorting(
                                    column.getIsSorted() === 'asc',
                                )
                            }
                            className="h-auto p-0 font-semibold hover:bg-transparent"
                        >
                            {t('User')}
                            <ArrowUpDown className="ml-2 h-4 w-4" />
                        </Button>
                    );
                },
                cell: ({ row }) => row.original.user.name,
            },
            {
                accessorKey: 'borrowedDate',
                header: ({ column }) => {
                    return (
                        <Button
                            variant="ghost"
                            onClick={() =>
                                column.toggleSorting(
                                    column.getIsSorted() === 'asc',
                                )
                            }
                            className="h-auto p-0 font-semibold hover:bg-transparent"
                        >
                            {t('Borrowed Date')}
                            <ArrowUpDown className="ml-2 h-4 w-4" />
                        </Button>
                    );
                },
                cell: ({ row }) => {
                    return new Date(
                        row.getValue('borrowedDate'),
                    ).toLocaleDateString();
                },
            },
            {
                id: 'daysElapsed',
                accessorFn: (row) => calculateDaysElapsed(row.borrowedDate),
                header: ({ column }) => {
                    return (
                        <Button
                            variant="ghost"
                            onClick={() =>
                                column.toggleSorting(
                                    column.getIsSorted() === 'asc',
                                )
                            }
                            className="h-auto p-0 font-semibold hover:bg-transparent"
                        >
                            {t('Loan Period')}
                            <ArrowUpDown className="ml-2 h-4 w-4" />
                        </Button>
                    );
                },
                cell: ({ row }) => {
                    const days = calculateDaysElapsed(
                        row.original.borrowedDate,
                    );
                    return t(':count days', { count: days.toString() });
                },
            },
            {
                accessorKey: 'returnedDate',
                id: 'status',
                header: ({ column }) => {
                    return (
                        <Button
                            variant="ghost"
                            onClick={() =>
                                column.toggleSorting(
                                    column.getIsSorted() === 'asc',
                                )
                            }
                            className="h-auto p-0 font-semibold hover:bg-transparent"
                        >
                            {t('Status')}
                            <ArrowUpDown className="ml-2 h-4 w-4" />
                        </Button>
                    );
                },
                cell: ({ row }) => {
                    const returned = row.original.returnedDate;
                    return (
                        <Badge variant={returned ? 'secondary' : 'default'}>
                            {returned ? t('Returned') : t('Loaned')}
                        </Badge>
                    );
                },
            },
            {
                id: 'actions',
                header: () => t('Actions'),
                cell: ({ row }) => {
                    const loan = row.original;
                    if (loan.returnedDate) {
                        return null;
                    }
                    return (
                        <Button
                            size="sm"
                            variant="outline"
                            onClick={() => handleReturnClick(loan.id)}
                        >
                            {t('Return')}
                        </Button>
                    );
                },
            },
        ],
        [t],
    );

    // eslint-disable-next-line react-hooks/incompatible-library
    const table = useReactTable({
        data: loans,
        columns,
        getCoreRowModel: getCoreRowModel(),
        getSortedRowModel: getSortedRowModel(),
        onSortingChange: setSorting,
        state: {
            sorting,
        },
    });

    return (
        <AppCommonLayout title={t('Loans')} breadcrumbs={breadcrumbs}>
            <div className="rounded-md border">
                <Table>
                    <TableHeader>
                        {table.getHeaderGroups().map((headerGroup) => (
                            <TableRow key={headerGroup.id}>
                                {headerGroup.headers.map((header) => {
                                    return (
                                        <TableHead key={header.id}>
                                            {header.isPlaceholder
                                                ? null
                                                : flexRender(
                                                    header.column
                                                        .columnDef.header,
                                                    header.getContext(),
                                                )}
                                        </TableHead>
                                    );
                                })}
                            </TableRow>
                        ))}
                    </TableHeader>
                    <TableBody>
                        {table.getRowModel().rows?.length ? (
                            table.getRowModel().rows.map((row) => (
                                <TableRow
                                    key={row.id}
                                    data-state={
                                        row.getIsSelected() && 'selected'
                                    }
                                >
                                    {row.getVisibleCells().map((cell) => (
                                        <TableCell key={cell.id}>
                                            {flexRender(
                                                cell.column.columnDef.cell,
                                                cell.getContext(),
                                            )}
                                        </TableCell>
                                    ))}
                                </TableRow>
                            ))
                        ) : (
                            <TableRow>
                                <TableCell
                                    colSpan={columns.length}
                                    className="h-24 text-center"
                                >
                                    {t('No loans found')}
                                </TableCell>
                            </TableRow>
                        )}
                    </TableBody>
                </Table>
            </div>

            <AlertDialog
                open={returnLoanId !== null}
                onOpenChange={(open) => !open && setReturnLoanId(null)}
            >
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>{t('Return Book')}</AlertDialogTitle>
                        <AlertDialogDescription>
                            {t('Are you sure you want to return this book?')}
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>{t('Cancel')}</AlertDialogCancel>
                        <AlertDialogAction onClick={handleReturnConfirm}>
                            {t('Return')}
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </AppCommonLayout>
    );
}
