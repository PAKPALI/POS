$log = "C:\POS\.freebuff\vite.log"
$logErr = "C:\POS\.freebuff\vite.log.err"
$workDir = "C:\POS"

$p = Start-Process -FilePath 'C:\nvm4w\nodejs\npm.cmd' -ArgumentList 'run','dev' -WorkingDirectory $workDir -RedirectStandardOutput $log -RedirectStandardError $logErr -WindowStyle Hidden -PassThru
Write-Output $p.Id
