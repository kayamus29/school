$source = "C:\Users\kaygo\Desktop\Unifiedtransform"
$destination = "C:\Users\kaygo\Desktop\livecpanel\schools\mienebischool"
$excludedDirs = @(".git", "vendor", "node_modules", "storage", "bootstrap\cache", "frontend_source")

echo "Source: $source"
echo "Destination: $destination"
echo "Scanning for missing or modified files..."
echo "Excluded: $($excludedDirs -join ', ')"

function Should-Exclude ($path) {
    foreach ($dir in $excludedDirs) {
        if ($path -match "\\$dir\\" -or $path -match "\\$dir$") { return $true }
    }
    return $false
}

$files = Get-ChildItem -Path $source -Recurse -File

foreach ($file in $files) {
    $relativePath = $file.FullName.Substring($source.Length)
    
    if (Should-Exclude $relativePath) { continue }

    $destPath = "$destination$relativePath"
    
    if (-not (Test-Path $destPath)) {
        echo "MISSING: $relativePath"
    } else {
        $destFile = Get-Item $destPath
        if ($file.Length -ne $destFile.Length) {
           echo "DIFF-SIZE: $relativePath (Source: $($file.Length) vs Dest: $($destFile.Length))"
        }
    }
}
echo "Scan complete."
