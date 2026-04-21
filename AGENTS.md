# AGENTS.md

## Commands
```bash
composer run dev      # Server, queue worker, and Vite (named: server,queue,vite) with --kill-others
npm run dev           # Vite dev server only
npm run build         # Build assets for production
composer run setup    # Install deps, create .env, generate key, migrate, build assets
composer run test     # Clear config cache then run tests
vendor/bin/pint       # Auto-fix PHP formatting (run before commits)
```

## Testing
- Single test: `php artisan test --filter=TestClassOrMethod`
- File: `php artisan test tests/Feature/ExampleTest.php`
- Test env: SQLite in-memory, sync queue, array cache/mail/session

## Stack
- Laravel 12, PHP ^8.2
- Vite 7 + Tailwind CSS 4 + Axios
- `vite.config.js` ignores `storage/framework/views/` for hot reload

## App: FTP Cloud Manager
Manages FTP sources ("nubes") with file browser.
- `app/Models/User.php` — `role` column (admin/user)
- `app/Models/Nube.php` — FTP connection config
- `app/Services/FtpService.php` — core FTP ops
- `routes/user.php` — all real routes (auth, user CRUD, nube CRUD, FTP browse/upload/download)
- `routes/web.php` — minimal redirect only

## Downloads
Downloads are synchronous (direct), no queue worker required.
- `POST /nubes/{nube}/download-sync` — download file or folder (ZIP)
- `GET /download-progress?jobId=...` — poll progress

## Constraints
- `.opencode/rules/slow-agent.md`: max 3 reads or 2 writes per turn, sequential work
