$php = "D:\laragon\bin\php\php-8.2.24-Win32-vs16-x64\php.exe"
$log = "C:\POS\.freebuff\preview.log"
$logErr = "C:\POS\.freebuff\preview.log.err"
$workDir = "C:\POS"

$p = Start-Process -FilePath $php -ArgumentList 'artisan','serve','--host=127.0.0.1','--port=8000' -WorkingDirectory $workDir -RedirectStandardOutput $log -RedirectStandardError $logErr -WindowStyle Hidden -PassThru
Write-Output $p.Id
