#!/usr/bin/env php
<?php

declare(strict_types=1);

const COPY_EXCLUDES = [
    '.git',
    'vendor',
    'node_modules',
    'frontend_source',
    'public/build',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
    'bootstrap/cache',
];

main();

function main(): void
{
    $workspace = __DIR__;
    $templates = discoverTemplates($workspace);

    if ($templates === []) {
        fwrite(STDERR, "No school templates were found in {$workspace}.\n");
        exit(1);
    }

    echo "School Provisioner\n";
    echo "==================\n\n";

    $defaultTemplate = in_array('bs_abuja', $templates, true) ? 'bs_abuja' : $templates[0];
    echo "Available templates: " . implode(', ', $templates) . "\n";

    $templateName = prompt('Template school folder', $defaultTemplate);
    if (!in_array($templateName, $templates, true)) {
        fwrite(STDERR, "Template '{$templateName}' does not exist.\n");
        exit(1);
    }

    $schoolName = promptRequired('School name');
    $targetBaseDir = prompt('Base directory to provision into', $workspace);
    $targetSlug = prompt('New folder name', slugify($schoolName));
    $appUrl = prompt('APP_URL', "https://{$targetSlug}.test");
    $portalPath = prompt('Portal path', '/login');
    $dbHost = prompt('DB host', '127.0.0.1');
    $dbPort = prompt('DB port', '3306');
    $dbName = prompt('DB database', $targetSlug);
    $dbUser = prompt('DB username', $targetSlug);
    $dbPassword = prompt('DB password', '');
    $primaryColor = prompt('Primary color (hex)', '#0d5cab');
    $secondaryColor = prompt('Secondary color (hex)', '#f4b544');
    $tagline = prompt('Front page tagline', 'Academic Excellence, Character, and Leadership');
    $headline = prompt('Front page headline', "Welcome to {$schoolName}");
    $summary = prompt(
        'Front page summary',
        "{$schoolName} combines strong academics, structure, and care to help every learner thrive."
    );
    $address = prompt('School address', 'Enter school address');
    $phone = prompt('School phone', 'Enter school phone');
    $email = prompt('School email', 'info@example.com');
    $adminEmail = prompt('Default admin email', "admin@{$targetSlug}.com");
    $logoPath = prompt('Logo file path (optional)', '');
    $loginBackgroundPath = prompt('Login background file path (optional)', '');

    $templatePath = "{$workspace}/{$templateName}";
    $targetBaseDir = rtrim($targetBaseDir, DIRECTORY_SEPARATOR);

    if ($targetBaseDir === '') {
        fwrite(STDERR, "Base directory cannot be empty.\n");
        exit(1);
    }

    if (!is_dir($targetBaseDir) && !mkdir($targetBaseDir, 0775, true) && !is_dir($targetBaseDir)) {
        fwrite(STDERR, "Could not create base directory: {$targetBaseDir}\n");
        exit(1);
    }

    $targetPath = "{$targetBaseDir}/{$targetSlug}";

    if (file_exists($targetPath)) {
        fwrite(STDERR, "Target folder already exists: {$targetPath}\n");
        exit(1);
    }

    echo "\nCopying template...\n";
    copyDirectory($templatePath, $targetPath);

    $relativeLogoPath = copyBrandingAsset($logoPath, $targetPath, 'logo');
    $relativeBackgroundPath = copyBrandingAsset($loginBackgroundPath, $targetPath, 'login-background');

    echo "Configuring application...\n";
    configureEnvironment(
        $targetPath,
        $schoolName,
        $appUrl,
        $adminEmail,
        $dbHost,
        $dbPort,
        $dbName,
        $dbUser,
        $dbPassword
    );
    writeSiteSettingSeeder(
        $targetPath,
        $schoolName,
        $primaryColor,
        $secondaryColor,
        $relativeLogoPath,
        $relativeBackgroundPath
    );
    writeSchoolFrontendConfig(
        $targetPath,
        [
            'school_name' => $schoolName,
            'tagline' => $tagline,
            'headline' => $headline,
            'summary' => $summary,
            'address' => $address,
            'phone' => $phone,
            'email' => $email,
            'portal_path' => $portalPath,
            'primary_color' => $primaryColor,
            'secondary_color' => $secondaryColor,
        ]
    );
    writeFrontWebsiteFiles($targetPath);
    ensureWebsiteRoute($targetPath);
    applyAuracleBranding($targetPath);
    ensureDynamicFavicon($targetPath);
    writeProvisioningNotes($targetPath, $templateName, $portalPath);

    echo "\nProvisioning complete.\n";
    echo "New school: {$targetPath}\n";
    echo "Next steps:\n";
    echo "1. cd {$targetPath}\n";
    echo "2. Review .env\n";
    echo "3. php artisan app:init\n";
    echo "4. php artisan storage:link\n";
}

