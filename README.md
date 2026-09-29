# SQL Daily

A local SQL practice app for keeping data analysis skills fresh, one query at a time.

SQL Daily is a 60-day syntax gym. Each day presents a business question, a visual guide to the available tables, and a place to write and run a SQLite query. The focus is on revisiting familiar SQL concepts regularly rather than following a beginner-to-advanced course.

> **Project status:** Phase one is a local, single-user app under active development. The current 60-day catalog rotates challenge patterns across five business datasets. Online accounts and syncing are outside this phase.

## How a practice day works

1. Open the dashboard to see today's challenge and your current and best streaks.
2. Read the question and inspect the table cards, columns, keys, and relationships.
3. Write a query in the SQL editor. Your draft is saved locally as you work.
4. Run it to see the result table and feedback. If you get stuck, reveal a hint.
5. Match the expected result to complete the challenge and see a reference query.

One new challenge becomes available each calendar day. Earlier challenges stay open for practice, including ones you missed. A challenge extends your streak only when you complete it on its assigned day; a missed day resets the current streak. The app uses the **Africa/Cairo** timezone for its daily schedule.

## What you can practice

The prompts use five small business datasets: retail sales, e-commerce, workforce, subscriptions, and inventory. They revisit filtering and sorting, joins, grouping and `HAVING`, subqueries and `EXISTS`, `CASE`, CTEs, and window functions such as ranking and running totals.

The challenge catalog currently provides 60 daily slots by combining 12 prompt patterns with those five datasets. The exercises are mixed across days instead of arranged as levels. They are based on the topics in the [SQL knowledge notes](my%20knowledge/Learning%20SQL.md) included in this repository.

Queries run against a fresh, temporary copy of the selected dataset. The app accepts one read-only `SELECT` or `WITH` query and compares its columns, values, and row order with a reference result. Your query cannot change the database that stores your progress.

## Run it locally

### Requirements

- PHP 8.3 or newer with **PDO SQLite** enabled (`php -m` should list `pdo_sqlite`)
- Composer
- Node.js and npm

No MySQL or PostgreSQL server, account, or hosted service is required. After dependencies are installed and frontend assets are built, the app runs locally.

### Setup

From the project directory:

```bash
composer install
npm install
cp .env.example .env
touch database/database.sqlite
php artisan key:generate
php artisan migrate
npm run build
php artisan serve
```

Open the local address printed by `php artisan serve`. The first visit starts Day 1 and creates your local practice profile.

The commands above use a Unix-like shell. On Windows, copy `.env.example` to `.env` and create an empty `database/database.sqlite` file using your shell or file manager before running the Artisan commands.

### If you see “could not find driver”

PHP is not loading its PDO SQLite extension. Check with:

```bash
php -m | grep -i sqlite
```

On Arch Linux or CachyOS, install the extension package if needed:

```bash
sudo pacman -S php-sqlite
```

Then enable `extension=pdo_sqlite` in `/etc/php/php.ini` by removing the leading semicolon. Enable `extension=sqlite3` there too if you want the separate SQLite3 PHP API. Restart any running PHP server, check `php -m` again, and rerun `php artisan migrate`.

Installing the `sqlite3` command-line program alone does **not** enable the PHP driver.

## Where your work is stored

Your start date, drafts, attempts, and completions are stored in `database/database.sqlite`. That file is ignored by Git, so cloning the repository starts with a fresh profile. Back up that file if you want to keep your progress when moving computers or reinstalling the app.

The challenge definitions and sample datasets live in `app/Domain/Practice/`. The interface is in `resources/views/`, and the SQL editor is bundled from `resources/js/`.

## Development

Run the test suite and rebuild frontend assets with:

```bash
php artisan test
npm run build
```

The app uses Laravel for the local web application, SQLite for progress and practice queries, CodeMirror for the SQL editor, and Tailwind CSS for the dark interface. These are implementation details; the product is designed to work entirely on your own machine.
