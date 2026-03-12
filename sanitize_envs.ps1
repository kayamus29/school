$portals = @(
    @{ Path = "c:\Users\kaygo\Desktop\livecpanel\schools\bs_abuja\.env"; URL = "http://abuja.bestsolution.ng" },
    @{ Path = "c:\Users\kaygo\Desktop\livecpanel\schools\bs_kaduna\.env"; URL = "http://kaduna.bestsolution.ng" },
    @{ Path = "c:\Users\kaygo\Desktop\livecpanel\schools\bs_kd\.env"; URL = "http://kd.bestsolution.ng" }
)

foreach ($port in $portals) {
    Write-Host "Cleaning up .env for $($port.Path) ..."
    $content = Get-Content $port.Path
    
    # Fix ASSET_URL to match the new school domain
    $content = $content -replace '^ASSET_URL=.*', "ASSET_URL=$($port.URL)"
    
    # Ensure APP_ENV is production for the new portals
    $content = $content -replace '^APP_ENV=.*', "APP_ENV=production"
    
    $content | Set-Content $port.Path
}
Write-Host "Cleanup Complete."