function discoverTemplates(string $workspace): array
{
    $entries = array_filter(scandir($workspace) ?: [], static function (string $entry) use ($workspace): bool {
        if ($entry === '.' || $entry === '..') {
            return false;
        }

        if (!str_starts_with($entry, 'bs_')) {
            return false;
        }

        return is_dir("{$workspace}/{$entry}") && is_file("{$workspace}/{$entry}/artisan");
    });

    sort($entries);

    return array_values($entries);
}

function prompt(string $label, string $default = ''): string
{
    $suffix = $default !== '' ? " [{$default}]" : '';
    $value = readline("{$label}{$suffix}: ");
    $value = trim($value);

    return $value === '' ? $default : $value;
}

function promptRequired(string $label): string
{
    do {
        $value = trim(readline("{$label}: "));
    } while ($value === '');

    return $value;
}

function slugify(string $value): string
{
    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9]+/', '_', $value) ?? '';
    $value = trim($value, '_');

    return $value !== '' ? $value : 'new_school';
}

function copyDirectory(string $source, string $destination): void
{
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        $sourcePath = $item->getPathname();
        $relativePath = ltrim(str_replace($source, '', $sourcePath), DIRECTORY_SEPARATOR);

        if ($relativePath === '' || shouldExclude($relativePath)) {
            continue;
        }

        $targetPath = "{$destination}/{$relativePath}";

        if ($item->isDir()) {
            if (!is_dir($targetPath)) {
                mkdir($targetPath, 0775, true);
            }
            continue;
        }

        if (!is_dir(dirname($targetPath))) {
            mkdir(dirname($targetPath), 0775, true);
        }

        copy($sourcePath, $targetPath);
    }
}

function shouldExclude(string $relativePath): bool
{
    foreach (COPY_EXCLUDES as $exclude) {
        if ($relativePath === $exclude || str_starts_with($relativePath, $exclude . DIRECTORY_SEPARATOR)) {
            return true;
        }
    }

    return in_array(basename($relativePath), ['.env', 'laravel.log'], true);
}

function copyBrandingAsset(string $sourcePath, string $targetRoot, string $prefix): ?string
{
    $sourcePath = trim($sourcePath);
    if ($sourcePath === '') {
        return null;
    }

    if (!is_file($sourcePath)) {
        fwrite(STDERR, "Skipping missing asset: {$sourcePath}\n");
        return null;
    }

    $extension = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION)) ?: 'png';
    $relativePath = "branding/{$prefix}.{$extension}";
    $targetPath = "{$targetRoot}/public/{$relativePath}";

    if (!is_dir(dirname($targetPath))) {
        mkdir(dirname($targetPath), 0775, true);
    }

    copy($sourcePath, $targetPath);

    return $relativePath;
}

