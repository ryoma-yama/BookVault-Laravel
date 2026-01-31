# デプロイ手順

BookVault-Laravelを本番環境にデプロイする手順について説明します。

## 📋 デプロイ前の準備

### システム要件

#### サーバー環境
- **OS**: Ubuntu 22.04 LTS以降（推奨）
- **CPU**: 2コア以上
- **メモリ**: 4GB以上（推奨: 8GB）
- **ストレージ**: 20GB以上

#### ソフトウェア要件
- **PHP**: 8.2以降
- **Node.js**: 18以降
- **PostgreSQL**: 14以降
- **Redis**: 6以降
- **Meilisearch**: 最新版
- **Nginx**: 1.18以降 または **Apache**: 2.4以降
- **Composer**: 2.x
- **npm**: 9以降

### 必要なPHP拡張機能

```bash
sudo apt update
sudo apt install -y \
  php8.2-cli \
  php8.2-fpm \
  php8.2-pgsql \
  php8.2-redis \
  php8.2-mbstring \
  php8.2-xml \
  php8.2-curl \
  php8.2-zip \
  php8.2-bcmath \
  php8.2-intl \
  php8.2-gd
```

## 🚀 デプロイ方法

### 方法1: Docker Compose（推奨）

最も簡単で再現性の高い方法です。

#### 1. 必要なファイルの準備

```bash
# サーバーにログイン
ssh user@your-server.com

# プロジェクトディレクトリ作成
mkdir -p /var/www/bookvault
cd /var/www/bookvault

# リポジトリクローン
git clone https://github.com/ryoma-yama/BookVault-Laravel.git .
```

#### 2. 環境変数の設定

```bash
# .envファイル作成
cp .env.example .env
nano .env
```

本番環境用の設定：

```env
APP_NAME=BookVault
APP_ENV=production
APP_KEY=  # 後で生成
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=bookvault_production
DB_USERNAME=bookvault_user
DB_PASSWORD=<strong-password>

CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=redis
REDIS_PASSWORD=<redis-password>
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email@example.com
MAIL_PASSWORD=<email-password>
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@your-domain.com"
MAIL_FROM_NAME="${APP_NAME}"

GOOGLE_BOOKS_API_KEY=<your-api-key>
```

#### 3. Docker Composeで起動

```bash
# Docker Composeでビルド・起動
docker-compose -f docker-compose.prod.yml up -d --build
```

#### 4. アプリケーションのセットアップ

```bash
# コンテナ内でコマンド実行
docker-compose exec laravel.test bash

# セットアップスクリプト実行
composer run setup

# アプリケーションキー生成（まだの場合）
php artisan key:generate

# 本番環境最適化
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# 検索インデックス作成
php artisan scout:import "App\Models\Book"
```

### 方法2: 手動デプロイ

#### 1. データベースのセットアップ

```bash
# PostgreSQLインストール
sudo apt install postgresql postgresql-contrib

# PostgreSQLユーザーとデータベース作成
sudo -u postgres psql
```

```sql
CREATE DATABASE bookvault_production;
CREATE USER bookvault_user WITH PASSWORD 'your-strong-password';
GRANT ALL PRIVILEGES ON DATABASE bookvault_production TO bookvault_user;
\q
```

#### 2. Redisのセットアップ

```bash
# Redisインストール
sudo apt install redis-server

# Redis設定
sudo nano /etc/redis/redis.conf
# 以下を設定:
# requirepass your-redis-password
# maxmemory 256mb
# maxmemory-policy allkeys-lru

# Redis再起動
sudo systemctl restart redis-server
sudo systemctl enable redis-server
```

#### 3. Meilisearchのセットアップ

```bash
# Meilisearchインストール
curl -L https://install.meilisearch.com | sh

# Systemdサービス作成
sudo nano /etc/systemd/system/meilisearch.service
```

```ini
[Unit]
Description=Meilisearch
After=network.target

[Service]
Type=simple
User=www-data
ExecStart=/usr/local/bin/meilisearch --env production
Restart=on-failure

[Install]
WantedBy=multi-user.target
```

```bash
# Meilisearch起動
sudo systemctl start meilisearch
sudo systemctl enable meilisearch
```

#### 4. アプリケーションのデプロイ

```bash
# アプリケーションディレクトリ
cd /var/www/bookvault

# リポジトリクローン
git clone https://github.com/ryoma-yama/BookVault-Laravel.git .

# 依存関係インストール
composer install --optimize-autoloader --no-dev
npm install
npm run build

# 環境設定
cp .env.example .env
nano .env  # 上記の本番環境設定を入力

# アプリケーション初期化
php artisan key:generate
php artisan migrate --force
php artisan storage:link

# 最適化
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# パーミッション設定
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

#### 5. Nginxの設定

```bash
sudo nano /etc/nginx/sites-available/bookvault
```

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com;
    root /var/www/bookvault/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
# サイト有効化
sudo ln -s /etc/nginx/sites-available/bookvault /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

#### 6. SSL証明書（Let's Encrypt）

```bash
# Certbot インストール
sudo apt install certbot python3-certbot-nginx

