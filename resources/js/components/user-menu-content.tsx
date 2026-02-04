import { Link, router, usePage } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { LogOut, Settings } from 'lucide-react';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuSub,
    DropdownMenuSubContent,
    DropdownMenuSubTrigger,
} from '@/components/ui/dropdown-menu';
import { UserInfo } from '@/components/user-info';
import { useMobileNavigation } from '@/hooks/use-mobile-navigation';
import { logout } from '@/routes';
import { edit } from '@/routes/profile';
import type { SharedData, User } from '@/types';

type Props = {
    user: User;
};

export function UserMenuContent({ user }: Props) {
    const cleanup = useMobileNavigation();
    const { locale } = usePage<SharedData>().props;
    const { t } = useLaravelReactI18n();

    const handleLogout = () => {
        cleanup();
        router.flushAll();
    };

    const handleLanguageSwitch = (newLocale: string) => {
        cleanup();
        router.get(`/locale/${newLocale}`);
    };

    return (
        <>
            <DropdownMenuLabel className="p-0 font-normal">
                <div className="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                    <UserInfo user={user} showEmail={true} />
                </div>
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuGroup>
                <DropdownMenuItem asChild>
                    <Link
                        className="block w-full cursor-pointer"
                        href={edit()}
                        prefetch
                        onClick={cleanup}
                    >
                        <Settings className="mr-2" />
                        {t('Settings')}
                    </Link>
                </DropdownMenuItem>
                <DropdownMenuSub>
                    <DropdownMenuSubTrigger className="cursor-pointer">
                        <span className="ml-8">{t('Language')}</span>
                    </DropdownMenuSubTrigger>
                    <DropdownMenuSubContent>
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
                    </DropdownMenuSubContent>
                </DropdownMenuSub>
            </DropdownMenuGroup>
            <DropdownMenuSeparator />
            <DropdownMenuItem asChild>
                <Link
                    className="block w-full cursor-pointer"
                    href={logout()}
                    as="button"
                    onClick={handleLogout}
                    data-test="logout-button"
                >
                    <LogOut className="mr-2" />
                    {t('Log Out')}
                </Link>
            </DropdownMenuItem>
        </>
    );
}
