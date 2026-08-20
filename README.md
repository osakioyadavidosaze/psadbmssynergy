# Synergy Food

A hostable PHP CRUD food website. The public page displays available menu items; `admin.php` lets you add, edit, hide, and delete items.

## Requirements

- PHP 8.1 or newer
- PDO SQLite extension enabled

## Run locally

From this folder, run:

```powershell
php -S localhost:8000
```

Then open <http://localhost:8000> in a browser. The SQLite database is created and seeded automatically at `data/synergy.sqlite`.

## Hosting

### Docker / Render-style hosting

This project includes a `Dockerfile` and `render.yaml`. Push the folder to GitHub, create a new Render Web Service from the repository, choose Docker, and deploy. Render will use PHP 8.2 with PDO SQLite automatically.

The free Render filesystem is temporary. Menu edits and user accounts can be lost after a redeploy or restart. For a real production site, use a persistent disk or migrate the app to PostgreSQL/MySQL.

### Traditional PHP hosting

Upload the project files to a PHP host with PHP 8.1+ and PDO SQLite enabled. Make the `data` folder writable by PHP. The database file is created at `data/synergy.sqlite`, and `data/.htaccess` blocks direct downloads of the database.

Before publishing, change the admin password, enable HTTPS, and keep error display disabled.

## CRUD routes

- `index.php`: public menu and category filtering
- `admin.php`: create, read, update, and delete menu items
- `schema.sql`: database schema reference
