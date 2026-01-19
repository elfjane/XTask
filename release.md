# Release Notes

All notable changes to the XTask project will be documented in this file.

## [2026-01-19] - Completed Tasks Management Enhancement
- **UI/UX Optimization**:
  - Renamed "Category" to "Level" (級別) across the task management module for better clarity.
  - **Visual Enhancement**: The "Level" column in the task table now features dynamic background colors: Level 2 (Important) is highlighted in **Yellow**, and Level 3 (Priority) in **Red**.
  - Added the "Level" field to the Task Creation modal, allowing users to set priority levels when creating new tasks.
  - Updated sorting labels to reflect the new "Level" terminology.
- **New Feature**: 
  - **Drag-and-Drop Reordering**: Users can now manually reorder tasks by dragging any non-interactive area of a task row up or down in the list. The custom order is preserved across sessions.
  - Administrators and Auditors can now revert completed or failed tasks back to "Unsubmitted" and "Working" status exclusively from the **Completed Tasks** page details modal.
  - The "Edit" and "Revert" buttons for finalized tasks are now restricted to authorized users and only appear when viewing the Completed Tasks list to maintain a clean workflow.
- **Backend Improvements**:
  - Reverted tasks now have their review metadata (timestamps and reviewer ID) cleared automatically.
  - Refined the "Completed Tasks" list to strictly exclude tasks that are "Under Review" (`submitted`).
  - Applied the same logic to task statistics to ensure point calculations only include finalized work.

## [2026-01-14 17:15] - Task Status Quick Switch
- **Frontend Enhancements**:
  - Implemented one-click status switching in the Task list.
  - Users can now click the status badge to open a dropdown and change the task status directly (e.g., Working, In Progress, Idle).
  - Backend integration reuses the robust `PUT /tasks/{task}` interface which handles side-effects like auto-submission for review.
- **Backend Improvements**:
  - Optimized the handling of `AccessDeniedHttpException` to return a clean JSON 403 Forbidden response instead of a verbose debug stack trace.
  - Updated `PUT /tasks/{task}` OpenAPI specification to document the 403 Unauthorized response.

## [2026-01-14 10:50] - Excel Import Status Improvement
- **Backend Improvements**:
  - Enhanced Excel task import to respect the 'Status' (狀態) column.
  - Tasks are now only marked as 'approved' and 'finished' if the Excel status specifically indicates they are completed.
  - Added mapping for multiple status strings (e.g., 已完成, 執行中, 未執行, 待測試, etc.) to ensure data consistency during import.

## [2026-01-14 11:10] - Performance and UI Optimization
- **Performance Improvements**:
  - Added database indexes to frequently used filtering and sorting columns in the tasks table.
  - Optimized Eager Loading logic to only fetch the `latestRemark` in list views, significantly reducing memory usage and API response time.
- **Frontend UI Adjustments**:
  - Reordered columns in the "Completed Tasks" view: "Review Status", "Reviewer", and "Reviewed At" are now moved after the "Memo" column for better readability.
  - Removed test account credentials from the login page.
  - Set default task points to 1.0 and allowed a minimum of 0 points (with 0.5 step increments).

## [2026-01-13 18:25] - Docker Support
- **Infrastructure**:
  - Added `docker-compose.yml` to the root directory for full stack orchestration.
  - Created `backend/Dockerfile` and `backend/.docker/nginx/default.conf` for the Laravel API.
  - Created `frontend/Dockerfile` for the Nuxt 4 application.
  - Integrated MySQL 8.0 as the default database service in Docker.

## [2026-01-13 17:50] - Markdown Support & Task Enhancement
- **Markdown Support**:
  - Installed `marked` for Markdown parsing.
  - Created `MarkdownViewer.vue` component to safely render Markdown content with auto-opening links in new tabs.
  - **Automatic link icons**: Added automatic service icon detection (GitLab, Asana, Google Docs/Sheets/Slides, GitHub, Slack, YouTube, etc.) for Markdown links.
  - Enabled Markdown for Task `memo`, `remarks`, and `output_url` fields.
  - Enabled Markdown for Schedule `memo` and `memos` fields.
  - Added "Markdown" hint badges to input labels in forms.
