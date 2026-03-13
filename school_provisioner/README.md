# School Provisioning

`provision_school.php` creates a new school as its own standalone folder, using one existing school as the template.

## What It Asks You

When you run:

```bash
php provision_school.php
```

the script asks for:

1. Template school folder
2. School name
3. Base directory to provision into
4. New folder name
5. `APP_URL`
6. Portal path
7. DB host
8. DB port
9. DB database
10. DB username
11. DB password
12. Primary color
13. Secondary color
14. Front page tagline
15. Front page headline
16. Front page summary
17. School address
18. School phone
19. School email
20. Default admin email
21. Logo file path (optional)
22. Login background file path (optional)

Yes, it asks which school to copy as the template.

Example template options:

- `bs_abuja`
- `bs_kd`
- `bs_kaduna`

## What It Creates

The script creates a brand new standalone school folder inside whatever base directory you provide.

Example:

```text
/home/mimi/.test/schools/royal_academy
```

That new folder is a separate Laravel app, like the existing school folders.

## What The Script Does

- Copies the selected template school into a new folder
- Excludes heavy/generated directories like `vendor`, `node_modules`, and built frontend output
- Writes the new school branding and config
- Generates `.env.example`
- Generates a ready-to-edit `.env`
- Creates a simple front-facing website using Laravel Blade plus normal HTML/CSS/JS
- Seeds school settings for the new school
- Optionally copies the logo and login background into the new app
- Sets up the app so the school logo can be used as the portal favicon
- Replaces visible legacy branding with `Auracle Technologies`

## Database Tables

The provisioner does not directly create database tables by itself.

Database tables are created when you run this inside the new school folder:

```bash
php artisan app:init
```

`app:init` runs:

- migrations
- seeders
- cache clear/build steps
- admin initialization

So the normal flow is:

```bash
cd /home/mimi/.test/<new_school_folder>
```

Then review `.env`, then run:

```bash
php artisan app:init
php artisan storage:link
```

## Front Website

The generated front website is shared-hosting friendly and does not require Node to edit.

Main files:

- `resources/views/website/front.blade.php`
- `public/website/app.css`
- `public/website/app.js`
- `config/school_frontend.php`

## Current Limits

- The script prepares the school app but does not automatically create the MySQL database itself.
- You still need to provide working `.env` database credentials for the new school.
- The script does not currently deploy to production automatically.

## Recommended Use

1. Run `php provision_school.php`
2. Choose the template school
3. Choose the base directory for the new school
4. Fill in the remaining prompts
5. Open the new folder
6. Review `.env`
7. Run `php artisan app:init`
8. Run `php artisan storage:link`
9. Upload/deploy that standalone folder as its own school instance
