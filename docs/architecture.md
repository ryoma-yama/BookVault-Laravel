# システムアーキテクチャ

BookVault-Laravelのシステム構成とアーキテクチャについて説明します。

## 🏗️ 技術スタック

### バックエンド

#### Laravel 12
- **フレームワーク**: Laravel 12（PHP 8.2以上）
- **認証**: Laravel Fortify（ヘッドレス認証）
- **ルーティング**: Laravel Wayfinder（TypeScript型安全ルーティング）
- **テスト**: Pest v4（PHPテストフレームワーク）
- **コード整形**: Laravel Pint
- **開発ツール**: Laravel Pail（ログビューア）、Laravel Tinker

#### データベース
- **RDBMS**: PostgreSQL 18
- **キャッシュ/キュー**: Redis
- **全文検索**: Meilisearch

### フロントエンド

#### React & TypeScript
- **UIフレームワーク**: React 19
- **言語**: TypeScript 5.7
- **SSRフレームワーク**: Inertia.js v2
- **ビルドツール**: Vite 7

#### UIライブラリ
- **コンポーネント**: Radix UI（ヘッドレスUIコンポーネント）
- **スタイリング**: Tailwind CSS 4
- **アイコン**: Lucide React
- **ユーティリティ**: 
  - class-variance-authority（CVA）
  - clsx、tailwind-merge
  - tw-animate-css（アニメーション）

#### フォーム・入力
- **Headless UI**: @headlessui/react
- **OTP入力**: input-otp

### 開発環境

#### コンテナ化
- **開発環境**: Dev Container（VS Code）
- **本番環境**: Laravel Sail（Docker Compose）

#### 品質管理ツール
- **リンター**: ESLint 9
- **フォーマッター**: Prettier 3
- **型チェック**: TypeScript

## 📐 システムアーキテクチャ

### アーキテクチャパターン

```
┌─────────────────────────────────────────────────────┐
│                   クライアント                         │
│              （Webブラウザ）                          │
└─────────────────┬───────────────────────────────────┘
                  │ HTTP/HTTPS
                  │
┌─────────────────▼───────────────────────────────────┐
│              Webサーバー層                            │
│                （Nginx/Apache）                      │
└─────────────────┬───────────────────────────────────┘
                  │
┌─────────────────▼───────────────────────────────────┐
│           アプリケーション層                          │
│                                                      │
│  ┌──────────────────────────────────────────────┐  │
│  │         Laravel Application                  │  │
│  │  ┌────────────┐      ┌───────────────┐      │  │
│  │  │ Controller │◄────►│   Inertia.js  │      │  │
│  │  └──────┬─────┘      └───────┬───────┘      │  │
│  │         │                    │              │  │
│  │  ┌──────▼─────┐      ┌───────▼───────┐      │  │
│  │  │   Model    │      │  React Views  │      │  │
│  │  │  (Eloquent)│      │  (TypeScript) │      │  │
│  │  └──────┬─────┘      └───────────────┘      │  │
│  │         │                                    │  │
│  │  ┌──────▼─────────────────────────────┐      │  │
│  │  │  Middleware & Service Layer        │      │  │
│  │  │  - Authentication (Fortify)        │      │  │
│  │  │  - Authorization (Policies)        │      │  │
│  │  │  - Business Logic                  │      │  │
│  │  └────────────────────────────────────┘      │  │
│  └──────────────────────────────────────────────┘  │
└─────────────────┬───────────────────────────────────┘
                  │
        ┌─────────┴─────────┐
        │                   │
┌───────▼────────┐  ┌───────▼────────┐  ┌──────────────┐
│   PostgreSQL   │  │     Redis      │  │ Meilisearch  │
│   Database     │  │  Cache/Queue   │  │    Search    │
└────────────────┘  └────────────────┘  └──────────────┘
```

### レイヤー構造

#### プレゼンテーション層
- **React Components**: TypeScriptで記述されたUIコンポーネント
- **Inertia.js**: サーバーサイドとクライアントサイドの橋渡し
- **Tailwind CSS**: ユーティリティファーストのスタイリング

#### アプリケーション層
- **Controllers**: HTTPリクエストの処理
- **Requests**: バリデーションロジック
- **Middleware**: リクエスト/レスポンスのフィルタリング
- **Actions**: ビジネスロジックのカプセル化

