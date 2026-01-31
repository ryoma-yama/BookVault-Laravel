import { Head } from '@inertiajs/react';
import AppearanceTabs from '@/components/appearance-tabs';
import Heading from '@/components/heading';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { edit as editAppearance } from '@/routes/appearance';
import type { BreadcrumbItem } from '@/types';
import { useLaravelReactI18n } from 'laravel-react-i18n';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Appearance settings',
        href: editAppearance().url,
    },
];

export default function Appearance() {
    const { t } = useLaravelReactI18n();

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('Appearance settings')} />

            <h1 className="sr-only">{t('Appearance Settings')}</h1>

            <SettingsLayout>
                <div className="space-y-6">
                    <Heading
                        variant="small"
                        title={t('Appearance settings')}
                        description={t(
                            "Update your account's appearance settings",
                        )}
                    />
                    <AppearanceTabs />
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
