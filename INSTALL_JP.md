# Minecraft TPS Monitor - インストールガイド

このガイドでは、Pterodactyl PanelにMinecraft TPS Monitorアドオンをインストールする詳細な手順を説明します。

## インストール前のチェックリスト

インストールを開始する前に、以下の項目を確認してください：

- [ ] サーバーへのrootまたはsudoアクセス権限
- [ ] Pterodactyl Panel v1.11.0以上がインストール済み
- [ ] PHP 8.1以上
- [ ] Composerがインストール済み
- [ ] Node.js 16+とnpmがインストール済み
- [ ] データベースアクセス権限（MySQL/MariaDB/PostgreSQL）
- [ ] Webサーバー（Nginx/Apache）が設定済み

## インストール方法

### 方法1: 手動インストール（推奨）

#### ステップ1: パネルのバックアップ

**⚠️ 重要: アドオンをインストールする前に必ずパネルをバックアップしてください！**

```bash
# データベースのバックアップ
mysqldump -u root -p panel > panel_backup_$(date +%Y%m%d_%H%M%S).sql

# パネルファイルのバックアップ
tar -czf pterodactyl_backup_$(date +%Y%m%d_%H%M%S).tar.gz /var/www/pterodactyl
```

#### ステップ2: ダウンロードと展開

```bash
# pterodactylディレクトリに移動
cd /var/www/pterodactyl

# アドオンをダウンロード
# 注意: 以下のURLは例です。実際のリリースURLまたはzipファイルのパスに置き換えてください
# wget https://github.com/GONKUNNNNN/minecraft-tps-monitor/archive/main.zip
# または、ローカルファイルからコピーする場合：
# cp /path/to/minecraft-tps-monitor.zip .

# アドオンを展開
unzip main.zip

# ファイルをパネルディレクトリにコピー
cp -r minecraft-tps-monitor-main/panelfiles/* .

# 一時ファイルを削除
rm -rf main.zip minecraft-tps-monitor-main
```

#### ステップ3: PHP依存関係のインストール

```bash
# composer依存関係をインストール/更新
composer install --no-dev --optimize-autoloader

# 問題が発生した場合は以下を試してください：
composer update --no-dev --optimize-autoloader
```

#### ステップ4: Node.js依存関係のインストール

```bash
# npm依存関係をインストール
npm install

# 権限の問題が発生した場合：
sudo npm install --unsafe-perm=true --allow-root
```

#### ステップ5: データベースマイグレーション

```bash
# データベースマイグレーションを実行
php artisan migrate

# マイグレーションが失敗した場合は、.envでデータベース接続を確認してから再試行：
php artisan migrate --force
```

#### ステップ6: サービスプロバイダーの登録

`config/app.php`を編集してサービスプロバイダーを追加：

```php
'providers' => [
    // ... 既存のプロバイダー ...
    
    /*
     * TPS Monitor Service Provider
     */
    Pterodactyl\Providers\TpsMonitorServiceProvider::class,
],
```

#### ステップ7: 設定の公開

```bash
# 設定ファイルを公開
php artisan vendor:publish --tag=tps-monitor-config

# 必要に応じて設定を編集
nano config/tps-monitor.php
```

#### ステップ8: 環境設定

`.env`ファイルに以下の変数を追加：

```env
# TPS Monitor設定
TPS_MONITOR_RETENTION_DAYS=30
TPS_MONITOR_AUTO_CLEANUP=true
TPS_MONITOR_COLLECTION_INTERVAL=60
TPS_MONITOR_AUTO_COLLECT=false
TPS_MONITOR_ALERTS_ENABLED=false
TPS_MONITOR_DEFAULT_CHART_HOURS=24
TPS_MONITOR_AUTO_REFRESH_INTERVAL=30
TPS_MONITOR_DEBUG=false
```

#### ステップ9: フロントエンドアセットのビルド

```bash
# 本番用アセットをビルド
npm run build:production

# 開発環境の場合：
# npm run build
```

#### ステップ10: キャッシュのクリア

```bash
# 全てのキャッシュをクリア
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# 本番環境用に最適化
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### ステップ11: 権限の設定

```bash
# 所有権を設定
chown -R www-data:www-data /var/www/pterodactyl

# 権限を設定
chmod -R 755 /var/www/pterodactyl
chmod -R 775 /var/www/pterodactyl/storage
chmod -R 775 /var/www/pterodactyl/bootstrap/cache
```

#### ステップ12: サービスの再起動

```bash
# Webサーバーを再起動
sudo systemctl restart nginx
# またはApacheの場合：
# sudo systemctl restart apache2

# PHP-FPMを再起動（使用している場合）
sudo systemctl restart php8.1-fpm

