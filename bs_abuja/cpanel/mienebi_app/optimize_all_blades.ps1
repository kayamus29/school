# Blade Optimization Script - Comprehensive
# Applies standardization patterns to all Blade views

$viewsPath = "c:\Users\kaygo\Desktop\Unifiedtransform\resources\views"
$logFile = "blade_optimization_log.txt"

# Patterns to fix
$patterns = @(
    # Fix invalid Bootstrap classes
    @{
        Pattern = 'class="([^"]*)\bcol-(\d+)-(xs|sm|md|lg|xl)\b([^"]*)"'
        Replacement = 'class="$1col-$3-$2$4"'
        Description = "Fix invalid Bootstrap grid classes (col-3-md -> col-md-3)"
    },
    
    # Fix table-responsive as table class
    @{
        Pattern = '<table class="([^"]*)table-responsive([^"]*)"'
        Replacement = '<div class="table-responsive"><table class="$1$2"'
        Description = "Fix table-responsive applied as table class"
    },
    
    # Ensure main content div has standard grid
    @{
        Pattern = '<div class="col-xs-\d+ col-sm-\d+ col-md-\d+ col-lg-\d+">'
        Replacement = '<div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">'
        Description = "Standardize main content grid"
    }
)

# Get all Blade files
$bladeFiles = Get-ChildItem -Path $viewsPath -Filter "*.blade.php" -Recurse

Write-Host "Found $($bladeFiles.Count) Blade files to process..."
"Blade Optimization Log - $(Get-Date)" | Out-File $logFile

$filesModified = 0
$totalChanges = 0

foreach ($file in $bladeFiles) {
    $content = Get-Content $file.FullName -Raw
    $originalContent = $content
    $fileChanges = 0
    
    foreach ($pattern in $patterns) {
        $matches = [regex]::Matches($content, $pattern.Pattern)
        if ($matches.Count -gt 0) {
            $content = $content -replace $pattern.Pattern, $pattern.Replacement
            $fileChanges += $matches.Count
            "  - $($pattern.Description): $($matches.Count) changes" | Out-File $logFile -Append
        }
    }
    
    if ($content -ne $originalContent) {
        Set-Content -Path $file.FullName -Value $content -NoNewline
        $filesModified++
        $totalChanges += $fileChanges
        
        $relativePath = $file.FullName.Replace($viewsPath, "").TrimStart('\')
        Write-Host "✓ Modified: $relativePath ($fileChanges changes)"
        "Modified: $relativePath ($fileChanges changes)" | Out-File $logFile -Append
    }
}

Write-Host "`n=== Summary ===" -ForegroundColor Green
Write-Host "Files processed: $($bladeFiles.Count)"
Write-Host "Files modified: $filesModified"
Write-Host "Total changes: $totalChanges"
Write-Host "Log saved to: $logFile"

"" | Out-File $logFile -Append
"=== Summary ===" | Out-File $logFile -Append
"Files processed: $($bladeFiles.Count)" | Out-File $logFile -Append
"Files modified: $filesModified" | Out-File $logFile -Append
"Total changes: $totalChanges" | Out-File $logFile -Append
