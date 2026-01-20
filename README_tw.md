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

### 本地開發
- **前端**: 進入 `/frontend`，執行 `npm install` 與 `npm run dev`。
- **後端**: 進入 `/backend`，執行 `composer install`、`php artisan migrate` 與 `php artisan serve --port=8111`。

### Docker 控制 (推薦)
我們提供了便捷的腳本來切換環境：
- **開發環境 (Dev)**: 執行 `manage_docker.bat dev` (Windows) 或 `./manage_docker.sh dev` (Linux)。
- **生產環境 (Prod)**: 執行 `manage_docker.bat prod` (Windows) 或 `./manage_docker.sh prod` (Linux)。

**手動執行指令 (Linux):**
```bash
# 開發環境
docker compose up -d --build

# 生產環境
docker compose -f docker-compose.prod.yml up -d --build
```

## 自動化部署
透過內建選單實現一鍵上傳至雲端伺服器：
1.  在 `deploy.sh` 中設定您的伺服器連線資訊（IP、帳號、路徑）。
2.  在專案根目錄執行 `deploy.sh` (Linux) 或 `deploy.bat` (Windows)。
- **流程**: 自動編譯前端 -> 打包檔案 -> SSH 上傳 -> 遠端解壓 -> 自動執行後端維護作業 (Migration, Cache 清理)。

## 功能特性與優化
- **效能提升**:
    - **Skeleton Loader**: 實作動態骨架屏，優化資料載入時的視覺體驗。
    - **搜尋防抖 (Debounce)**：針對任務搜尋實作 300ms 延遲發送，有效減輕伺服器負載。
    - **輕量化生產映像檔**: 使用 Docker 多階段編譯，大幅縮減 Production 環境的映像檔體積。
- **任務管理**: 支援整列拖拽排序、級別顏色標示（黃色代表重要、紅色代表優先）、詳細資料編輯彈窗。
- **審核工作流**: 完工任務自動進入審核流程，確保品質，並自動過濾已結案任務。
- **Excel 整合**: 支援批次匯入任務，並能掃描 Excel 單元格內的超連結，自動轉為 Markdown 格式。
- **多國語系**: 完備的英文與繁體中文（i18n）支援。

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
