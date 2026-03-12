$portals = @(
    "c:\Users\kaygo\Desktop\livecpanel\schools\bs_abuja",
    "c:\Users\kaygo\Desktop\livecpanel\schools\bs_kaduna",
    "c:\Users\kaygo\Desktop\livecpanel\schools\bs_kd"
)

foreach ($port in $portals) {
    Write-Host "Generating key for $port ..."
    Set-Location $port
    php artisan key:generate
}

Write-Host "Key Generation Complete."
