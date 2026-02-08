import { Link, usePage } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { BookCheck, BookOpen, ClipboardList, Library, Users } from 'lucide-react';
import { LanguageSwitcher } from '@/components/language-switcher';
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
import { home } from '@/routes';
import admin from '@/routes/admin';
import borrowed from '@/routes/borrowed';
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
            href: home(),
            icon: Library,
            isActive: isCurrentUrl(home()),
        },
        {
            title: t('Borrowed Books'),
            href: borrowed.index(),
            icon: BookCheck,
            isActive: isCurrentUrl(borrowed.index()),
        },
    ];

    // Admin navigation items
    const adminNavItems: NavItem[] = [
        // {
        //     title: t('Admin Dashboard'),
        //     href: admin.dashboard(),
        //     icon: LayoutGrid,
        //     isActive: isCurrentUrl(admin.dashboard()),
        // },
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
        {
            title: t('Loan Management'),
            href: admin.loans.index(),
            icon: ClipboardList,
            isActive: isCurrentUrl(admin.loans.index()),
        },
    ];

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={home()} prefetch>
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
                <LanguageSwitcher />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
