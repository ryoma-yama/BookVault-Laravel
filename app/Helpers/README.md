# BookVault Helpers

このディレクトリには、BookVaultアプリケーションで使用される補助的なヘルパークラスが含まれています。

## MarkdownHelper

Markdown形式のテキストをHTMLに変換するヘルパークラスです。セキュリティを考慮した実装となっています。

### 機能

- Markdown → HTML変換
- 安全でないHTMLタグのエスケープ
- XSS攻撃の防止

### 使用例

```php
use App\Helpers\MarkdownHelper;

$markdown = '# タイトル

これは**太字**のテキストです。';

$html = MarkdownHelper::toHtml($markdown);
// 出力: <h1>タイトル</h1><p>これは<strong>太字</strong>のテキストです。</p>
```

### セキュリティ

- `html_input` は `escape` に設定されており、生のHTMLタグはエスケープされます
- `<script>`, `<iframe>`, `<object>`, `<embed>` タグは許可されません
- 安全でないリンクは無効化されます

## GoogleBooksHelper

Google Books APIの画像URLを生成するヘルパークラスです。

### 機能

- Google Books volume IDから書籍カバー画像のURLを生成

### 使用例

```php
use App\Helpers\GoogleBooksHelper;

$googleId = 'abc123xyz';
$imageUrl = GoogleBooksHelper::getCoverUrl($googleId);
// 出力: https://books.google.com/books/content?id=abc123xyz&printsec=frontcover&img=1&zoom=1&source=gbs_api
```

### 画像ポリシー

BookVaultでは、書籍の表紙画像を以下の方針で扱います：

1. **アップロード不要**: 画像ファイルのアップロード機能は実装しません
2. **Google Books API利用**: Google Books APIが提供する画像URLを直接使用します
3. **ストレージ削減**: ローカルストレージやクラウドストレージへの画像保存は行いません
4. **最新情報**: Google Booksから常に最新の書籍情報と画像を取得できます

#### 画像URL形式

```
https://books.google.com/books/content?id={volume_id}&printsec=frontcover&img=1&zoom=1&source=gbs_api
```

パラメータ：
- `id`: Google Books volume ID
- `printsec`: frontcover (表紙)
- `img`: 1 (画像タイプ)
- `zoom`: 1 (中サイズ)
- `source`: gbs_api (Google Books API経由)

## テスト

すべてのヘルパークラスにはユニットテストが用意されています：

```bash
# ヘルパーのテストを実行
php artisan test --filter="MarkdownHelper|GoogleBooksHelper"
```
