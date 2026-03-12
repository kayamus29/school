$portals = @(
    @{ Path = "c:\Users\kaygo\Desktop\livecpanel\schools\bs_abuja"; Name = "Best Solution Abuja" },
    @{ Path = "c:\Users\kaygo\Desktop\livecpanel\schools\bs_kaduna"; Name = "Best Solution Kaduna" },
    @{ Path = "c:\Users\kaygo\Desktop\livecpanel\schools\bs_kd"; Name = "Best Solution KD" }
)

foreach ($port in $portals) {
    Write-Host "Updating School Name for $($port.Path) to $($port.Name) ..."
    Set-Location $port.Path
    
    # Use artisan tinker or a direct DB call to update the settings
    # Assuming ID 1 exists because of seeder
    $phpCode = "App\Models\AcademicSetting::updateOrCreate(['id' => 1], ['school_name' => '$($port.Name)']);"
    php artisan tinker --execute="$phpCode"
}

Write-Host "Customization Complete."
