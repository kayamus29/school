$distPath = "c:\Users\kaygo\Desktop\livecpanel\schools\bestsolution_main\dist"
$portals = @(
    "c:\Users\kaygo\Desktop\livecpanel\schools\bs_abuja\public",
    "c:\Users\kaygo\Desktop\livecpanel\schools\bs_kaduna\public",
    "c:\Users\kaygo\Desktop\livecpanel\schools\bs_kd\public"
)

foreach ($p in $portals) {
    Write-Host "Deploying website assets to $p ..."
    robocopy $distPath $p /E /V /R:0 /W:0
}

Write-Host "Deployment Complete."
