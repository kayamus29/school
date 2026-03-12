$portals = @(
    "c:\Users\kaygo\Desktop\livecpanel\schools\bs_abuja",
    "c:\Users\kaygo\Desktop\livecpanel\schools\bs_kaduna",
    "c:\Users\kaygo\Desktop\livecpanel\schools\bs_kd"
)

foreach ($port in $portals) {
    Write-Host "Migrating $port ..."
    Set-Location $port
    # Run migrations and seed. Use --force for production safety if APP_ENV is production, 
    # but here APP_ENV is local in the env.
    php artisan migrate --seed
}

Write-Host "Migration and Seeding Complete."