# SSL証明書取得
sudo certbot --nginx -d your-domain.com

# 自動更新設定確認
sudo systemctl status certbot.timer
```

#### 7. キューワーカーの設定

```bash
sudo nano /etc/systemd/system/bookvault-queue.service
```

```ini
[Unit]
Description=BookVault Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/bookvault
ExecStart=/usr/bin/php /var/www/bookvault/artisan queue:work --sleep=3 --tries=3 --max-time=3600
Restart=on-failure

[Install]
WantedBy=multi-user.target
```

```bash
# サービス起動
sudo systemctl start bookvault-queue
sudo systemctl enable bookvault-queue
```

#### 8. スケジューラー設定（Cron）

```bash
sudo crontab -e -u www-data
```

以下を追加：

```
* * * * * cd /var/www/bookvault && php artisan schedule:run >> /dev/null 2>&1
```

## 🔧 環境変数の詳細設定

### 必須環境変数

```env
# アプリケーション基本設定
APP_NAME=BookVault
APP_ENV=production
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxx
APP_DEBUG=false
APP_URL=https://your-domain.com

# データベース
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=bookvault_production
DB_USERNAME=bookvault_user
DB_PASSWORD=<strong-db-password>

# キャッシュ・セッション
CACHE_STORE=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_ENCRYPT=true

# キュー
QUEUE_CONNECTION=redis

# Redis
REDIS_HOST=localhost
REDIS_PASSWORD=<strong-redis-password>
REDIS_PORT=6379
```

### メール設定

#### Gmail SMTP（例）

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@your-domain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

#### AWS SES（例）

```env
MAIL_MAILER=ses
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=us-east-1
MAIL_FROM_ADDRESS="noreply@your-domain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### オプション設定

```env
# ログ
LOG_CHANNEL=stack
LOG_LEVEL=error
LOG_STACK=daily

# ファイルストレージ（S3使用時）
FILESYSTEM_DISK=s3
AWS_BUCKET=your-bucket-name
AWS_USE_PATH_STYLE_ENDPOINT=false

# Meilisearch
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://localhost:7700
MEILISEARCH_KEY=your-master-key
```

## 🔄 デプロイ後の更新手順

### アプリケーションの更新

```bash
# メンテナンスモード開始
php artisan down

# 最新コードを取得
git pull origin main

# 依存関係更新
composer install --optimize-autoloader --no-dev
npm install
npm run build

# データベースマイグレーション
php artisan migrate --force

# キャッシュクリア＆再生成
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# 検索インデックス更新
php artisan scout:import "App\Models\Book"

# メンテナンスモード終了
php artisan up
```

### ゼロダウンタイムデプロイ

Laravel Envoyerまたはデプロイスクリプトを使用：

```bash
#!/bin/bash
# deploy.sh

set -e

echo "🚀 Starting deployment..."

# 新しいリリースディレクトリ作成
RELEASE_DIR="/var/www/bookvault/releases/$(date +%Y%m%d%H%M%S)"
mkdir -p $RELEASE_DIR

# コードをクローン
git clone --depth 1 https://github.com/ryoma-yama/BookVault-Laravel.git $RELEASE_DIR

cd $RELEASE_DIR

# 依存関係インストール
composer install --optimize-autoloader --no-dev
npm install
npm run build

# 共有ファイルのシンボリックリンク
ln -s /var/www/bookvault/shared/.env .env
ln -s /var/www/bookvault/shared/storage storage

# 最適化
php artisan config:cache
php artisan route:cache
php artisan view:cache

# マイグレーション
php artisan migrate --force

# currentシンボリックリンクを更新
ln -nfs $RELEASE_DIR /var/www/bookvault/current

# サービス再起動
sudo systemctl reload php8.2-fpm
sudo systemctl restart bookvault-queue

# 古いリリースを削除（最新5つを保持）
cd /var/www/bookvault/releases
ls -t | tail -n +6 | xargs rm -rf

echo "✅ Deployment completed successfully!"
```

## 📊 監視とログ

### アプリケーションログ

```bash
# リアルタイムログ監視
tail -f storage/logs/laravel.log

# エラーログのみ
tail -f storage/logs/laravel.log | grep ERROR
```

### システム監視

#### Nginxログ

```bash
# アクセスログ
tail -f /var/log/nginx/access.log

# エラーログ
tail -f /var/log/nginx/error.log
```

#### システムリソース

