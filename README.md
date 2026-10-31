# 勤怠管理アプリ

## 概要

Laravelを使用して開発した勤怠管理アプリです。

一般ユーザーは出勤・退勤・休憩の記録、勤怠履歴の確認、勤怠修正申請を行えます。

管理者は従業員の勤怠確認、勤怠情報の修正、修正申請の承認・却下を行えます。

また、メール認証、勤怠レポート、公開APIなどの応用機能も実装しています。

## 使用技術

- PHP 8.5.9（開発環境で使用／composer.jsonの指定は ^8.1）
- Laravel 10.50.3
- Laravel Sail
- Docker
- MySQL 8.4
- Laravel Sanctum（API認証）

## 環境構築

### 1. リポジトリの取得

```bash
git clone https://github.com/shiipuro/attendance-app.git
cd attendance-app
```

### 2. PHPパッケージのインストール

PHPとComposerがインストールされている環境で、以下を実行してください。
Docker Desktopも事前に起動しておいてください。

```bash
composer install
```

### 3. 環境設定ファイルの作成

```bash
cp .env.example .env
```

### 4. Dockerコンテナの起動

```bash
./vendor/bin/sail up -d
```

### 5. アプリケーションキーの生成

```bash
./vendor/bin/sail artisan key:generate
```

### 6. データベースの作成

```bash
./vendor/bin/sail artisan migrate
```

## テスト

自動テストには、通常のアプリ用とは別の `testing` データベースを使用します。

テストを実行する前に、テスト用データベースが作成されていることを確認してください。

```bash
./vendor/bin/sail artisan test
```

※ テストではデータベースの内容が初期化される場合があります。

## テスト用ログイン情報

環境構築後、以下のコマンドでテスト用アカウントを作成できます。

```bash
./vendor/bin/sail artisan db:seed
```

### 一般ユーザー

ログインURL：`/login`

| メールアドレス    | パスワード  |
| ----------------- | ----------- |
| user1@example.com | password123 |
| user2@example.com | password123 |
| user3@example.com | password123 |

### 管理者

ログインURL：`/admin/login`

| メールアドレス    | パスワード  |
| ----------------- | ----------- |
| admin@example.com | password123 |

※ 上記は開発・動作確認用のアカウントです。

※ `php artisan db:seed` 実行時に、テスト用アカウント・勤怠データ・修正申請データが登録されます。
