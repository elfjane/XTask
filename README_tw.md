# XTask - 專案管理系統

XTask 是一個全方位的專案管理應用程式，設計支援多平台（電腦、平板、手機）。它透過追蹤任務、排程及協作者備註，幫助團隊保持井然有序。

## 技術棧

- **前端**: Nuxt 4, Vue 3, Vanilla CSS
- **後端**: Laravel 12.x, PHP 8.x
- **資料庫**: MySQL/SQLite
- **身分驗證**: Firebase 整合 (計畫中/進行中) + Laravel 內建驗證
- **測試**: PHPUnit, Laravel 功能測試
- **文件**: OpenAPI 3.1.0

## 專案結構

```text
XTask/
├── frontend/    # Nuxt 4 應用程式
│   └── app/     # 核心應用程式原始碼 (頁面、組件等)
└── backend/     # Laravel API
```

## 入門指南

### 前端
1. 進入 `/frontend`
2. 執行 `npm install`
3. 執行 `npm run dev`

### 後端
1. 進入 `/backend`
2. 執行 `composer install`
3. 執行 `php artisan migrate`
4. 執行 `php artisan serve`

## 功能特性
- **用戶註冊**: 可透過環境變數切換的註冊系統。
- **排程管理**: 響應式佈局（電腦版表格、手機版卡片），並整合**留言板**功能供協作者溝通。
- **任務追蹤**: 詳細的任務管理，包含狀態與優先級追蹤。支援快速點擊變更執行人員。
- **多國語系支援**: 內建英文與繁體中文（i18n）支援。

## 環境配置

### 後端 (.env)
```env
ALLOW_REGISTRATION=true  # 設定為 false 以關閉用戶註冊
```

### 前端 (.env)
```env
NUXT_PUBLIC_ALLOW_REGISTRATION=true  # 設定為 false 以隱藏註冊介面
```

**註記**: 如果前端允許註冊但後端不允許，用戶在嘗試註冊時將會看到錯誤訊息。
