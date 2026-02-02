import { Head, Link, usePage } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { dashboard, login, register } from '@/routes';
import type { SharedData } from '@/types';

export default function Welcome({
    canRegister = true,
}: {
    canRegister?: boolean;
}) {
    const { auth } = usePage<SharedData>().props;
    const { t } = useLaravelReactI18n();

    return (
        <>
            <Head title="BookVault">
                <meta
                    name="description"
                    content={t('Library management app built with Laravel/Inertia.')}
                />
            </Head>
            <div className="min-h-screen bg-background text-foreground">
                <div className="mx-auto flex max-w-xl flex-col items-center gap-10 px-4 py-24 text-center">
                    <h1 className="text-3xl font-bold">
                        {t('Welcome to BookVault')}
                    </h1>
                    <p className="text-muted-foreground">
                        {t('This app is designed for small-scale library management and supports fetching book information using the Google Books API.')}
                    </p>
                    <div className="flex flex-col items-center gap-4">
                        {!auth.user && (
                            <>
                                <p className="text-sm text-muted-foreground">
                                    {t('Login is required to borrow books.')}
                                </p>
                                <div className="flex gap-4">
                                    <Link
                                        href={login()}
                                        className="inline-flex h-10 items-center justify-center rounded-md border border-input bg-background px-8 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                                    >
                                        {t('Log In')}
                                    </Link>
                                    {canRegister && (
                                        <Link
                                            href={register()}
                                            className="inline-flex h-10 items-center justify-center rounded-md bg-primary px-8 text-sm font-medium text-primary-foreground ring-offset-background transition-colors hover:bg-primary/90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                                        >
                                            {t('Register')}
                                        </Link>
                                    )}
                                </div>
                            </>
                        )}
                        {auth.user && (
                            <>
                                <p className="text-sm text-muted-foreground">
                                    {t('Logged in as')}: {auth.user.name}
                                </p>
                                <Link
                                    href={dashboard()}
                                    className="inline-flex h-10 items-center justify-center rounded-md bg-primary px-8 text-sm font-medium text-primary-foreground ring-offset-background transition-colors hover:bg-primary/90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                                >
                                    {t('Go to Dashboard')}
                                </Link>
                            </>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
}
