# Laravel Work Tracker

A personal productivity and time-tracking application built with Laravel 12, Blade and MySQL. It helps manage daily activities, track time spent on different work types and review completed work sessions.

## Features

- Manage work types such as Office Work, Writing, Studying, Running, etc.
- Start and stop work timers.
- Maintain active timers across page refreshes.
- Automatically record completed work sessions.
- View work history with start time, end time and total duration.
- Filter records by work type, description, day, month and year.
- Responsive design for desktop, tablet and mobile devices.

## Technology Stack

- Backend: PHP 8.x, Laravel 12
- Frontend: Blade, HTML, CSS, JavaScript
- Database: MySQL
- Mobile Support: Responsive Web Design


## Installation

Clone the repository:

```bash
git clone https://github.com/ravigurupackiyam/laravel-work-tracker.git
cd laravel-work-tracker
```

Install dependencies:

```bash
composer install
```

Configure environment:

```bash
cp .env.example .env
php artisan key:generate
```

Configure your database credentials in the `.env` file.

Run migrations:

```bash
php artisan migrate
```

Start the development server:

```bash
php artisan serve
```

Open the application at:

`http://127.0.0.1:8000`

## Project Purpose

This project demonstrates practical implementation of Laravel application development, persistent timer management, work session tracking, database operations, filtering and responsive user interface design.
