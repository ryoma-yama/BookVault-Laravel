import { Link, usePage } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { BookOpen, Library, LayoutGrid, Users } from 'lucide-react';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/hooks/use-current-url';
import admin from '@/routes/admin';
import books from '@/routes/books';
import type { NavItem, SharedData } from '@/types';
import AppLogo from './app-logo';

export function AppSidebar() {
    const { t } = useLaravelReactI18n();
    const { auth } = usePage<SharedData>().props;
    const { isCurrentUrl } = useCurrentUrl();

    // General user navigation items
    const generalNavItems: NavItem[] = [
        {
            title: t('Books'),
            href: books.index(),
            icon: Library,
            isActive: isCurrentUrl(books.index()),
        },
    ];

    // Admin navigation items
    const adminNavItems: NavItem[] = [
        {
            title: t('Admin Dashboard'),
            href: admin.dashboard(),
            icon: LayoutGrid,
            isActive: isCurrentUrl(admin.dashboard()),
        },
        {
            title: t('Book Management'),
            href: admin.books.index(),
            icon: BookOpen,
            isActive: isCurrentUrl(admin.books.index()),
        },
        {
            title: t('User Management'),
            href: admin.users.index(),
            icon: Users,
            isActive: isCurrentUrl(admin.users.index()),
        },
    ];

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={books.index()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain label={t('Application')} items={generalNavItems} />
                {auth.user?.role === 'admin' && (
                    <NavMain
                        label={t('Administration')}
                        items={adminNavItems}
                    />
                )}
            </SidebarContent>

            <SidebarFooter>
                {/* <NavFooter items={footerNavItems} className="mt-auto" /> */}
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