- **Backend Enhancements**:
  - Changed `output_url` column type from `string` to `text` to support long URLs.
  - Converted task date fields (`release_date`, `start_date`, `expected_finish_date`, `actual_finish_date`) to `timestamp` for better precision.
  - Refined `review_status` validation to be `nullable` and improved auto-update logic.
- **Frontend Enhancements**:
  - Expanded Task Edit Modal to include Project, Item, Department, Related Personnel, and Output URL.
  - Changed Output URL input to `textarea` for easier Markdown/Link editing.
  - Optimized Completed Tasks list with better date formatting and Markdown support for URLs and Remarks.
  - Fixed layout issues where long links would break table formatting.
- **Excel Import Improvements**:
  - Implemented automatic extraction of embedded hyperlinks from **all** Excel columns.
  - Added `autoConvertLinksToMarkdown` helper to linkify raw text URLs during import.
  - Fixed field mapping for "Item" and "Department" columns.
- **Bug Fixes**:
  - Restored missing `schedules/{schedule}/memos` route and fixed corresponding unit tests.


## [2026-01-13 15:50] - Excel Task Import
- **Backend**:
  - Implemented `TaskController@import` to batch import tasks from Excel files using `phpoffice/phpspreadsheet`.
  - Automatic extraction of embedded hyperlinks from the "Work" column (H column) into the "Memo" field.
  - Auto-approval and mapping of audit metadata based on import data.
- **Frontend**:
  - Added "Import Tasks" page for administrators.
  - Implemented premium file upload UI with drag-and-drop and real-time status.
  - Added multi-language support for import workflow.

## [2026-01-13 13:00] - Task Approval Workflow
- **Backend**:
  - Added `review_status` to tasks table (unsubmitted, submitted, approved, failed).
  - Implemented logic to auto-submit task for review when status becomes `finished`.
  - Added review status filtering to `TaskController@index`.
  - Updated `TaskPolicy` to allow Auditors to review (update) tasks and Assignees to update their own tasks.
  - Added `TaskReviewApiTest`.
- **Frontend**:
  - Added "Tasks Under Review" mode in `tasks.vue` (visible to Auditor, Manager, Admin).
  - Implemented read-only detail view for reviewing tasks.
  - Added "Pass" and "Fail" actions for Auditors.
  - Filtered out approved/failed tasks from the main task list.

## [2026-01-12 14:35] - Task & Schedule Details Enhancements
- **Backend**:
  - Added `show` and `update` methods to both `ScheduleController` and `TaskController`.
  - Updated API routes to support individual task/schedule retrieval and full updates.
  - Enabled sorting of Memos/Remarks by `created_at` (ASC).
- **Frontend**:
  - **Details Modal**: Implemented comprehensive detailed views for both Tasks and Schedules.
  - **Editing**: Integrated full editing capabilities within the detail modals, including updating all fields and dates.
  - **Remarks/Memos**:
    - List views now show only the **latest** remark/memo for a cleaner interface.
    - Full history of remarks/memos is accessible within the detail modals.
    - Added timestamp display for all remarks/memos.
  - **Actual Dates**: Added "Actual Start Date" and "Actual Finish Date" support for Schedules in both UI and backend.
  - **UI/UX**: Made task "Item" and "Work" cells clickable to open details, matching the schedule title behavior.
    
## [2026-01-09 16:50] - Schedule & Task Feature Enhancements
- **Backend**:
  - Created `ScheduleMemoController` to support message board style remarks for schedules.
  - Added `POST /api/schedules/{schedule}/memos` endpoint.
  - Added `POST /api/tasks/{task}/remarks` endpoint and `TaskRemarkController`.
  - Added `PUT /api/tasks/{task}` endpoint to allow updating tasks (e.g., changing assignee).
  - Added `GET /api/projects` endpoint and `ProjectController` for listing projects.
  - Added `GET /api/departments` endpoint and `DepartmentController` for listing departments.
  - Updated `ScheduleController@store` to default status to `in_progress` ("未執行").
  - Updated `TaskController@store` to default `status` to `in progress` ("未執行") and `level` to `1` ("一般").
  - Updated `openapi.yaml` and added comprehensive tests (`ScheduleMemoApiTest`, `TaskApiTest`).
