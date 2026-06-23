# Run as Administrator (right-click PowerShell -> Run as administrator):
#   powershell -ExecutionPolicy Bypass -File "D:\citizens-portal\devtools\add-citizens-id-hosts.ps1"

$ErrorActionPreference = 'Stop'
$hostsPath = "$env:SystemRoot\System32\drivers\etc\hosts"
$aliases = @('citizens-id.test', 'www.citizens-id.test')

$lines = [string[]](Get-Content -Path $hostsPath)
$updated = $false

for ($i = 0; $i -lt $lines.Count; $i++) {
    if ($lines[$i] -match '^\s*127\.0\.0\.1\s+.*citizens-portal\.test') {
        foreach ($alias in $aliases) {
            if ($lines[$i] -notmatch [regex]::Escape($alias)) {
                $lines[$i] = $lines[$i].TrimEnd() + "`t$alias"
                $updated = $true
                Write-Host "Added alias $alias to citizens-portal hosts line"
            }
        }
        break
    }
}

if (-not $updated) {
    Write-Host 'citizens-portal.test line not found or aliases already present.'
} else {
    $utf8NoBom = New-Object System.Text.UTF8Encoding $false
    [System.IO.File]::WriteAllLines($hostsPath, $lines, $utf8NoBom)
    Write-Host 'Hosts file updated.'
}

ipconfig /flushdns | Out-Null
Write-Host 'DNS cache flushed. Test: ping citizens-id.test'
