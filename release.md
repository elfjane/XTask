# Release Notes

All notable changes to the XTask project will be documented in this file.

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
