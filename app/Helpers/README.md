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

## テスト

すべてのヘルパークラスにはユニットテストが用意されています：

```bash
# ヘルパーのテストを実行
php artisan test --filter="MarkdownHelper"
```