```bash
# CPU/メモリ使用量
htop

# ディスク使用量
df -h

# プロセス確認
ps aux | grep php
ps aux | grep nginx
```

### パフォーマンス監視ツール（推奨）

- **Laravel Telescope**: 開発/ステージング環境用
- **New Relic**: APM監視
- **Sentry**: エラートラッキング
- **Laravel Horizon**: Redisキュー監視（推奨）

#### Laravel Horizonのインストール

```bash
composer require laravel/horizon
php artisan horizon:install
php artisan migrate
```

Supervisorで起動：

```bash
sudo nano /etc/supervisor/conf.d/horizon.conf
```

```ini
[program:horizon]
process_name=%(program_name)s
command=php /var/www/bookvault/artisan horizon
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/bookvault/storage/logs/horizon.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start horizon
```

## 🔒 セキュリティ対策

### ファイアウォール設定

```bash
# UFW有効化
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### fail2ban設定

```bash
# fail2banインストール
sudo apt install fail2ban

# 設定
sudo nano /etc/fail2ban/jail.local
```

```ini
[sshd]
enabled = true
maxretry = 3

[nginx-http-auth]
enabled = true
```

```bash
sudo systemctl restart fail2ban
```

### セキュリティヘッダー（Nginx）

```nginx
# /etc/nginx/sites-available/bookvault に追加
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "no-referrer-when-downgrade" always;
add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
```

### データベースバックアップ

自動バックアップスクリプト：

```bash
#!/bin/bash
# /usr/local/bin/backup-db.sh

BACKUP_DIR="/var/backups/bookvault"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="bookvault_production"
DB_USER="bookvault_user"

mkdir -p $BACKUP_DIR

# PostgreSQLバックアップ
PGPASSWORD=$DB_PASSWORD pg_dump -U $DB_USER -h localhost $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# 7日より古いバックアップを削除
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +7 -delete

echo "Backup completed: db_$DATE.sql.gz"
```

```bash
# 実行権限付与
sudo chmod +x /usr/local/bin/backup-db.sh

# Cron設定（毎日午前2時）
sudo crontab -e
```

```
0 2 * * * /usr/local/bin/backup-db.sh >> /var/log/bookvault-backup.log 2>&1
```

## 🐛 トラブルシューティング

### 500 Internal Server Error

```bash
# ログ確認
tail -f storage/logs/laravel.log
tail -f /var/log/nginx/error.log

# パーミッション修正
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# キャッシュクリア
php artisan optimize:clear
```

### データベース接続エラー

```bash
# PostgreSQL接続確認
psql -U bookvault_user -d bookvault_production -h localhost

# .env設定確認
cat .env | grep DB_

# PostgreSQLログ確認
sudo tail -f /var/log/postgresql/postgresql-14-main.log
```

### キューが動作しない

```bash
# キューワーカー状態確認
sudo systemctl status bookvault-queue

# 再起動
sudo systemctl restart bookvault-queue

# ログ確認
journalctl -u bookvault-queue -f
```

### Meilisearch接続エラー

```bash
# Meilisearch状態確認
sudo systemctl status meilisearch

# 接続テスト
curl http://localhost:7700/health

# ログ確認
journalctl -u meilisearch -f
```

### メモリ不足

```bash
# スワップ領域追加
sudo fallocate -l 2G /swapfile
sudo chmod 600 /swapfile
sudo mkswap /swapfile
sudo swapon /swapfile

# 永続化
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
```

## 📈 スケーリング戦略

### 垂直スケーリング

1. サーバースペックの増強
2. PHP-FPMワーカー数の増加
3. データベース最適化

### 水平スケーリング

1. **ロードバランサー導入**
   - Nginx/HAProxy
   - AWS ELB/ALB

2. **複数アプリケーションサーバー**
   - セッションをRedisで共有
   - ファイルストレージをS3へ

3. **データベースレプリケーション**
   - PostgreSQL レプリケーション
   - 読み取り専用レプリカ

4. **CDN導入**
   - 静的アセット配信
   - CloudFlare/AWS CloudFront

## 🎯 本番環境チェックリスト

デプロイ前に必ず確認：

- [ ] `APP_DEBUG=false` になっている
- [ ] `APP_ENV=production` になっている
- [ ] 強力な`APP_KEY`が設定されている
- [ ] データベースパスワードが強力
- [ ] Redis パスワードが設定されている
- [ ] SSL証明書が設定されている
- [ ] ファイアウォールが設定されている
- [ ] バックアップが設定されている
- [ ] 監視ツールが設定されている
- [ ] エラートラッキングが設定されている
- [ ] ログローテーションが設定されている
- [ ] キューワーカーが起動している
- [ ] Cronジョブが設定されている
- [ ] パーミッションが正しい

デプロイ成功おめでとうございます！🎉
