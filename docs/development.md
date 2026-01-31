# 開発ガイド

BookVault-Laravelの開発環境セットアップと開発手順について説明します。

## 📋 前提条件

### 必須ソフトウェア

- **Docker Desktop**: コンテナ環境
- **Visual Studio Code**: 推奨エディタ
- **Dev Containers拡張機能**: VS Code拡張
- **Git**: バージョン管理

### 推奨環境

- **OS**: Windows 10/11、macOS、Linux
- **メモリ**: 8GB以上（推奨: 16GB）
- **ストレージ**: 10GB以上の空き容量

## 🚀 環境構築

### 1. リポジトリのクローン

```bash
git clone https://github.com/ryoma-yama/BookVault-Laravel.git
cd BookVault-Laravel
```

### 2. Dev Containerで開く

1. VS Codeでプロジェクトを開く
2. コマンドパレット（`F1` または `Ctrl+Shift+P` / `Cmd+Shift+P`）を開く
3. "Dev Containers: Reopen in Container" を選択
4. コンテナのビルドと起動を待つ（初回は10-15分程度）

### 3. 初期セットアップ

Dev Container内で以下のコマンドを実行：

```bash
# 全セットアップを一括実行
composer run setup
```

このコマンドは以下を実行します：
- `composer install`: PHP依存関係のインストール
- `.env`ファイルの作成
- `php artisan key:generate`: アプリケーションキーの生成
- `php artisan migrate`: データベースマイグレーション
- `npm install`: JavaScript依存関係のインストール
- `npm run build`: フロントエンドのビルド

### 4. 環境変数の設定

`.env`ファイルを確認し、必要に応じて設定を変更：

```env
APP_NAME=BookVault
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password

# Google Books API Key（オプション）
GOOGLE_BOOKS_API_KEY=your-api-key-here
```

### 5. データベースのシード（オプション）

テストデータを投入する場合：

```bash
php artisan db:seed
```

## 🖥️ 開発サーバーの起動

### 標準開発モード

すべてのサービスを同時起動：

```bash
composer run dev
```

このコマンドは以下を並列で起動します：
- **Laravel開発サーバー**: http://localhost:8000
- **Vite開発サーバー**: http://localhost:5173
- **Queue Worker**: バックグラウンドジョブ処理
- **Laravel Pail**: リアルタイムログビューア

### 個別起動

必要に応じて個別にサービスを起動：

```bash
# Laravelサーバーのみ
php artisan serve

# Viteのみ
npm run dev

# キューワーカーのみ
php artisan queue:listen

# ログビューアのみ
php artisan pail
```

### SSRモード（サーバーサイドレンダリング）

SSRを使用する場合：

```bash
composer run dev:ssr
```

## 🧪 テストの実行

### すべてのテストを実行

```bash
composer run test
```

または

```bash
php artisan test
```

### コンパクト表示

```bash
php artisan test --compact
```

### 特定のテストファイルを実行

```bash
php artisan test tests/Feature/BookControllerTest.php
```

### 特定のテストケースを実行

```bash
php artisan test --filter test_user_can_view_book_list
```

### カバレッジレポート

```bash
php artisan test --coverage
```

### 並列テスト実行

```bash
php artisan test --parallel
```

## 🎨 コードフォーマットとリンティング

### PHP（Laravel Pint）

#### コード整形

```bash
composer run lint
```

または

```bash
pint
```

#### 整形チェック（CI用）

```bash
composer run test:lint
```

または

```bash
pint --test
```

### TypeScript/React（ESLint & Prettier）

#### ESLint実行と自動修正

```bash
npm run lint
```

#### Prettierでフォーマット

```bash
npm run format
```

#### フォーマットチェック

```bash
npm run format:check
```

#### 型チェック

```bash
npm run types
```

## 📝 コーディング規約

### PHP（PSR-12準拠）

Laravel Pintが自動的にPSR-12スタイルを適用します。

#### 命名規則

- **クラス**: PascalCase（例: `BookController`）
- **メソッド**: camelCase（例: `getUserBooks`）
- **変数**: snake_case（例: `$user_name`）
- **定数**: UPPER_CASE（例: `MAX_BOOKS`）

#### ベストプラクティス

