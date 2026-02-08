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

interface User {
    id: number;
    name: string;
    email: string;
}

interface Loan {
    id: number;
    book_copy_id: number;
    user_id: number;
    borrowed_date: string;
    returned_date: string | null;
    book_copy: BookCopy;
    user: User;
}

interface Props {
    loans: Loan[];
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin' },
    { title: 'Loan Management', href: '/admin/loans' },
];

export default function AdminLoansIndex({ loans }: Props) {
    const { t } = useLaravelReactI18n();
    const [sorting, setSorting] = useState<SortingState>([
        { id: 'borrowed_date', desc: true },
    ]);

    const calculateDaysElapsed = (borrowedDate: string): number => {
        const borrowed = new Date(borrowedDate);
        const now = new Date();
        const diff = now.getTime() - borrowed.getTime();
        return Math.floor(diff / (1000 * 60 * 60 * 24));
    };

    const handleReturn = (loanId: number) => {
        if (confirm(t('Are you sure you want to return this book?'))) {
            router.put(
                `/loans/${loanId}`,
                {},
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        router.reload();
                    },
                },
            );
        }
    };

    const columns = useMemo<ColumnDef<Loan>[]>(
        () => [
            {
                accessorKey: 'book_copy.book.title',
                id: 'title',
                header: ({ column }) => {
                    return (
                        <Button
                            variant="ghost"
                            onClick={() =>
                                column.toggleSorting(column.getIsSorted() === 'asc')
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
                        {row.original.book_copy.book.title}
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
                                column.toggleSorting(column.getIsSorted() === 'asc')
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
                accessorKey: 'borrowed_date',
                header: ({ column }) => {
                    return (
                        <Button
                            variant="ghost"
                            onClick={() =>
                                column.toggleSorting(column.getIsSorted() === 'asc')
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
                        row.getValue('borrowed_date'),
                    ).toLocaleDateString();
                },
            },
            {
                id: 'days_elapsed',
                accessorFn: (row) => calculateDaysElapsed(row.borrowed_date),
                header: ({ column }) => {
                    return (
                        <Button
                            variant="ghost"
                            onClick={() =>
                                column.toggleSorting(column.getIsSorted() === 'asc')
                            }
                            className="h-auto p-0 font-semibold hover:bg-transparent"
                        >
                            {t('Loan Period')}
                            <ArrowUpDown className="ml-2 h-4 w-4" />
                        </Button>
                    );
                },
                cell: ({ row }) => {
                    const days = calculateDaysElapsed(row.original.borrowed_date);
                    return t(':count days', { count: days.toString() });
                },
            },
            {
                accessorKey: 'returned_date',
                id: 'status',
                header: ({ column }) => {
                    return (
                        <Button
                            variant="ghost"
                            onClick={() =>
                                column.toggleSorting(column.getIsSorted() === 'asc')
                            }
                            className="h-auto p-0 font-semibold hover:bg-transparent"
                        >
                            {t('Status')}
                            <ArrowUpDown className="ml-2 h-4 w-4" />
                        </Button>
                    );
                },
                cell: ({ row }) => {
                    const returned = row.getValue('returned_date');
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
                    if (loan.returned_date) {
                        return null;
                    }
                    return (
                        <Button
                            size="sm"
                            variant="outline"
                            onClick={() => handleReturn(loan.id)}
                        >
                            {t('Return')}
                        </Button>
                    );
                },
            },
        ],
        [t],
    );

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
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('Loan Management')} />

            <div className="space-y-6 px-4 py-6">
                <Heading title={t('Loan Management')} />

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
                                                          header.column.columnDef
                                                              .header,
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
            </div>
        </AppLayout>
    );
}