#### ドメイン層
- **Models**: Eloquent ORM モデル
- **Policies**: 認可ロジック
- **Services**: ビジネスロジックの実装

#### インフラストラクチャ層
- **Database**: PostgreSQLデータベース
- **Cache**: Redisキャッシュ
- **Queue**: Redisキュー
- **Search**: Meilisearch全文検索エンジン

## 💾 データベース設計

### 主要テーブル

#### users（ユーザー）
- 基本情報（名前、メール、パスワード）
- 役割（role: user/admin）
- 表示名（display_name）
- 二要素認証情報

#### books（書籍）
- 書籍基本情報
- タイトル、ISBN、出版社、出版日
- 説明文、ページ数
- 書影URL

#### authors（著者）
- 著者名
- 経歴情報

#### book_authors（書籍-著者中間テーブル）
- 多対多リレーション

#### tags（タグ）
- タグ名
- スラッグ

#### book_tag（書籍-タグ中間テーブル）
- 多対多リレーション

#### book_copies（蔵書コピー）
- 個別の書籍コピー
- 在庫状態（available/loaned/maintenance）
- 所蔵場所
- 識別番号

#### loans（貸出）
- 貸出情報
- ユーザー、蔵書コピー
- 貸出日、返却予定日、返却日
- 貸出状態

#### reservations（予約）
- 予約情報
- ユーザー、書籍
- 予約日、ステータス

#### reviews（レビュー）
- ユーザーレビュー
- 評価（1-5）
- コメント
- 投稿日時

### リレーションシップ

```
User ──────────< Loan >────────── BookCopy
 │                                     │
 │                                     │
 └──────────< Reservation              │
 │                │                    │
 │                │                    │
 └──────────< Review                   │
                 │                     │
                 └────────> Book <─────┘
                              │
                              ├──< book_authors >──── Author
                              │
                              └──< book_tag >──────── Tag
```

## 📁 ディレクトリ構造

### バックエンド（Laravel）

```
app/
├── Actions/              # アクションクラス
├── Concerns/             # 共通トレイト
├── Helpers/              # ヘルパー関数
├── Http/
│   ├── Controllers/      # コントローラー
│   │   ├── Admin/        # 管理者用コントローラー
│   │   ├── Api/          # APIコントローラー
│   │   └── Settings/     # 設定用コントローラー
│   ├── Middleware/       # ミドルウェア
│   └── Requests/         # フォームリクエスト
├── Models/               # Eloquentモデル
├── Policies/             # 認可ポリシー
├── Providers/            # サービスプロバイダー
└── Services/             # サービスクラス

database/
├── factories/            # モデルファクトリー
├── migrations/           # マイグレーション
└── seeders/              # シーダー

routes/
├── api.php               # APIルート
├── web.php               # Webルート
├── console.php           # コンソールルート
└── settings.php          # 設定ルート

tests/
├── Feature/              # 機能テスト
└── Unit/                 # 単体テスト
```

### フロントエンド（React + TypeScript）

```
resources/js/
├── actions/              # Wayfinderアクション
├── components/           # Reactコンポーネント
│   ├── ui/               # UIコンポーネント（Radix UI）
│   └── layout/           # レイアウトコンポーネント
├── hooks/                # カスタムフック
├── layouts/              # ページレイアウト
├── lib/                  # ユーティリティライブラリ
├── pages/                # ページコンポーネント
│   ├── admin/            # 管理画面
│   ├── auth/             # 認証画面
│   ├── books/            # 書籍関連画面
│   └── settings/         # 設定画面
├── routes/               # Wayfinderルート定義
├── types/                # TypeScript型定義
├── app.tsx               # アプリケーションルート
└── ssr.tsx               # SSRエントリーポイント
```

## 🔄 データフロー

### 標準的なリクエストフロー

1. **ユーザーアクション**: ブラウザでボタンクリックやフォーム送信
2. **Inertia Request**: クライアントサイドでInertiaリクエスト生成
3. **Laravel Route**: サーバーサイドでルーティング
4. **Middleware**: 認証・認可チェック
5. **Controller**: ビジネスロジック実行
6. **Model/Service**: データ処理
7. **Database**: データ取得・保存
8. **Inertia Response**: JSON形式でレスポンス
9. **React Render**: コンポーネント再レンダリング
10. **UI Update**: ブラウザ表示更新

### キャッシュ戦略