```php
// Good: タイプヒンティングを使用
public function store(StoreBookRequest $request): Book
{
    return Book::create($request->validated());
}

// Good: Eloquentリレーションを活用
$book = Book::with(['authors', 'tags'])->find($id);

// Good: Early return
public function canBorrow(User $user): bool
{
    if ($user->hasOverdueLoans()) {
        return false;
    }
    
    return $user->currentLoansCount() < self::MAX_LOANS;
}
```

### TypeScript/React

#### 命名規則

- **コンポーネント**: PascalCase（例: `BookList`）
- **関数/変数**: camelCase（例: `fetchBooks`）
- **定数**: UPPER_CASE（例: `MAX_BOOKS`）
- **型/インターフェース**: PascalCase（例: `BookProps`）

#### ベストプラクティス

```typescript
// Good: 型定義を明確に
interface BookListProps {
  books: Book[];
  onBookClick: (book: Book) => void;
}

// Good: 関数コンポーネントを使用
export function BookList({ books, onBookClick }: BookListProps) {
  return (
    <div>
      {books.map((book) => (
        <BookCard key={book.id} book={book} onClick={onBookClick} />
      ))}
    </div>
  );
}

// Good: カスタムフックを活用
function useBooks() {
  const [books, setBooks] = useState<Book[]>([]);
  const [loading, setLoading] = useState(false);
  
  // ...
  
  return { books, loading };
}
```

## 🗄️ データベース操作

### マイグレーション

#### 新規マイグレーション作成

```bash
php artisan make:migration create_new_table --create=new_table
```

#### マイグレーション実行

```bash
php artisan migrate
```

#### ロールバック

```bash
php artisan migrate:rollback
```

#### リフレッシュ（全削除して再実行）

```bash
php artisan migrate:fresh
```

#### リフレッシュ＋シード

```bash
php artisan migrate:fresh --seed
```

### シーダー

#### シーダー作成

```bash
php artisan make:seeder BookSeeder
```

#### シーダー実行

```bash
php artisan db:seed
php artisan db:seed --class=BookSeeder
```

### モデル作成

#### モデル＋マイグレーション＋ファクトリー＋シーダー

```bash
php artisan make:model Book -mfs
```

オプション：
- `-m`: マイグレーション
- `-f`: ファクトリー
- `-s`: シーダー
- `-c`: コントローラー
- `-a`: 全部（all）

## 🎯 便利なArtisanコマンド

### コントローラー作成

```bash
# 標準コントローラー
php artisan make:controller BookController

# リソースコントローラー
php artisan make:controller BookController --resource

# APIコントローラー
php artisan make:controller Api/BookController --api
```

### リクエスト作成

```bash
php artisan make:request StoreBookRequest
```

### ポリシー作成

```bash
php artisan make:policy BookPolicy --model=Book
```

### ミドルウェア作成

```bash
php artisan make:middleware EnsureUserIsAdmin
```

### ルート一覧

```bash
php artisan route:list
```

## 🔍 全文検索機能（Laravel Scout + Meilisearch）

このプロジェクトでは、Laravel ScoutとMeilisearchを使用した高速な全文検索機能を実装しています。

### セットアップ

#### 1. 環境変数の設定

`.env`ファイルに以下を設定：

```env
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://meilisearch:7700
MEILISEARCH_KEY=
```

#### 2. インデックスの作成

全ての書籍をMeilisearchにインデックス化：

```bash
php artisan scout:import "App\Models\Book"
```

特定のモデルのインデックスを削除：

```bash
php artisan scout:flush "App\Models\Book"
```

インデックスを再作成（フラッシュ＋インポート）：

```bash
php artisan scout:flush "App\Models\Book"
php artisan scout:import "App\Models\Book"
```

### 検索可能な項目

Bookモデルでは以下の項目が検索対象です：

- **id**: 書籍ID
- **title**: タイトル
- **publisher**: 出版社
- **description**: 説明文
- **isbn_13**: ISBN-13コード
- **authors**: 著者名（リレーションから取得）

### 使用方法

#### コントローラーでの検索

