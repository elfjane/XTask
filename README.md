# XTask - Project Management System

XTask is a comprehensive project management application designed for cross-platform support (PC, Tablet, Mobile). It helps teams stay organized by tracking tasks, schedules, and collaborator remarks.

## Technology Stack

- **Frontend**: Nuxt 4, Vue 3, Vanilla CSS
- **Backend**: Laravel 12.x, PHP 8.x
- **Database**: MySQL/SQLite
- **Authentication**: Firebase Integration (planned/in-progress) + Laravel Auth
- **Testing**: PHPUnit, Laravel Feature Tests
- **Documentation**: OpenAPI 3.1.0

## Project Structure

```text
XTask/
├── frontend/    # Nuxt 4 Application
│   └── app/     # Core application source (Pages, Components, etc.)
└── backend/     # Laravel API
```

## Getting Started

### Frontend
1. Navigate to `/frontend`
2. Run `npm install`
3. Run `npm run dev`

### Backend
1. Navigate to `/backend`
2. Run `composer install`
3. Run `php artisan migrate`
4. Run `php artisan serve --port=8111`

### Docker (Recommended)
1. Run `docker-compose up -d` in the root directory.
2. Backend will be available at `http://localhost:8111` (via Nginx).
3. Frontend will be available at `http://localhost:3111`.

## Features
- **User Registration**: Configurable registration system with environment variable toggle.
- **Schedules**: Responsive view (Table for PC, Cards for Mobile) with an integrated **Message Board** for collaborator remarks. Supports **detailed view and editing** by clicking on schedule titles.
- **Task Points Statistics**: Interactive charts and matrix tables to track team performance and monthly contributions.
- **Markdown Support**: Full Markdown support for memos, remarks, and output links with automatic URL-to-link conversion.
- **Excel Task Import**: Batch import tasks with automatic extraction of embedded hyperlinks from Excel cells into Markdown links.
- **Responsive Design**: Fully functional across desktop, tablet, and mobile devices. Includes an **Approval Workflow** where finished tasks are submitted for Auditor review. The **Completed Tasks** list automatically filters out pending reviews to ensure data clarity. Supports **detailed view and editing** for all task and schedule fields.
- **Multi-language Support**: i18n integrated for English and Traditional Chinese.

## Environment Configuration

### Backend (.env)
```env
ALLOW_REGISTRATION=true  # Set to false to disable user registration
```

### Frontend (.env)
```env
NUXT_PUBLIC_ALLOW_REGISTRATION=true  # Set to false to hide registration UI
```

**Note**: If frontend allows registration but backend doesn't, users will see an error message when attempting to register.
