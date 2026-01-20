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
4. 執行 `php artisan serve --port=8111`

### Docker (推薦)
1. 在根目錄執行 `docker-compose up -d`
2. 後端 API 將運行於 `http://localhost:8111` (透過 Nginx)
3. 前端界面將運行於 `http://localhost:3111`

## 功能特性
- **任務點數統計**: 透過互動式圖表與矩陣表格追蹤團隊表現與每月貢獻。
- **Markdown 支援**: 全面支援 Markdown 語法於備註、對話紀錄與產出連結中，並提供自動網址轉換功能。
- **Excel 任務匯入**: 批次匯入任務，並能自動掃描 Excel 單元格內的超連結，將其轉為 Markdown 格式。
- **響應式設計**: 在桌上型電腦、平板與手機上皆能完整運行。包括 **審核工作流**，完成的任務會提交給審核者進行覆核。支援透過點擊標題查看**詳細資料與編輯**。
- **任務追蹤**: 詳細的任務管理，包含狀態與優先級追蹤。支援快速點擊變更執行人員。包含**審核流程**，完成的任務將自動送交審核者審查，且「已完成」清單僅會顯示結案（已通過）之任務，自動過濾送審中資料以避免混淆。
- **多國語系支援**: 內建英文與繁體中文（i18n）支援。
- **自動化部署**: 提供 `deploy.sh` 腳本，支援一鍵打包、上傳、環境維護（Composer、Migration、權限修正）。
- **載入/搜尋優化**: 實作 Skeleton Loader 骨架屏提升載入體驗，並在搜尋功能中加入 Debounce 降低伺服器負擔。

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
