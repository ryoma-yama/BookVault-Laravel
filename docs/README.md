# BookVault-Laravel ドキュメント

BookVault-Laravelは、図書館管理システムを実装したモダンなWebアプリケーションです。Laravel 12とInertia.js + Reactを使用して構築されています。

## 📚 ドキュメント目次

### [機能一覧](./features.md)
システムが提供する主要な機能について説明しています。
- 書籍管理
- 貸出・予約管理
- ユーザー管理
- レビュー機能
- 検索機能

### [アーキテクチャ](./architecture.md)
システムの技術構成とアーキテクチャについて説明しています。
- 技術スタック
- システム構成
- データベース設計
- ディレクトリ構造

### [開発ガイド](./development.md)
開発環境のセットアップと開発手順について説明しています。
- 環境構築
- 開発サーバーの起動
- テストの実行
- コーディング規約

### [デプロイ手順](./deployment.md)
本番環境へのデプロイ方法について説明しています。
- 環境準備
- デプロイ手順
- 環境変数の設定
- トラブルシューティング

## 🚀 クイックスタート

1. **リポジトリのクローン**
   ```bash
   git clone <repository-url>
   cd BookVault-Laravel
   ```

2. **Dev Containerで開く**
   - VS Codeでプロジェクトを開く
   - コマンドパレット（F1）を開く
   - "Dev Containers: Reopen in Container"を選択

3. **環境のセットアップ**
   ```bash
   composer run setup
   ```

4. **開発サーバーの起動**
   ```bash
   composer run dev
   ```

詳細は[開発ガイド](./development.md)を参照してください。

## 🔗 関連リンク

- [Laravel ドキュメント](https://laravel.com/docs)
- [Inertia.js ドキュメント](https://inertiajs.com/)
- [React ドキュメント](https://react.dev/)
- [Pest ドキュメント](https://pestphp.com/)

## 📝 ライセンス

MIT License