function configureEnvironment(
    string $targetPath,
    string $schoolName,
    string $appUrl,
    string $adminEmail,
    string $dbHost,
    string $dbPort,
    string $dbName,
    string $dbUser,
    string $dbPassword
): void
{
    $envExamplePath = "{$targetPath}/.env.example";
    if (!is_file($envExamplePath)) {
        return;
    }

    $content = file_get_contents($envExamplePath);
    if ($content === false) {
        return;
    }

    $replacements = [
        '/^APP_NAME=.*$/m' => 'APP_NAME="' . addcslashes($schoolName, '"') . '"',
        '/^APP_URL=.*$/m' => "APP_URL={$appUrl}",
        '/^DEFAULT_ADMIN_EMAIL=.*$/m' => "DEFAULT_ADMIN_EMAIL={$adminEmail}",
        '/^DB_HOST=.*$/m' => "DB_HOST={$dbHost}",
        '/^DB_PORT=.*$/m' => "DB_PORT={$dbPort}",
        '/^DB_DATABASE=.*$/m' => "DB_DATABASE={$dbName}",
        '/^DB_USERNAME=.*$/m' => "DB_USERNAME={$dbUser}",
        '/^DB_PASSWORD=.*$/m' => "DB_PASSWORD={$dbPassword}",
    ];

    foreach ($replacements as $pattern => $replacement) {
        if (preg_match($pattern, $content) === 1) {
            $content = preg_replace($pattern, $replacement, $content) ?? $content;
        } else {
            $content .= PHP_EOL . $replacement;
        }
    }

    file_put_contents($envExamplePath, $content);
    file_put_contents("{$targetPath}/.env", $content);
}

function writeSiteSettingSeeder(
    string $targetPath,
    string $schoolName,
    string $primaryColor,
    string $secondaryColor,
    ?string $logoPath,
    ?string $loginBackgroundPath
): void {
    $logoLine = $logoPath !== null ? "                'school_logo_path' => '{$logoPath}',\n" : '';
    $backgroundLine = $loginBackgroundPath !== null ? "                'login_background_path' => '{$loginBackgroundPath}',\n" : '';

    $content = <<<PHP
<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SiteSetting::updateOrCreate(
            ['id' => 1],
            [
                'school_name' => '{$schoolName}',
{$logoLine}{$backgroundLine}                'primary_color' => '{$primaryColor}',
                'secondary_color' => '{$secondaryColor}',
                'geo_range' => 500,
                'late_time' => '08:00',
            ]
        );
    }
}
PHP;

    writeFile("{$targetPath}/database/seeders/SiteSettingSeeder.php", $content . PHP_EOL);
}

function writeSchoolFrontendConfig(string $targetPath, array $config): void
{
    $export = var_export($config, true);
    $content = <<<PHP
<?php

return {$export};
PHP;

    writeFile("{$targetPath}/config/school_frontend.php", $content . PHP_EOL);
}

function writeFrontWebsiteFiles(string $targetPath): void
{
    $view = <<<'BLADE'
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('school_frontend.school_name', config('app.name')) }}</title>
    <meta name="description" content="{{ config('school_frontend.summary') }}">
    @php
        $schoolLogo = isset($site_setting) && !empty($site_setting->school_logo_path)
            ? asset($site_setting->school_logo_path)
            : null;
    @endphp
    @if($schoolLogo)
        <link rel="icon" href="{{ $schoolLogo }}">
        <link rel="shortcut icon" href="{{ $schoolLogo }}">
        <link rel="apple-touch-icon" href="{{ $schoolLogo }}">
    @endif
    <link rel="stylesheet" href="{{ asset('website/app.css') }}">
