# Backup script for Windows (PowerShell)

# backup/backup.ps1
# Usage: .\backup.ps1
$db = 'pos_enterprise'
$user = 'root'
$pass = ''
$out = Join-Path (Split-Path -Parent $MyInvocation.MyCommand.Definition) '..\storage\backups'
if(-not (Test-Path $out)) { New-Item -ItemType Directory -Path $out | Out-Null }
$now = Get-Date -Format 'yyyyMMddTHHmmss'
$file = Join-Path $out "${db}_$now.sql"
& mysqldump -u $user -p$pass $db > $file
if(Test-Path $file) { Write-Host "Backup saved to $file" } else { Write-Host 'Backup failed' }
