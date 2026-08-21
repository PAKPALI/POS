# POS Preview — Run Doc

## Prerequisites
- PHP 8.2.24 at `D:\laragon\bin\php\php-8.2.24-Win32-vs16-x64\php.exe`
- Node.js v20 (nvm4w)
- MySQL via Laragon (`pos_testing` or `pos` database)
- `.env` configured (copy from main checkout)

## Reproduce Uncommitted Artifacts
1. Copy `.env` from the main checkout if missing
2. Run `composer install` (vendor/ already present)
3. Run `npm install` (node_modules/ already present)
4. Run `php artisan migrate` to ensure database is up to date

## How to Run the Server
### 1. Laravel PHP server (port 8000)
```powershell
D:\laragon\bin\php\php-8.2.24-Win32-vs16-x64\php.exe artisan serve --host=127.0.0.1 --port=8000
```
Or use the PowerShell script: `.freebuff\start-server.ps1`

### 2. Vite dev server (port 5173)
```bash
npm run dev
```
Or use the PowerShell script: `.freebuff\start-vite.ps1`

### Logs
- PHP server: `.freebuff\preview.log` / `.freebuff\preview.log.err`
- Vite: `.freebuff\vite.log` / `.freebuff\vite.log.err`