</head>
<body>
    <header class="site-header">
        <div class="container nav-row">
            <div class="brand">
                @if(isset($site_setting) && !empty($site_setting->school_logo_path))
                    <img src="{{ asset($site_setting->school_logo_path) }}" alt="{{ config('school_frontend.school_name') }} logo">
                @endif
                <div>
                    <p class="eyebrow">{{ config('school_frontend.tagline') }}</p>
                    <h1>{{ config('school_frontend.school_name', config('app.name')) }}</h1>
                </div>
            </div>
            <a class="portal-link" href="{{ config('school_frontend.portal_path', '/login') }}">Portal Login</a>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="container hero-grid">
                <div>
                    <p class="eyebrow">Shared-hosting friendly website</p>
                    <h2>{{ config('school_frontend.headline') }}</h2>
                    <p class="lead">{{ config('school_frontend.summary') }}</p>
                    <div class="hero-actions">
                        <a class="btn btn-primary" href="{{ config('school_frontend.portal_path', '/login') }}">Open Portal</a>
                        <a class="btn btn-secondary" href="#contact">Contact School</a>
                    </div>
                </div>
                <div class="hero-card">
                    <h3>What you can edit</h3>
                    <ul>
                        <li>`resources/views/website/front.blade.php` for the HTML</li>
                        <li>`public/website/app.css` for styling</li>
                        <li>`public/website/app.js` for interactions</li>
                        <li>`config/school_frontend.php` for school content</li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="info-strip">
            <div class="container cards">
                <article class="info-card">
                    <h3>Fast to maintain</h3>
                    <p>No Node build is required for the front-facing website.</p>
                </article>
                <article class="info-card">
                    <h3>Linked to Laravel</h3>
                    <p>The login button goes straight into the existing Laravel portal.</p>
                </article>
                <article class="info-card">
                    <h3>School branding</h3>
                    <p>The uploaded school logo is used for the portal brand and favicon.</p>
                </article>
            </div>
        </section>

        <section class="about">
            <div class="container split">
                <div>
                    <p class="eyebrow">About the school</p>
                    <h3>Front website with normal HTML, CSS, and JS</h3>
                    <p>
                        This school was provisioned with a simple Laravel-served frontend so it works cleanly on shared hosting.
                        You can expand this page into a full site without needing a separate Node deployment.
                    </p>
                </div>
                <div class="checklist">
                    <div>Single deploy target</div>
                    <div>Logo-aware portal</div>
                    <div>Blade-based front pages</div>
                    <div>Editable school content</div>
                </div>
            </div>
        </section>

        <section id="contact" class="contact">
            <div class="container split">
                <div>
                    <p class="eyebrow">Contact</p>
                    <h3>School details</h3>
                    <p>{{ config('school_frontend.address') }}</p>
                    <p><a href="tel:{{ preg_replace('/\s+/', '', config('school_frontend.phone')) }}">{{ config('school_frontend.phone') }}</a></p>
                    <p><a href="mailto:{{ config('school_frontend.email') }}">{{ config('school_frontend.email') }}</a></p>
                </div>
                <div class="hero-card">
                    <h3>Portal access</h3>
                    <p>Use the school portal for administration, staff, results, and student workflows.</p>
                    <a class="btn btn-primary" href="{{ config('school_frontend.portal_path', '/login') }}">Go to Login</a>
                </div>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container footer-row">
            <p>&copy; {{ date('Y') }} {{ config('school_frontend.school_name', config('app.name')) }}</p>
            <p>Powered by <a href="https://kayodeamusan.netlify.app" target="_blank" rel="noreferrer">Auracle Technologies</a></p>
        </div>
    </footer>

    <script src="{{ asset('website/app.js') }}"></script>
</body>
</html>
BLADE;

    $css = <<<'CSS'
:root {
    --primary-color: #0d5cab;
    --secondary-color: #f4b544;
    --ink: #122033;
    --muted: #5f6f84;
    --panel: #ffffff;
    --panel-soft: #f5f7fb;
    --line: #d9e0eb;
    --shadow: 0 24px 50px rgba(18, 32, 51, 0.12);
}

* {
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    color: var(--ink);
    background:
        radial-gradient(circle at top left, rgba(244, 181, 68, 0.24), transparent 28%),
        linear-gradient(180deg, #fcfdff 0%, #eef3f9 100%);
}

a {
    color: inherit;
}

.container {
    width: min(1120px, calc(100% - 32px));
    margin: 0 auto;
}

.site-header {
    padding: 20px 0;
}

.nav-row,
.footer-row,
.split,
.hero-grid,
.cards {
    display: grid;
    gap: 24px;
}

.nav-row,
.footer-row {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    align-items: center;
}

.brand {
    display: flex;
    align-items: center;
    gap: 16px;
}

.brand img {
    width: 56px;
    height: 56px;
    object-fit: contain;
    border-radius: 14px;
    background: #fff;
    padding: 6px;
    box-shadow: var(--shadow);
}

.eyebrow {
    margin: 0 0 8px;
    font-size: 12px;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: var(--muted);
}

.brand h1,
.hero h2,
.about h3,
.contact h3,
.hero-card h3,
.info-card h3 {
    margin: 0;
    line-height: 1.1;
}

.portal-link,
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    min-height: 48px;
    padding: 0 18px;
    border-radius: 999px;
    text-decoration: none;
    font-weight: 700;
}

