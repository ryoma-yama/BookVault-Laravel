import { usePage } from '@inertiajs/react';
import { useEffect, useRef } from 'react';
import { toast } from 'sonner';
import { AppContent } from '@/components/app-content';
import { AppShell } from '@/components/app-shell';
import { AppSidebar } from '@/components/app-sidebar';
import { AppSidebarHeader } from '@/components/app-sidebar-header';
import { Toaster } from '@/components/ui/sonner';
import type { AppLayoutProps, SharedData } from '@/types';

export default function AppSidebarLayout({
    children,
    breadcrumbs = [],
}: AppLayoutProps) {
    const { flash } = usePage<SharedData>().props;
    
    // Track the last shown toast message to prevent duplicates
    // This is necessary because React StrictMode intentionally runs effects twice in development
    const lastToastRef = useRef<{ success?: string; error?: string }>({});

    useEffect(() => {
        // Only show toast if the message is different from the last one shown
        // This prevents duplicate toasts from StrictMode double-rendering in development
        if (flash?.success && flash.success !== lastToastRef.current.success) {
            toast.success(flash.success);
            lastToastRef.current.success = flash.success;
        }
        if (flash?.error && flash.error !== lastToastRef.current.error) {
            toast.error(flash.error);
            lastToastRef.current.error = flash.error;
        }

        // Reset the reference when navigating to a page without flash messages
        // This allows the same message to show again on subsequent actions
        if (!flash?.success && !flash?.error) {
            lastToastRef.current = {};
        }
    }, [flash?.success, flash?.error]);

    return (
        <AppShell variant="sidebar">
            <AppSidebar />
            <AppContent variant="sidebar" className="overflow-x-hidden">
                <AppSidebarHeader breadcrumbs={breadcrumbs} />
                {children}
            </AppContent>
            <Toaster position="bottom-center" />
        </AppShell>
    );
}
