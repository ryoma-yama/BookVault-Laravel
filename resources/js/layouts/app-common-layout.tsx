import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

interface Props {
    title: string;
    breadcrumbs?: BreadcrumbItem[];
    children: React.ReactNode;
}

export default function AppCommonLayout({
    title,
    breadcrumbs = [],
    children,
}: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />
            <div className="space-y-6 px-4 py-6">
                <Heading title={title} />
                {children}
            </div>
        </AppLayout>
    );
}
