# The Goat Academy

The Goat Academy is a Laravel 12 application for managing language courses, students and examinations. It provides separate dashboards for administrators, instructors and exam officers. The frontend is built with Vue 3 and Vite and styled with Tailwind CSS.

## Features
- Student registration with skill tracking and file uploads.
- Course scheduling, attendance and progress tests.
- Role based permissions using [spatie/laravel-permission](https://github.com/spatie/laravel-permission).
- Printable reports for courses and suggested student paths.

## Installation
1. Clone the repository.
2. Run `composer install`.
3. Copy `.env.example` to `.env` and adjust your environment settings.
4. Generate an application key with `php artisan key:generate`.
5. Run the migrations and seeders via `php artisan migrate --seed`.
6. Install frontend dependencies with `npm install`.
7. Build assets with `npm run dev` (use `npm run build` for production).
8. Start the development server using `php artisan serve` or `composer dev` to run all watchers.

## Running Tests
Execute the test suite with:

```bash
php artisan test
```

## License
Released under the MIT License.
