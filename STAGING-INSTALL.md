# FastPhunzira flat staging deployment

Upload the **contents** of this package directly into
`staging-fastphunzira.lovestoblog.com/htdocs/`. Do not upload this package as
a nested directory.

The resulting `htdocs/` directory must contain `index.php`, `.htaccess`,
`app/`, `bootstrap/`, `config/`, `resources/`, `routes/`, `storage/`, and
`vendor/` at the same level.

## Security

Keep `.htaccess` in `htdocs/`. It blocks direct web requests to source,
configuration, dependencies, storage, and `.env`; do not remove those rules.
Only the application front controller and authenticated media routes should
serve application data.

## Database

Import `database/migrations/*.sql` through InfinityFree phpMyAdmin in ascending
filename order. Do not overwrite existing tables without a confirmed backup.

## Verification

After upload, first open the HTTPS staging URL. Then complete the registration,
login, enrollment, lesson, quiz, and exam smoke tests before enabling payments.