```php
use App\Models\Book;

// 基本的な検索
$books = Book::search('Laravel')->get();

// ページネーション付き検索
$books = Book::search('Laravel')->paginate(15);

// 追加のフィルタリング
$books = Book::search('Laravel')
    ->query(function ($builder) {
        $builder->where('publisher', 'like', '%O\'Reilly%');
    })
    ->paginate(15);
```

#### テスト環境での設定

テストでは`database`ドライバーを使用します（`phpunit.xml`に設定済み）：

```xml
<env name="SCOUT_DRIVER" value="database"/>
```

このドライバーは外部サービス不要で、テスト実行時にデータベースを使用した簡易的な検索を提供します。

### インデックス管理のベストプラクティス

#### 自動インデックス化

モデルを保存・更新すると自動的にインデックスが更新されます：

```php
$book = Book::create([
    'title' => '新しい書籍',
    'description' => '説明文',
]);
// 自動的にMeilisearchにインデックス化されます
```

#### 手動でインデックス化を制御

```php
// インデックス化を一時的に無効化
Book::withoutSyncingToSearch(function () {
    Book::factory()->count(100)->create();
});

// その後まとめてインデックス化
php artisan scout:import "App\Models\Book"
```

#### インデックスのカスタマイズ

モデルの`toSearchableArray()`メソッドで検索対象をカスタマイズ：

```php
public function toSearchableArray(): array
{
    return [
        'id' => $this->id,
        'title' => $this->title,
        'publisher' => $this->publisher,
        'description' => $this->description,
        'isbn_13' => $this->isbn_13,
        'authors' => $this->authors->pluck('name')->implode(', '),
    ];
}
```

### Meilisearchダッシュボード

開発環境では、Meilisearchのダッシュボードにアクセスできます：

- **URL**: http://localhost:7700
- インデックスの状態、検索テスト、設定の確認が可能

### パフォーマンス最適化

#### キューを使用したインデックス化

大量のデータを扱う場合は、キューを使用して非同期でインデックス化：

```env
SCOUT_QUEUE=true
```

```bash
# キューワーカーの起動
php artisan queue:work
```

### トラブルシューティング

#### インデックスが更新されない場合

```bash
# キャッシュをクリア
php artisan cache:clear
php artisan config:clear

# インデックスを再作成
php artisan scout:flush "App\Models\Book"
php artisan scout:import "App\Models\Book"
```

#### Meilisearchに接続できない場合

```bash
# Meilisearchコンテナのステータス確認
docker ps | grep meilisearch

# ログの確認
docker compose logs meilisearch
```



### キャッシュクリア

```bash
# 全キャッシュクリア
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# または一括で
php artisan optimize:clear
```

### キャッシュ最適化（本番用）

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🔧 Laravel Wayfinder（型安全ルーティング）

### Wayfinderルート定義の生成

```bash
php artisan wayfinder:generate
```

このコマンドでTypeScript型定義が自動生成されます。

### 使用例

```typescript
import { route } from '@/routes';

// 型安全なルーティング
const bookUrl = route('books.show', { book: bookId });
```

## 🐛 デバッグ

### Laravel Pail（ログビューア）

```bash
# リアルタイムログ監視
php artisan pail

# フィルター付き
php artisan pail --filter="error"
```

### Tinker（REPL）

```bash
php artisan tinker
```

```php
// Tinker内での実行例
>>> $books = App\Models\Book::all();
>>> $user = App\Models\User::find(1);
>>> $user->loans()->count();
```

### dd/dump

```php
// デバッグ出力して停止
dd($variable);

// デバッグ出力のみ
dump($variable);

// Inertia.jsでの使用
Inertia::render('page', [
    'data' => $data,
])->dump();
```

### Ray（推奨デバッグツール）

```bash
composer require spatie/laravel-ray --dev
```

```php
ray($variable);
ray()->table($array);
```

## 📦 パッケージ管理

### Composerパッケージ

```bash
# インストール
composer require vendor/package

# 開発依存
composer require --dev vendor/package

# アップデート
composer update

# 削除
composer remove vendor/package
```

### NPMパッケージ

```bash
# インストール
npm install package-name

# 開発依存
npm install --save-dev package-name

# アップデート
npm update

# 削除
npm uninstall package-name
```

## 🔍 検索機能（Meilisearch）

### インデックス作成

```bash
php artisan scout:import "App\Models\Book"
```

