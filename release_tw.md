# 更新日誌

所有 XTask 專案的重要變更都將記錄在此文件中。

## [2026-01-09 16:50] - 排程與任務功能增強與優化
- **後端 (Backend)**:
  - 建立 `ScheduleMemoController` 以支援排程的留言板式備註。
  - 新增 `POST /api/schedules/{schedule}/memos` 接口。
  - 新增 `POST /api/tasks/{task}/remarks` 接口與 `TaskRemarkController`。
  - 新增 `PUT /api/tasks/{task}` 接口，允許更新任務內容（例如變更執行人員）。
  - 新增 `GET /api/projects` 接口與 `ProjectController` 用於獲取專案列表。
  - 新增 `GET /api/departments` 接口與 `DepartmentController` 用於獲取部門列表。
  - 更新 `ScheduleController@store`，將狀態預設為 `in_progress` ("未執行")。
  - 更新 `TaskController@store`，將 `status` 預設為 `in progress` ("未執行")，`level` 預設為 `1` ("一般")。
  - 更新 `openapi.yaml` 並加入完整測試 (`ScheduleMemoApiTest`, `TaskApiTest`)。
- **Frontend**:
  - 更新 `schedules.vue`，從建立表單中移除「實際開始」、「實際結束」與「狀態」。
  - 更新 `tasks.vue`，從建立表單中移除「狀態」與「類別」，改用後端預設值。
  - **執行人員更新**: 實作點擊即改功能，在任務列表中直接點擊執行人員即可變更指派。
  - **專案與部門**: 將「專案」改為下拉選單，並新增「部門」選單及連動執行人員的自動帶入功能。
  - **留言板**: 將任務「備註」改為互動式留言板組件，支援即時留言。
  - **修正**: 同步 `en.json` 與 `zh-TW.json` 補齊缺失的狀態翻譯。
  - **UI 優化**: 修正任務列表中執行人員資訊的垂直置中對齊問題。

## [2026-01-09 14:15] - 角色權限控制 (RBAC) 系統
- **後端**:
  - 實作 5 種角色：`admin`, `manager`, `task_user`, `executor`, `auditor`。
  - 新增 `User`, `Department`, `Project`, `Schedule`, 與 `Task` 的 Laravel Policies。
  - 更新 `CheckRole` 中間件以支援多角色驗證。
  - 在 Controller 中強制執行「先驗證後校驗」以增進安全性。
  - Seed 預設所有角色的用戶。
- **前端**:
  - 增強 `useAuth` 加入 `hasRole` 與 `can` 權限輔助工具。
  - 實作根據角色的動態 UI 渲染（隱藏/顯示按鈕與導覽項）。
  - 更新 `admin` 中間件以支援多角色授權。

## [2026-01-09 11:45] - 管理介面與功能
- **後端**:
  - **Schema**: 建立 `departments` 與 `projects` 表；在 `users` 表加入角色、狀態等欄位。
  - **API**: 實作用戶、專案、部門的 CRUD 管理接口。
  - **安全性**: 加入角色檢查並阻擋凍結帳戶登入。
  - [修正] 解決重新整理跳轉登入問題。
  - [修正] 修復 Admin 介面 `apiBase` 未定義錯誤。
  - [修正] 補全 `zh-TW` 缺失翻譯。
  - [功能] 實作用戶凍結功能。
  - **測試**: 加入所有 Admin API 的功能測試。
  - **Seeding**: 加入預設部門與專案資料。
- **前端**:
  - **佈局**: 建立帶側邊欄的 `admin` 佈局。
  - **帳號管理**: 實作用戶列表、建立、編輯與凍結功能。
  - **資源管理**: 實作專案與部門管理模組。
  - **i18n**: 完成管理介面的完整中英文翻譯。
  - **UI/UX**: 優化導覽列連結與語系切換位置（移至個人設定頁）。

## [2026-01-08 17:30] - 用戶註冊功能與開關控制
- **後端**: 加入 `ALLOW_REGISTRATION` 控制註冊功能之開關。
- **前端**: 根據後端設定顯示或隱藏註冊連結。
- **功能**: 
  - 實作個人資料頁面 (`/profile`)。
  - 檢視與編輯個人資訊 (姓名、信箱)。
  - 變更密碼功能。
- **修正**: 優化驗證中間件，解決註冊後的狀態同步問題。

## [2026-01-08 17:00] - 後端穩定性與單元測試
- **修正**: 強制 API 路由在未驗證時回傳 JSON。
- **測試**: 加入 Auth, Profile, Schedules, Tasks, Users 的完整功能測試。
- **測試**: 建立所有核心模型的 Factory。
- **文件**: 同步 `openapi.yaml` 與 API 實作。

## [2026-01-08 16:30] - 任務管理整合
- **前端**: 實作響應式 Tasks 頁面。
  - 電腦版表格視圖 (Table View)。
  - 手機版卡片視圖 (Card View)。
  - 新增任務選擇器與表單校驗。
- **後端**: 實作 `TaskController` 及其關係預載。

## [2026-01-08 15:35] - 頁面載入特效
- **UI/UX**: 加入 `NuxtLoadingIndicator` 並實作頁面與佈局間的淡入淡出特效。

## [2026-01-08 15:30] - 排程頁面多國語系支持
- **i18n**: 整合排程頁面所有標籤與狀態的翻譯。

## [2026-01-08 15:15] - 響應式 UI 與 Nuxt 4 升級
- **新功能**: 新增響應式排程頁。
- **資料庫**: 為 `schedules` 加上 `memo` 欄位。
- **遷移**: 將前端專案結構升級至 Nuxt 4 (移動至 `app/`)。

## [2026-01-07] - 初始專案搭建
- **基礎**: Nuxt 與 Laravel 基礎架構搭建。
- **身分驗證**: 基礎用戶登入機制實作。
