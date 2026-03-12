$sourceEnv = "c:\Users\kaygo\Desktop\livecpanel\schools\mienebischool\.env"
$configs = @(
    @{ Path = "c:\Users\kaygo\Desktop\livecpanel\schools\bs_abuja\.env"; Name = "Best Solution Abuja"; URL = "http://abuja.bestsolution.ng"; DB = "bs_abuja_db" },
    @{ Path = "c:\Users\kaygo\Desktop\livecpanel\schools\bs_kaduna\.env"; Name = "Best Solution Kaduna"; URL = "http://kaduna.bestsolution.ng"; DB = "bs_kaduna_db" },
    @{ Path = "c:\Users\kaygo\Desktop\livecpanel\schools\bs_kd\.env"; Name = "Best Solution KD"; URL = "http://kd.bestsolution.ng"; DB = "bs_kd_db" }
)

foreach ($cnf in $configs) {
    Write-Host "Configuring $($cnf.Path) ..."
    Copy-Item $sourceEnv $cnf.Path -Force
    
    $content = Get-Content $cnf.Path
    $content = $content -replace '^APP_NAME=.*', "APP_NAME=""$($cnf.Name)"""
    $content = $content -replace '^APP_URL=.*', "APP_URL=$($cnf.URL)"
    $content = $content -replace '^DB_DATABASE=.*', "DB_DATABASE=$($cnf.DB)"
    
    $content | Set-Content $cnf.Path
}
Write-Host "Configuration Complete."
