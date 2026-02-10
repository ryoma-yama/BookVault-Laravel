'use no memo';
import { Link } from '@inertiajs/react';
import { router } from '@inertiajs/react';
import {
    type ColumnDef,
    type ColumnFiltersState,
    flexRender,
    getCoreRowModel,
    getFilteredRowModel,
    getSortedRowModel,
    type SortingState,
    useReactTable,
} from '@tanstack/react-table';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { useMemo, useState } from 'react';
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
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppCommonLayout from '@/layouts/app-common-layout';

import type { BreadcrumbItem } from '@/types';

interface Tag {
    id: number;
    name: string;
}

interface Author {
    id: number;
    name: string;
}

interface Book {
    id: number;
    title: string;
    authors: Author[];
    tags: Tag[];
    copies_count: number;
}

interface PaginatedBooks {
    data: Book[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Props {
    books: PaginatedBooks;
}

export default function AdminBooksIndex({ books }: Props) {
    const { t } = useLaravelReactI18n();
    const [sorting, setSorting] = useState<SortingState>([]);
    const [columnFilters, setColumnFilters] = useState<ColumnFiltersState>([]);
    const [deleteBookId, setDeleteBookId] = useState<number | null>(null);

    const handleDeleteClick = (bookId: number) => {
        setDeleteBookId(bookId);
    };

    const handleDeleteConfirm = () => {
        if (deleteBookId !== null) {
            router.delete(`/admin/books/${deleteBookId}`, {
                preserveScroll: true,
                onSuccess: () => {
                    setDeleteBookId(null);
                },
            });
        }
    };

    const columns = useMemo<ColumnDef<Book>[]>(
        () => [
            {
                accessorKey: 'title',
                header: () => t('Title'),
                cell: ({ row }) => (
                    <div className="font-medium">{row.original.title}</div>
                ),
            },
            {
                id: 'tags',
                accessorFn: (row) =>
                    row.tags.map((tag) => tag.name).join(' '),
                header: () => t('Tags'),
                cell: ({ row }) => {
                    const book = row.original;
                    if (book.tags.length === 0) {
                        return (
                            <span className="text-muted-foreground">
                                {t('No tags')}
                            </span>
                        );
                    }
                    return (
                        <div className="flex flex-wrap gap-1">
                            {book.tags.map((tag) => (
                                <Badge key={tag.id} variant="secondary">
                                    #{tag.name}
                                </Badge>
                            ))}
                        </div>
                    );
                },
                filterFn: (row, id, value) => {
                    const searchValue = value.toLowerCase();
                    return row.original.tags.some((tag) =>
                        tag.name.toLowerCase().includes(searchValue),
                    );
                },
            },
            {
                accessorKey: 'copies_count',
                header: () => t('Inventory'),
                cell: ({ row }) => {
                    const count = row.original.copies_count;
                    return (
                        <div className="text-center">
                            <span
                                className={
                                    count > 0
                                        ? 'text-foreground'
                                        : 'text-muted-foreground'
                                }
                            >
                                {count}
                            </span>
                        </div>
                    );
                },
            },
            {
                id: 'actions',
                header: () => t('Actions'),
                cell: ({ row }) => {
                    const book = row.original;
                    return (
                        <div className="flex gap-2">
                            <Link href={`/admin/books/${book.id}/edit`}>
                                <Button variant="outline" size="sm">
                                    {t('Edit')}
                                </Button>
                            </Link>
                            <Button
                                variant="destructive"
                                size="sm"
                                onClick={() => handleDeleteClick(book.id)}
                            >
                                {t('Delete')}
                            </Button>
                        </div>
                    );
                },
            },
        ],
        [t],
    );

    const table = useReactTable({
        data: books.data,
        columns,
        getCoreRowModel: getCoreRowModel(),
        getSortedRowModel: getSortedRowModel(),
        getFilteredRowModel: getFilteredRowModel(),
        onSortingChange: setSorting,
        onColumnFiltersChange: setColumnFilters,
        state: {
            sorting,
            columnFilters,
        },
    });

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('Admin'),
            href: '/admin/books',
        },
        {
            title: t('Books'),
            href: '/admin/books',
        },
    ];

    return (
        <AppCommonLayout title={t('Books')} breadcrumbs={breadcrumbs}>
            <div className="mb-4 flex items-center justify-between">
                <div className="flex flex-1 items-center gap-2">
                    <Input
                        placeholder={t('Filter by tags...')}
                        value={
                            (table.getColumn('tags')?.getFilterValue() as
                                | string
                                | undefined) ?? ''
                        }
                        onChange={(event) =>
                            table
                                .getColumn('tags')
                                ?.setFilterValue(event.target.value)
                        }
                        className="max-w-sm"
                    />
                </div>
                <Link href="/admin/books/create">
                    <Button>{t('Add New Book')}</Button>
                </Link>
            </div>

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
                                    {t('No books found')}
                                </TableCell>
                            </TableRow>
                        )}
                    </TableBody>
                </Table>
            </div>

            <AlertDialog
                open={deleteBookId !== null}
                onOpenChange={(open) => !open && setDeleteBookId(null)}
            >
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>
                            {t('Delete Book')}
                        </AlertDialogTitle>
                        <AlertDialogDescription>
                            {t('Are you sure you want to delete this book?')}
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>{t('Cancel')}</AlertDialogCancel>
                        <AlertDialogAction onClick={handleDeleteConfirm}>
                            {t('Delete')}
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </AppCommonLayout>
    );
}