### インデックス削除

```bash
php artisan scout:flush "App\Models\Book"
```

### Meilisearchダッシュボード

ブラウザで http://localhost:7700 にアクセス

## 🚢 ビルド

### 開発ビルド

```bash
npm run dev
```

### 本番ビルド

```bash
npm run build
```

### SSRビルド

```bash
npm run build:ssr
```

## 🔄 Git ワークフロー

### ブランチ戦略

```bash
# feature開発
git checkout -b feature/book-search
# 開発...
git add .
git commit -m "feat: Add book search functionality"
git push origin feature/book-search

# bugfix
git checkout -b bugfix/fix-loan-date
# 修正...
git commit -m "fix: Fix loan date calculation"
git push origin bugfix/fix-loan-date
```

### コミットメッセージ規約

Conventional Commitsに準拠：

```
feat: 新機能
fix: バグ修正
docs: ドキュメント変更
style: コードスタイル変更（機能に影響なし）
refactor: リファクタリング
test: テスト追加・修正
chore: ビルドプロセスやツールの変更
```

例：
```bash
git commit -m "feat: Add book reservation feature"
git commit -m "fix: Fix user authentication issue"
git commit -m "docs: Update API documentation"
```

## 🛠️ トラブルシューティング

### コンテナが起動しない

```bash
# コンテナを再ビルド
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Composerの依存関係エラー

```bash
composer install --ignore-platform-reqs
```

### Node modulesのエラー

```bash
rm -rf node_modules package-lock.json
npm install
```

### データベース接続エラー

```bash
# マイグレーションをリセット
php artisan migrate:fresh

# データベースの状態確認
php artisan db:show
```

### キャッシュ関連の問題

```bash
# 全キャッシュをクリア
php artisan optimize:clear

# Node関連キャッシュもクリア
npm run build
```

### パーミッションエラー

```bash
# storage/bootstrapディレクトリの権限修正
chmod -R 775 storage bootstrap/cache
```

## 📚 参考リソース

### 公式ドキュメント

- [Laravel Documentation](https://laravel.com/docs)
- [Inertia.js Documentation](https://inertiajs.com/)
- [React Documentation](https://react.dev/)
- [TypeScript Documentation](https://www.typescriptlang.org/docs/)
- [Pest Documentation](https://pestphp.com/)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)

### Laravel関連

- [Laravel Fortify](https://laravel.com/docs/fortify)
- [Laravel Wayfinder](https://github.com/laravel/wayfinder)
- [Laravel Pint](https://laravel.com/docs/pint)
- [Laravel Pail](https://laravel.com/docs/pail)

### 開発ツール

- [Radix UI](https://www.radix-ui.com/)
- [Lucide Icons](https://lucide.dev/)
- [Meilisearch](https://www.meilisearch.com/docs)

## 💡 ベストプラクティス

### パフォーマンス

1. **Eager Loading**: N+1問題を避ける
   ```php
   $books = Book::with(['authors', 'tags'])->get();
   ```

2. **Indexing**: 検索が多いカラムにインデックス
   ```php
   $table->index('isbn');
   ```

3. **Caching**: 頻繁にアクセスされるデータをキャッシュ
   ```php
   Cache::remember('books', 3600, fn() => Book::all());
   ```

### セキュリティ

1. **バリデーション**: 常に入力を検証
   ```php
   $validated = $request->validate([
       'title' => 'required|max:255',
       'isbn' => 'required|unique:books',
   ]);
   ```

2. **認可**: ポリシーを使用
   ```php
   $this->authorize('update', $book);
   ```

3. **Mass Assignment保護**
   ```php
   protected $fillable = ['title', 'isbn'];
   protected $guarded = ['id'];
   ```

### コード品質

1. **コミット前チェックリスト**
   - [ ] テストが通る
   - [ ] Lintエラーがない
   - [ ] 型チェックが通る
   - [ ] 不要なコメント/console.logを削除

2. **プルリクエスト前**
   - [ ] 最新のmainブランチをマージ
   - [ ] すべてのテストが通る
   - [ ] ビルドが成功する
   - [ ] 変更内容を説明するドキュメント更新

これで開発環境のセットアップと基本的な開発フローの準備が整いました！
