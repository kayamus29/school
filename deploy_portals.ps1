$source = "c:\Users\kaygo\Desktop\livecpanel\schools\mienebischool"
$destinations = @(
    "c:\Users\kaygo\Desktop\livecpanel\schools\bs_abuja",
    "c:\Users\kaygo\Desktop\livecpanel\schools\bs_kaduna",
    "c:\Users\kaygo\Desktop\livecpanel\schools\bs_kd"
)

foreach ($dest in $destinations) {
    Write-Host "Cloning to $dest ..."
    # /MIR = Mirror (Clone)
    # /XD = Exclude Directories (.git, node_modules)
    # /XF = Exclude Files (.env)
    # /NFL /NDL = No File List / No Dir List (Quiet output)
    # /R:0 /W:0 = No retries (Fail fast)
    robocopy $source $dest /MIR /XD .git node_modules /XF .env /NFL /NDL /R:0 /W:0
}
Write-Host "Deployment Complete."