.portal-link,
.btn-primary {
    color: #fff;
    background: var(--primary-color);
    box-shadow: var(--shadow);
}

.btn-secondary {
    color: var(--primary-color);
    background: rgba(13, 92, 171, 0.08);
}

.hero,
.about,
.contact,
.info-strip {
    padding: 32px 0 56px;
}

.hero-grid,
.split {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    align-items: center;
}

.hero h2 {
    font-size: clamp(2.4rem, 4vw, 4.8rem);
    margin-bottom: 20px;
}

.lead {
    margin: 0 0 28px;
    font-size: 1.05rem;
    line-height: 1.7;
    color: var(--muted);
}

.hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.hero-card,
.info-card,
.checklist {
    background: var(--panel);
    border: 1px solid rgba(217, 224, 235, 0.8);
    border-radius: 24px;
    padding: 28px;
    box-shadow: var(--shadow);
}

.hero-card ul {
    margin: 16px 0 0;
    padding-left: 18px;
    color: var(--muted);
    line-height: 1.8;
}

.cards {
    grid-template-columns: repeat(3, minmax(0, 1fr));
}

.info-card p,
.about p,
.contact p {
    color: var(--muted);
    line-height: 1.7;
}

.checklist {
    display: grid;
    gap: 14px;
    background:
        linear-gradient(135deg, rgba(13, 92, 171, 0.08), rgba(244, 181, 68, 0.18)),
        #fff;
}

.checklist div {
    padding: 14px 16px;
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.9);
    font-weight: 700;
}

.site-footer {
    padding: 24px 0 40px;
    border-top: 1px solid var(--line);
}

.site-footer a {
    color: var(--primary-color);
}

@media (max-width: 820px) {
    .nav-row,
    .footer-row,
    .hero-grid,
    .split,
    .cards {
        grid-template-columns: 1fr;
    }

    .nav-row {
        justify-items: start;
    }

    .portal-link {
        width: 100%;
    }
}
CSS;

    $js = <<<'JS'
document.addEventListener('DOMContentLoaded', function () {
    document.body.classList.add('is-ready');
});
JS;

    writeFile("{$targetPath}/resources/views/website/front.blade.php", $view . PHP_EOL);
    writeFile("{$targetPath}/public/website/app.css", $css . PHP_EOL);
    writeFile("{$targetPath}/public/website/app.js", $js . PHP_EOL);
}

function ensureWebsiteRoute(string $targetPath): void
{
    $routesPath = "{$targetPath}/routes/web.php";
    if (!is_file($routesPath)) {
        return;
    }

    $content = file_get_contents($routesPath);
    if ($content === false || str_contains($content, "name('website.home')")) {
        return;
    }

    $route = <<<PHP

Route::view('/', 'website.front')->name('website.home');
PHP;

    file_put_contents($routesPath, rtrim($content) . PHP_EOL . $route . PHP_EOL);
}

