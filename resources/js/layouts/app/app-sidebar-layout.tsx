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
    
    // Use ref to track if we've already shown this flash message
    // This makes the effect idempotent, which is the correct pattern for StrictMode
    // Reference: https://react.dev/reference/react/useEffect#my-effect-runs-twice-when-the-component-mounts
    const shownFlashRef = useRef<{ success?: string; error?: string }>({});

    useEffect(() => {
        // Only show toast if we haven't shown this exact message yet
        // This ensures the effect is idempotent even when StrictMode runs it twice
        if (flash?.success && flash.success !== shownFlashRef.current.success) {
            toast.success(flash.success);
            shownFlashRef.current.success = flash.success;
        }
        if (flash?.error && flash.error !== shownFlashRef.current.error) {
            toast.error(flash.error);
            shownFlashRef.current.error = flash.error;
        }

        // Reset tracking when navigating to a page without flash messages
        // This allows showing the same message again on the next action
        if (!flash?.success && !flash?.error) {
            shownFlashRef.current = {};
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
