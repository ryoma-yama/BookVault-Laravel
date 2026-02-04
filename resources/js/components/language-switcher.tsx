import { router, usePage } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Globe } from 'lucide-react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import type { SharedData } from '@/types';

export function LanguageSwitcher() {
    const { locale } = usePage<SharedData>().props;
    const { t, setLocale } = useLaravelReactI18n();

    const handleLanguageSwitch = (newLocale: string) => {
        router.get(`/locale/${newLocale}`, {}, {
            preserveState: false,
            preserveScroll: true,
            onSuccess: () => {
                setLocale(newLocale);
            },
        });
    };

    return (
        <SidebarMenu>
            <SidebarMenuItem>
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <SidebarMenuButton
                            size="default"
                            tooltip={t('Language')}
                        >
                            <Globe />
                            <span>{t('Language')}</span>
                        </SidebarMenuButton>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent side="right" align="start">
                        <DropdownMenuItem
                            onClick={() => handleLanguageSwitch('ja')}
                            className={`${locale === 'ja' ? 'bg-accent' : ''} cursor-pointer`}
                        >
                            日本語
                        </DropdownMenuItem>
                        <DropdownMenuItem
                            onClick={() => handleLanguageSwitch('en')}
                            className={`${locale === 'en' ? 'bg-accent' : ''} cursor-pointer`}
                        >
                            English
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </SidebarMenuItem>
        </SidebarMenu>
    );
}
