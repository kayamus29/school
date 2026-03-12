# Blade Optimization Script - Simplified
$viewsPath = "c:\Users\kaygo\Desktop\Unifiedtransform\resources\views"
$logFile = "blade_optimization_log.txt"

Write-Host "Starting Blade optimization..."
"Blade Optimization Log - $(Get-Date)" | Out-File $logFile

$bladeFiles = Get-ChildItem -Path $viewsPath -Filter "*.blade.php" -Recurse
Write-Host "Found $($bladeFiles.Count) Blade files"

$filesModified = 0
$totalChanges = 0

foreach ($file in $bladeFiles) {
    $content = Get-Content $file.FullName -Raw -Encoding UTF8
    $originalContent = $content
    $fileChanges = 0
    
    # Fix: col-3-md -> col-md-3
    $before = $content
    $content = $content -replace 'class="([^"]*)\bcol-(\d+)-(xs|sm|md|lg|xl)\b([^"]*)"', 'class="$1col-$3-$2$4"'
    if ($content -ne $before) {
        $fileChanges++
    }
    
    # Fix: table class="table-responsive" -> div wrapper
    $before = $content
    $content = $content -replace '<table class="([^"]*)table-responsive([^"]*)"', '<div class="table-responsive"><table class="$1$2"'
    if ($content -ne $before) {
        $fileChanges++
    }
    
    if ($content -ne $originalContent) {
        Set-Content -Path $file.FullName -Value $content -NoNewline -Encoding UTF8
        $filesModified++
        $totalChanges += $fileChanges
        
        $relativePath = $file.FullName.Replace($viewsPath + "\", "")
        Write-Host "Modified: $relativePath"
        "Modified: $relativePath" | Out-File $logFile -Append
    }
}

Write-Host ""
Write-Host "=== Summary ==="
Write-Host "Files processed: $($bladeFiles.Count)"
Write-Host "Files modified: $filesModified"
Write-Host "Total changes: $totalChanges"

"" | Out-File $logFile -Append
"=== Summary ===" | Out-File $logFile -Append
"Files processed: $($bladeFiles.Count)" | Out-File $logFile -Append
"Files modified: $filesModified" | Out-File $logFile -Append
"Total changes: $totalChanges" | Out-File $logFile -Append
