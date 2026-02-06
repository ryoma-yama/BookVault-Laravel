# Database Setup

このドキュメントでは、BookVaultのデータベース設定とシーディングについて説明します。

## マイグレーション

### Books テーブル

書籍情報を格納するテーブルです。

```bash
php artisan migrate
```

#### スキーマ

| カラム名 | 型 | 説明 |
|---------|------|------|
| id | bigint | 主キー |
| google_id | string | Google Books volume ID (ユニーク) |
| isbn_13 | string | ISBN-13コード (nullable) |
| title | string | 書籍タイトル |
| publisher | string | 出版社 (nullable) |
| published_date | string | 出版日 (nullable) |
| description | text | 書籍の説明 (nullable) |
| created_at | timestamp | 作成日時 |
| updated_at | timestamp | 更新日時 |

## Factory

テストやシーディング用のダミーデータを生成します。

### BookFactory

```php
use App\Models\Book;

// 1冊の書籍を作成
$book = Book::factory()->create();

// 複数の書籍を作成
$books = Book::factory()->count(10)->create();

// 特定の属性を指定して作成
$book = Book::factory()->create([
    'title' => 'マイ・ブック',
    'publisher' => 'サンプル出版',
]);
```

## Seeder

### DatabaseSeeder

データベースに初期データを投入します。

```bash
# マイグレーション実行後にシーディング
php artisan db:seed

# マイグレーションとシーディングを一度に実行
php artisan migrate:fresh --seed
```

#### 生成されるデータ

- テストユーザー: 1名 (test@example.com)
- 追加ユーザー: 5名
- 書籍: 40冊

### カスタムシーディング

必要に応じてデータ量を調整できます：

```php
// database/seeders/DatabaseSeeder.php
Book::factory(100)->create(); // 100冊の書籍を作成
```

## テスト環境

テストではインメモリSQLiteデータベースを使用します：

```xml
<!-- phpunit.xml -->
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

これにより、テスト実行ごとに新しいデータベースが作成され、テスト終了後に自動的に破棄されます。
