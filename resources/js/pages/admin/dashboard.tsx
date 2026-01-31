import { Head, Link } from '@inertiajs/react';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import { useLaravelReactI18n } from 'laravel-react-i18n';

interface Props {
    stats: {
        total_books: number;
        total_users: number;
        active_loans: number;
        total_loans: number;
    };
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin' },
    { title: 'Dashboard', href: '/admin' },
];

export default function AdminDashboard({ stats }: Props) {
    const { t } = useLaravelReactI18n();

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('Admin Dashboard')} />

            <div className="space-y-6">
                <div>
                    <h1 className="text-3xl font-bold">
                        {t('Admin Dashboard')}
                    </h1>
                    <p className="mt-2 text-muted-foreground">
                        {t('Overview of your library management system')}
                    </p>
                </div>

                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                {t('Total Books')}
                            </CardTitle>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth="2"
                                className="h-4 w-4 text-muted-foreground"
                            >
                                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" />
                                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" />
                            </svg>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {stats.total_books}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                {t('books in the library')}
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                {t('Total Users')}
                            </CardTitle>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth="2"
                                className="h-4 w-4 text-muted-foreground"
                            >
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
                            </svg>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {stats.total_users}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                {t('registered users')}
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                {t('Active Loans')}
                            </CardTitle>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth="2"
                                className="h-4 w-4 text-muted-foreground"
                            >
                                <path d="M12 20h9" />
                                <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" />
                            </svg>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {stats.active_loans}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                {t('books currently on loan')}
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                {t('Total Loans')}
                            </CardTitle>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth="2"
                                className="h-4 w-4 text-muted-foreground"
                            >
                                <rect
                                    width="20"
                                    height="14"
                                    x="2"
                                    y="5"
                                    rx="2"
                                />
                                <path d="M2 10h20" />
                            </svg>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {stats.total_loans}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                {t('all-time loans')}
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid gap-4 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>{t('Quick Actions')}</CardTitle>
                            <CardDescription>
                                {t('Manage your library')}
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-2">
                            <Link
                                href="/admin/users"
                                className="block rounded-lg border p-3 transition-colors hover:bg-accent"
                            >
                                <div className="font-medium">
                                    {t('User Management')}
                                </div>
                                <div className="text-sm text-muted-foreground">
                                    {t('Manage user accounts and roles')}
                                </div>
                            </Link>
                            <Link
                                href="/books"
                                className="block rounded-lg border p-3 transition-colors hover:bg-accent"
                            >
                                <div className="font-medium">
                                    {t('Book Collection')}
                                </div>
                                <div className="text-sm text-muted-foreground">
                                    {t('Browse and search books')}
                                </div>
                            </Link>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>{t('System Overview')}</CardTitle>
                            <CardDescription>
                                {t('Library statistics')}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                <div className="flex justify-between">
                                    <span className="text-sm text-muted-foreground">
                                        {t('Books per user')}
                                    </span>
                                    <span className="font-medium">
                                        {stats.total_users > 0
                                            ? (
                                                  stats.total_books /
                                                  stats.total_users
                                              ).toFixed(2)
                                            : '0'}
                                    </span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-sm text-muted-foreground">
                                        {t('Active loan rate')}
                                    </span>
                                    <span className="font-medium">
                                        {stats.total_loans > 0
                                            ? (
                                                  (stats.active_loans /
                                                      stats.total_loans) *
                                                  100
                                              ).toFixed(1)
                                            : '0'}
                                        %
                                    </span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
