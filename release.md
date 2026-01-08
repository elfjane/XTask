# Release Notes

All notable changes to the XTask project will be documented in this file.

## [2026-01-08] - Responsive UI & Nuxt 4 Upgrade
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