function applyAuracleBranding(string $targetPath): void
{
    $files = [
        "{$targetPath}/app/Providers/AppServiceProvider.php",
        "{$targetPath}/app/Http/Controllers/SiteSettingController.php",
        "{$targetPath}/app/Console/Commands/AppInit.php",
        "{$targetPath}/resources/views/layouts/footer.blade.php",
        "{$targetPath}/resources/views/home.blade.php",
        "{$targetPath}/resources/views/layouts/website.blade.php",
        "{$targetPath}/resources/views/layouts/app.blade.php",
        "{$targetPath}/resources/views/welcome.blade.php",
        "{$targetPath}/resources/js/Layouts/AppShell.tsx",
        "{$targetPath}/database/seeders/StaffSeeder.php",
        "{$targetPath}/database/migrations/2026_01_05_111830_create_site_settings_table.php",
        "{$targetPath}/config/app.php",
    ];

    $replacements = [
        'Unified Transform Academy' => 'Auracle Technologies',
        'Unified Transform' => 'Auracle Technologies',
        'Unifiedtransform' => 'Auracle Technologies',
        'https://github.com/changeweb/Unifiedtransform' => 'https://kayodeamusan.netlify.app',
        'changeweb/Unifiedtransform' => 'kayodeamusan.netlify.app',
        'admin@ut.com' => 'admin@auracletech.com',
        'unifiedtransform' => 'auracle_technologies',
    ];

    foreach ($files as $file) {
        if (!is_file($file)) {
            continue;
        }

        $content = file_get_contents($file);
        if ($content === false) {
            continue;
        }

        $updated = str_replace(array_keys($replacements), array_values($replacements), $content);
        file_put_contents($file, $updated);
    }
}

function ensureDynamicFavicon(string $targetPath): void
{
    $layoutFiles = [
        "{$targetPath}/resources/views/layouts/app.blade.php",
        "{$targetPath}/resources/views/welcome.blade.php",
    ];

    $search = <<<'BLADE'
    <link rel="shortcut icon" href="{{asset('favicon_io/favicon.ico')}}">
    <link rel="shortcut icon" sizes="16x16" href="{{asset('favicon_io/favicon-16x16.png')}}">
    <link rel="shortcut icon" sizes="32x32" href="{{asset('favicon_io/favicon-32x32.png')}}">
    <link rel="apple-touch-icon" href="{{asset('favicon_io/apple-touch-icon.png')}}">
    <link rel="icon" href="{{asset('favicon_io/android-chrome-192x192.png')}}" sizes="192x192">
    <link rel="icon" href="{{asset('favicon_io/android-chrome-512x512.png')}}" sizes="512x512">
BLADE;

    $replace = <<<'BLADE'
    @php
        $favicon = isset($site_setting) && !empty($site_setting->school_logo_path)
            ? asset($site_setting->school_logo_path)
            : asset('favicon_io/favicon.ico');
    @endphp
    <link rel="icon" href="{{ $favicon }}">
    <link rel="shortcut icon" href="{{ $favicon }}">
    <link rel="apple-touch-icon" href="{{ $favicon }}">
    @if(!isset($site_setting) || empty($site_setting->school_logo_path))
        <link rel="shortcut icon" sizes="16x16" href="{{ asset('favicon_io/favicon-16x16.png') }}">
        <link rel="shortcut icon" sizes="32x32" href="{{ asset('favicon_io/favicon-32x32.png') }}">
        <link rel="icon" href="{{ asset('favicon_io/android-chrome-192x192.png') }}" sizes="192x192">
        <link rel="icon" href="{{ asset('favicon_io/android-chrome-512x512.png') }}" sizes="512x512">
    @endif
BLADE;

    foreach ($layoutFiles as $file) {
        if (!is_file($file)) {
            continue;
        }

        $content = file_get_contents($file);
        if ($content === false) {
            continue;
        }

        $content = str_replace($search, $replace, $content);
        file_put_contents($file, $content);
    }
}

function writeProvisioningNotes(string $targetPath, string $templateName, string $portalPath): void
{
    $notes = <<<TXT
This school was provisioned from template: {$templateName}

Frontend:
- HTML: resources/views/website/front.blade.php
- CSS: public/website/app.css
- JS: public/website/app.js
- Content: config/school_frontend.php

Portal link:
- {$portalPath}

Branding:
- Unified Transform references were replaced with Auracle Technologies in the provisioned app.
- The portal favicon uses the configured school logo when one exists.
TXT;

    writeFile("{$targetPath}/PROVISIONING_NOTES.txt", $notes . PHP_EOL);
}

function writeFile(string $path, string $content): void
{
    if (!is_dir(dirname($path))) {
        mkdir(dirname($path), 0775, true);
    }

    file_put_contents($path, $content);
}
