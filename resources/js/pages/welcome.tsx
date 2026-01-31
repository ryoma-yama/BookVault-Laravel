import { Head, Link, usePage } from '@inertiajs/react';
import type { SharedData } from '@/types';
import { dashboard, login, register } from '@/routes';

export default function Welcome({
    canRegister = true,
}: {
    canRegister?: boolean;
}) {
    const { auth } = usePage<SharedData>().props;

    return (
        <>
            <Head title="BookVault">
                <meta
                    name="description"
                    content="Laravel/Inertia による蔵書管理アプリです。"
                />
            </Head>
            <div className="min-h-screen bg-background text-foreground">
                <div className="mx-auto flex max-w-xl flex-col items-center gap-10 px-4 py-24 text-center">
                    <h1 className="text-3xl font-bold">
                        蔵書管理アプリへようこそ
                    </h1>
                    <p className="text-muted-foreground">
                        本アプリは、小規模向けの蔵書管理を目的としており、Google
                        Books API を用いた書籍情報の取得に対応しています。
                    </p>
                    <div className="flex flex-col items-center gap-4">
                        {!auth.user && (
                            <>
                                <p className="text-sm text-muted-foreground">
                                    書籍の貸出にはログインが必要です。
                                </p>
                                <div className="flex gap-4">
                                    <Link
                                        href={login()}
                                        className="inline-flex h-10 items-center justify-center rounded-md border border-input bg-background px-8 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                                    >
                                        ログイン
                                    </Link>
                                    {canRegister && (
                                        <Link
                                            href={register()}
                                            className="inline-flex h-10 items-center justify-center rounded-md bg-primary px-8 text-sm font-medium text-primary-foreground ring-offset-background transition-colors hover:bg-primary/90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                                        >
                                            新規登録
                                        </Link>
                                    )}
                                </div>
                            </>
                        )}
                        {auth.user && (
                            <>
                                <p className="text-sm text-muted-foreground">
                                    ログイン中: {auth.user.name}
                                </p>
                                <Link
                                    href={dashboard()}
                                    className="inline-flex h-10 items-center justify-center rounded-md bg-primary px-8 text-sm font-medium text-primary-foreground ring-offset-background transition-colors hover:bg-primary/90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                                >
                                    ダッシュボードへ
                                </Link>
                            </>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
}