- **Redisキャッシュ**: 頻繁にアクセスされるデータ
- **Databaseキャッシュ**: セッション、キューデータ
- **ブラウザキャッシュ**: 静的アセット（Vite）

### 検索フロー

1. **ユーザー入力**: 検索クエリ入力
2. **Meilisearch**: 全文検索実行
3. **Results**: 関連度順でソート済み結果
4. **Highlight**: 検索語句のハイライト表示

## 🔒 セキュリティアーキテクチャ

### 認証フロー（Laravel Fortify）

1. **登録**: メール確認付きユーザー登録
2. **ログイン**: 通常ログイン or 二要素認証
3. **セッション**: 暗号化されたセッション管理
4. **CSRF**: トークンベースのCSRF保護

### 認可システム

- **Middleware**: ルートレベルでのアクセス制御
- **Policies**: モデルレベルでの権限チェック
- **Gates**: カスタム認可ロジック

### データ保護

- **暗号化**: パスワード、セッション
- **バリデーション**: 入力データの検証
- **サニタイゼーション**: XSS対策
- **Prepared Statements**: SQLインジェクション対策

## 📊 パフォーマンス最適化

### バックエンド最適化

- **Eloquent Eager Loading**: N+1問題の回避
- **Query Optimization**: インデックス活用
- **Redis Caching**: データキャッシング
- **Queue Jobs**: 非同期処理

### フロントエンド最適化

- **Code Splitting**: Viteによる自動分割
- **Tree Shaking**: 未使用コードの除去
- **Lazy Loading**: 遅延ロード
- **React Compiler**: 自動メモ化

### アセット最適化

- **Vite Build**: 最適化されたプロダクションビルド
- **CSS Purging**: Tailwind CSS未使用クラスの削除
- **Image Optimization**: 画像の最適化
- **CDN**: 静的アセットの配信（オプション）

## 🧪 テスト戦略

### バックエンドテスト（Pest）

- **Feature Tests**: エンドツーエンドテスト
- **Unit Tests**: 単体テスト
- **Database Tests**: データベーストランザクション

### フロントエンドテスト

- **Type Checking**: TypeScript型チェック
- **ESLint**: コード品質チェック
- **Prettier**: コードフォーマットチェック

## 🚀 スケーラビリティ

### 水平スケーリング

- **Load Balancer**: 複数アプリケーションサーバー
- **Session Storage**: Redisによる共有セッション
- **Database Replication**: PostgreSQLレプリケーション

### 垂直スケーリング

- **Server Resources**: CPU/メモリの増強
- **Database Optimization**: クエリ最適化、インデックス
- **Caching**: より多くのキャッシュ層

## 🔧 開発ツール

### Laravel専用ツール

- **Laravel Pail**: リアルタイムログビューア
- **Laravel Tinker**: REPLインターフェース
- **Laravel Boost**: 開発者体験向上ツール
- **Laravel Pint**: コード整形ツール

### TypeScript/React開発ツール

- **Vite**: 高速ビルドツール
- **ESLint**: 静的解析ツール
- **Prettier**: コードフォーマッター
- **TypeScript**: 型チェッカー

## 📦 依存関係管理

### バックエンド（Composer）
- PHP 8.2以上
- Laravel 12
- Laravel Fortify
- Laravel Wayfinder
- Pest v4

### フロントエンド（npm）
- React 19
- TypeScript 5.7
- Inertia.js v2
- Radix UI
- Tailwind CSS 4
- Vite 7

## 🌐 デプロイアーキテクチャ

### 推奨デプロイ構成

```
┌─────────────────────────────────────────────┐
│            Load Balancer (Nginx)            │
└──────────┬──────────────────────┬───────────┘
           │                      │
┌──────────▼──────────┐  ┌────────▼──────────┐
│  App Server 1       │  │  App Server 2     │
│  (Laravel + React)  │  │  (Laravel + React)│
└──────────┬──────────┘  └────────┬──────────┘
           │                      │
           └──────────┬───────────┘
                      │
         ┌────────────┼────────────┐
         │            │            │
┌────────▼──┐  ┌──────▼─────┐  ┌──▼──────────┐
│PostgreSQL │  │   Redis    │  │ Meilisearch │
│(Primary)  │  │            │  │             │
└───────────┘  └────────────┘  └─────────────┘
```

このアーキテクチャにより、高可用性、スケーラビリティ、保守性を実現しています。
