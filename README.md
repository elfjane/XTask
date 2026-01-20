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

### Local Development
- **Frontend**: Navigate to `/frontend`, run `npm install` then `npm run dev`.
- **Backend**: Navigate to `/backend`, run `composer install`, `php artisan migrate`, then `php artisan serve --port=8111`.

### Docker (Recommended)
We provide a convenient script to manage your Docker environments:
- **Development**: Run `manage_docker.bat dev` (Windows) or `docker-compose up -d --build`.
- **Production Simulation**: Run `manage_docker.bat prod`. This uses multi-stage builds to package the code inside images and removes source code volume mounts for security.

## Deployment
Automate your production deployment with our built-in scripts:
1.  Configure your server details in `deploy.sh`.
2.  Run `deploy.sh` (Linux/Mac) or `deploy.bat` (Windows).
- **Process**: Builds Frontend -> Packages Files -> SSH Upload -> Remote Extraction -> Runs Post-deploy hooks (Migration, Cache clean, etc.).

## Features & Optimizations
- **Performance**: 
    - **Skeleton Loaders**: Animated loading states for a smoother perceived experience.
    - **Search Debouncing**: Optimized list filtering to reduce API overhead.
    - **Multi-stage Docker**: Optimized production images for faster deployment and smaller footprint.
- **Task Management**: Drag-and-drop reordering, priority levels (with color coding), and detailed edit modals.
- **Approval Workflow**: Integrated review system for task quality assurance.
- **Excel Integration**: Batch import tasks with automatic Markdown link conversion for embedded URLs.
- **Markdown Support**: Rich text rendering across all memos and remarks.
- **Multi-language**: Built-in support for English and Traditional Chinese (i18n).

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