- **Frontend**:
  - Updated `schedules.vue` to remove "Actual Start", "Actual Finish", and "Status" from the creation form.
  - Updated `tasks.vue` to remove "Status" and "Category" (Level) from the creation form, using backend defaults.
  - **Task Assignee**: Implemented inline assignee editing by clicking on the assignee cell in the task table.
  - **Project/Department**: Changed "Project" to select menu and added "Department" selection with intelligent auto-population based on selected assignee.
  - **Remarks Board**: Replaced simple Task "Memo" with an interactive message board mirroring the schedule feature.
  - **Fix**: Synchronized `en.json` and `zh-TW.json` with all missing status and board translations.
  - **UI**: Fixed vertical alignment of assignee information in the task table.

## [2026-01-09 14:15] - Role-Based Access Control (RBAC) System
- **Backend**:
  - Implemented 5 roles: `admin`, `manager`, `task_user`, `executor`, `auditor`.
  - Added Laravel Policies for `User`, `Department`, `Project`, `Schedule`, and `Task`.
  - Updated `CheckRole` middleware to support multi-role validation.
  - Enforced "Authorization before Validation" in controllers for enhanced security.
  - Seeded default users for all roles (e.g., `manager@example.com`, `auditor@example.com`).
- **Frontend**:
  - Enhanced `useAuth` composable with `hasRole` and `can` permission helpers.
  - Implemented dynamic UI rendering based on roles (hiding/showing buttons and nav items).
  - Updated `admin` middleware to support multiple authorized roles.

## [2026-01-09 11:45] - Admin Management Interface
- **Backend**:
  - **Schema**: Created `departments` and `projects` tables. Added `employee_id`, `role`, `is_active`, `department_id` to `users`.
  - **API**: Implemented Admin Controllers for Users, Projects, and Departments with CRUD operations.
  - **Security**: Added `CheckRole` middleware to restrict Admin APIs. Updated `AuthController` to block login for frozen (`is_active=false`) users.
  - [Fix] 解決刷新頁面跳回登入問題（Middleware 改進和自動用戶資料獲取）
  - [Fix] 修復 Admin 介面中 `apiBase` 未定義導致的路由錯誤
  - [Fix] 補全 `zh-TW` 語系中缺少的通用翻譯 keys (`common.name`)
  - [Feature] 完整 Admin 後台介面（用戶、部門、專案管理）
  - [Feature] 用戶凍結功能
  - **Testing**: Added comprehensive Feature tests for all Admin APIs (`AdminUserTest`, `AdminDepartmentTest`, `AdminProjectTest`).
  - **Seeding**: Added default departments ('技術', '營運', '產品', '美術', '客服', '測試', '前端', '後端', '運維'), default project ('Project'), and ensured default admin user exists.
- **Frontend**:
  - **Layout**: Created `admin` layout with sidebar navigation.
  - **Account Management**: Implemented User List, Create, and Edit pages. Added "Freeze" account functionality.
  - **Resource Management**: Implemented Project and Department management with Add/Edit modals.
  - **i18n**: Added full Chinese/English translations for the Admin interface.
  - **UI/UX**: Centered navigation links (Schedules, Tasks) in the header with active indicators.
  - **Navigation**: Relocated "Management" button to user dropdown for a cleaner header.
  - **Layout**: Synchronized Admin layout branding and container width (1920px) with the main application.
  - **i18n**: Moved language switcher from header to Personal Settings (`/profile`) page.