# キューワーカーを再起動（使用している場合）
sudo systemctl restart pteroq
```

### 方法2: 自動インストールスクリプト

**⚠️ 自己責任で使用してください。実行前にスクリプトを必ず確認してください。**

```bash
# インストールスクリプトをダウンロードして実行
# インストールスクリプトをダウンロード（実際のURLに置き換えてください）
# wget https://raw.githubusercontent.com/GONKUNNNNN/minecraft-tps-monitor/main/install.sh
chmod +x install.sh
sudo ./install.sh
```

## インストール後の設定

### 1. 管理パネルの設定

1. Pterodactyl管理パネルにログイン
2. **管理** → **TPS Monitor**に移動
3. デフォルト設定を構成
4. ダッシュボード機能をテスト

### 2. サーバー設定

1. パネル内のMinecraftサーバーに移動
2. **TPS Monitor**タブを確認
3. コンポーネントが正しく読み込まれることを確認
4. TPSデータ収集をテスト

### 3. APIテスト

APIエンドポイントをテスト：

```bash
# {server_uuid}を実際のサーバーUUIDに置き換え
# {api_token}をAPIトークンに置き換え

curl -H "Authorization: Bearer {api_token}" \
     -H "Accept: application/json" \
     "https://your-panel.com/api/client/servers/{server_uuid}/tps/current"
```

## インストール確認

### インストール状況の確認

```bash
# マイグレーションが正常に実行されたかを確認
php artisan migrate:status | grep tps_monitor

# サービスプロバイダーが登録されているかを確認
php artisan route:list | grep tps

# アセットがビルドされているかを確認
ls -la public/assets/ | grep tps
```

### 機能テスト

1. **管理ダッシュボード**: `/admin/tps`にアクセスしてダッシュボードが読み込まれることを確認
2. **サーバーパネル**: 任意のMinecraftサーバーでTPS Monitorタブを確認
3. **APIエンドポイント**: API呼び出しが適切なレスポンスを返すことをテスト
4. **データベース**: `tps_monitor`テーブルが存在し、アクセス可能であることを確認

## トラブルシューティング

### よくあるインストール問題

#### 問題: マイグレーションが失敗する

```bash
# データベース接続を確認
php artisan tinker
>>> DB::connection()->getPdo();

# 接続が正常な場合、以下を試行：
php artisan migrate:refresh
php artisan migrate --path=database/migrations/2024_01_01_000000_create_tps_monitor_table.php
```

#### 問題: アセットがビルドされない

```bash
# npmキャッシュをクリア
npm cache clean --force

# node_modulesを削除して再インストール
rm -rf node_modules package-lock.json
npm install

# 再度ビルドを試行
npm run build:production
```

#### 問題: サービスプロバイダーが読み込まれない

```bash
# プロバイダーがconfig/app.phpにあるかを確認
grep -n "TpsMonitorServiceProvider" config/app.php

# 設定キャッシュをクリア
php artisan config:clear
php artisan config:cache
```

#### 問題: 権限が拒否される

```bash
# 所有権と権限を修正
sudo chown -R www-data:www-data /var/www/pterodactyl
sudo chmod -R 755 /var/www/pterodactyl
sudo chmod -R 775 /var/www/pterodactyl/storage
sudo chmod -R 775 /var/www/pterodactyl/bootstrap/cache
```

#### 問題: 500内部サーバーエラー

```bash
# エラーログを確認
tail -f /var/log/nginx/error.log
# または
tail -f /var/log/apache2/error.log

# Laravelログを確認
tail -f storage/logs/laravel.log

# 一時的にデバッグモードを有効化
# .envファイルで：
APP_DEBUG=true
```

### ヘルプの取得

問題が発生した場合：

1. READMEの[トラブルシューティングセクション](README.md#troubleshooting)を確認
2. [既存の問題](https://github.com/GONKUNNNNN/minecraft-tps-monitor/issues)を検索（リポジトリが利用可能な場合）
3. 以下の情報を含めて新しい問題を作成：
   - Pterodactylのバージョン
   - PHPバージョン
   - エラーメッセージ
   - 実行したインストール手順

## アンインストール

アドオンを削除する必要がある場合：

```bash
# データベーステーブルを削除
php artisan migrate:rollback --path=database/migrations/2024_01_01_000000_create_tps_monitor_table.php

# config/app.phpからサービスプロバイダーを削除
# 設定ファイルを削除
rm config/tps-monitor.php

# アドオンファイルを削除（注意深く！）
# これは手動プロセスです - TPS monitor関連ファイルのみを削除してください

# キャッシュをクリア
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# アセットを再ビルド
npm run build:production
```

## セキュリティ考慮事項

- インストール前に必ずバックアップを取る
- パネルを最新の状態に保つ
- 強力なデータベースパスワードを使用
- APIアクセスを適切に制限
- 不審な活動についてログを監視
- 本番環境ではHTTPSを使用

## パフォーマンス最適化

インストール後に検討すべき事項：

- キャッシュ用にRedisを有効化
- バックグラウンドタスク用にキューワーカーを設定
- 適切なデータベースインデックスを設定
- 静的アセット用にCDNを使用
- gzip圧縮を有効化

---

**インストール完了！** 🎉

Minecraft TPS Monitorが使用可能になりました。管理パネルにアクセスして、サーバーのパフォーマンス監視を開始してください。