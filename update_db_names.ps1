$configs = @(
    @{ Path = "c:\Users\kaygo\Desktop\livecpanel\schools\bs_abuja\.env"; DB = "mienebis_bs_abuja_db" },
    @{ Path = "c:\Users\kaygo\Desktop\livecpanel\schools\bs_kaduna\.env"; DB = "mienebis_bs_kaduna_db" },
    @{ Path = "c:\Users\kaygo\Desktop\livecpanel\schools\bs_kd\.env"; DB = "mienebis_bs_kd_db" }
)

foreach ($cnf in $configs) {
    Write-Host "Updating database for $($cnf.Path) to $($cnf.DB)..."
    $content = Get-Content $cnf.Path
    $content = $content -replace '^DB_DATABASE=.*', "DB_DATABASE=$($cnf.DB)"
    $content | Set-Content $cnf.Path
}
Write-Host "Database Configuration Updated."