## [2026-01-08 17:30] - User Registration Feature with Toggle Control
- **Backend**: 
  - Added `ALLOW_REGISTRATION` environment variable to control registration availability.
  - Implemented `register` API endpoint (`POST /api/register`) with validation.
  - Added registration toggle check that returns 403 when disabled.
  - Updated `config/app.php` to include `allow_registration` configuration.
- **Frontend**:
  - Added `NUXT_PUBLIC_ALLOW_REGISTRATION` environment variable.
  - Created registration page (`/register`) with full i18n support.
  - Added conditional registration link on login page based on environment variable.
  - Implemented error handling for backend registration disabled scenario.
  - Updated `useAuth` composable to support registration.
  - **Fix**: Updated auth middleware to check token existence (`isAuthenticated`) instead of user object to prevent premature redirects.
  - **Fix**: Added global user fetch in `app.vue` to restore user session on page reload.
  - **Fix**: Resolves state synchronization issue after registration (auto-login).
  - **Fix**: Fixed layout transition issues on auth pages using dedicated `auth` layout.
- **Dashboard**: Added "View Tasks" button to the main dashboard action area.
- **Profile**: Implemented User Profile page (`/profile`).
  - View and edit personal information (Name, Email).
  - Change password functionality with current password validation.
  - Backend: Enhanced `ProfileController` to support email uniqueness check and password update.
- **i18n**: Added registration and profile related translations for English and Traditional Chinese.
- **Tests**: Added comprehensive Feature tests for Profile update functionality.
- **Documentation**: Updated `.env.example` files for both frontend and backend.



## [2026-01-08 17:00] - Backend Stability & Unit Tests
- **Fix**: Resolved `500 Internal Server Error` on API routes by forcing JSON responses for unauthenticated requests.
- **Fix**: Stabilized frontend authentication by refactoring `useFetch` headers in `tasks.vue` and `schedules.vue`.
- **Testing**: Added comprehensive Feature tests for Auth, Profile, Schedules, Tasks, and Users.
- **Testing**: Created model factories for Task, Schedule, Remark, and Memo.
- **Documentation**: Synchronized `openapi.yaml` with current API implementation.
- **Database**: Verified and seeded default admin user.

## [2026-01-08 16:30] - Task Management Integration
- **Frontend**: Implemented fully responsive Tasks page (`/tasks`) with:
  - Desktop Table View with status badges and user avatars.
  - Mobile Card View for optimized small-screen experience.
  - Create Task Modal with user selection and form validation.
- **Backend**: Implemented `TaskController` API (`index`, `store`) with relationship loading (`assignee`, `remarks`).
- **Database**: Finalized `tasks` table schema (added `related_personnel`, `item`, `memo` fields).
- **i18n**: Complete localization for Task status, priorities, and form labels.

## [2026-01-08 15:35] - Page Loading Transitions
- **UI/UX**: Added `NuxtLoadingIndicator` (top progress bar) and smooth fade/slide transitions between pages and layouts.
- **Config**: Enabled `pageTransition` and `layoutTransition` in `nuxt.config.ts`.
- **Styles**: Defined global transition animations in `app.vue`.

## [2026-01-08 15:30] - i18n Support for Schedules
- **i18n**: Integrated multi-language support (English/Traditional Chinese) for all table headers, labels, and status values on the Schedules page.
- **Documentation**: Added `README.md` and `release.md` to the root directory.

## [2026-01-08 15:15] - Responsive UI & Nuxt 4 Upgrade
- **New Feature**: Added a fully responsive Schedules page.
  - PC: Table view with max-width 1920px and automated date calculations.
  - Tablet: Centered card view (max-width 700px).
  - Mobile: Fluid card view (100% width).
- **Database**: Added `memo` field to `schedules` table.
- **Migration**: Upgraded frontend to Nuxt 4 structure (moved source to `app/`).
- **Fix**: Resolved `ENOENT` error for i18n locale files.
- **Maintenance**: Removed deprecated `msvs_version` npm warnings.

## [2026-01-07] - Initial Project Scaffolding
- Basic Nuxt and Laravel setup.
- Initial multi-language support (i18n).
- User authentication boilerplate.
