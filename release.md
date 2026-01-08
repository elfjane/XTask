# Release Notes

All notable changes to the XTask project will be documented in this file.

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
