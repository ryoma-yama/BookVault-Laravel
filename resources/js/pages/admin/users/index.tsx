import { Head, Link, router } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { useState } from 'react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
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

type UserRole = 'admin' | 'user';

interface User {
    id: number;
    name: string;
    email: string;
    role: UserRole;
    created_at: string;
}

interface PaginatedResponse<T> {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
}

interface Filters {
    search?: string;
    role?: string;
}

interface Props {
    users: PaginatedResponse<User>;
    filters: Filters;
}

function getRoleBadgeVariant(role: UserRole): 'default' | 'secondary' {
    return role === 'admin' ? 'default' : 'secondary';
}

function getRoleDisplayName(role: UserRole): string {
    return role === 'admin' ? 'Admin' : 'User';
}

export default function AdminUsersIndex({ users, filters }: Props) {
    const { t } = useLaravelReactI18n();
    const [search, setSearch] = useState(filters.search || '');
    const [role, setRole] = useState(filters.role || 'all');

    const breadcrumbs: BreadcrumbItem[] = [
            {
                title: t('Admin'),
                href: '/admin/users',
            },
            {
                title: t('Users'),
                href: '/admin/users',
            },
    ];
    
    const handleSearch = () => {
        router.get(
            '/admin/users',
            {
                search: search || undefined,
                role: role !== 'all' ? role : undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    const updateRole = (userId: number, newRole: string) => {
        if (confirm(t("Are you sure you want to update this user's role?"))) {
            router.patch(
                `/admin/users/${userId}`,
                { role: newRole },
                {
                    preserveScroll: true,
                },
            );
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('User Management')} />

            <div className="space-y-6 px-4 py-6">
                <Heading title={t('User Management')} />
                <div className="flex gap-4">
                    <Input
                        type="text"
                        placeholder={t('Search by name or email...')}
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                        className="max-w-sm"
                    />

                    <Select value={role} onValueChange={setRole}>
                        <SelectTrigger className="w-[180px]">
                            <SelectValue placeholder={t('Filter by role')} />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">
                                {t('All Roles')}
                            </SelectItem>
                            <SelectItem value="admin">{t('Admin')}</SelectItem>
                            <SelectItem value="user">{t('User')}</SelectItem>
                        </SelectContent>
                    </Select>

                    <Button onClick={handleSearch}>{t('Search')}</Button>
                </div>

                <div className="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{t('Name')}</TableHead>
                                <TableHead>{t('Email')}</TableHead>
                                <TableHead>{t('Role')}</TableHead>
                                <TableHead>{t('Created')}</TableHead>
                                <TableHead>{t('Actions')}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {users.data.map((user) => (
                                <TableRow key={user.id}>
                                    <TableCell className="font-medium">
                                        {user.name}
                                    </TableCell>
                                    <TableCell>{user.email}</TableCell>
                                    <TableCell>
                                        <Badge
                                            variant={getRoleBadgeVariant(
                                                user.role,
                                            )}
                                        >
                                            {t(getRoleDisplayName(user.role))}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        {new Date(
                                            user.created_at,
                                        ).toLocaleDateString()}
                                    </TableCell>
                                    <TableCell>
                                        <Select
                                            value={user.role}
                                            onValueChange={(value) =>
                                                updateRole(user.id, value)
                                            }
                                        >
                                            <SelectTrigger className="w-[120px]">
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="user">
                                                    {t('User')}
                                                </SelectItem>
                                                <SelectItem value="admin">
                                                    {t('Admin')}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </div>

                {users.last_page > 1 && (
                    <div className="flex items-center justify-between">
                        <p className="text-sm text-muted-foreground">
                            {t('Showing :count of :total users', {
                                count: users.data.length.toString(),
                                total: users.total.toString(),
                            })}
                        </p>
                        <div className="flex gap-2">
                            {users.current_page > 1 && (
                                <Link
                                    href={`/admin/users?page=${users.current_page - 1}`}
                                    preserveState
                                    preserveScroll
                                >
                                    <Button variant="outline">
                                        {t('Previous')}
                                    </Button>
                                </Link>
                            )}
                            {users.current_page < users.last_page && (
                                <Link
                                    href={`/admin/users?page=${users.current_page + 1}`}
                                    preserveState
                                    preserveScroll
                                >
                                    <Button variant="outline">
                                        {t('Next')}
                                    </Button>
                                </Link>
                            )}
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
