#!/bin/sh
set -e

echo "[Docker Entrypoint] Memulai inisialisasi lingkungan E-Management..."

# 1. Pastikan direktori krusial tersedia pada volume storage
mkdir -p \
    /var/www/html/storage/fonts \
    /var/www/html/storage/framework/cache/data \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/logs \
    /var/www/html/storage/app/public \
    /var/www/html/storage/app/private/izins \
    /var/www/html/bootstrap/cache

# 2. Sinkronisasi kepemilikan dan hak akses (mengatasi UID clash pada Debian VPS)
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache || true

# 3. Tunggu Database MySQL siap menerima koneksi (readiness check)
if [ -n "$DB_HOST" ] && [ "$DB_CONNECTION" = "mysql" ]; then
    echo "[Docker Entrypoint] Memeriksa kesiapan database di $DB_HOST:${DB_PORT:-3306}..."
    max_retries=30
    count=0
    until php -r "
        try {
            \$pdo = new PDO(
                'mysql:host='.getenv('DB_HOST').';port='.(getenv('DB_PORT') ?: 3306).';dbname='.getenv('DB_DATABASE'),
                getenv('DB_USERNAME'),
                getenv('DB_PASSWORD'),
                [PDO::ATTR_TIMEOUT => 2, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            exit(0);
        } catch (Throwable \$e) {
            exit(1);
        }
    " > /dev/null 2>&1; do
        count=$((count+1))
        if [ $count -ge $max_retries ]; then
            echo "[Docker Entrypoint] PERINGATAN: Database belum siap setelah $max_retries percobaan. Melanjutkan..."
            break
        fi
        echo "[Docker Entrypoint] Database belum siap, mencoba kembali ($count/$max_retries)..."
        sleep 2
    done
    echo "[Docker Entrypoint] Koneksi Database MySQL berhasil diverifikasi."
fi

# 4. Inisialisasi spesifik untuk layanan Web/App Utama (php-fpm)
if [ "$1" = "php-fpm" ]; then
    echo "[Docker Entrypoint] Menjalankan tugas pemeliharaan aplikasi..."
    
    # Symlink storage publik jika belum ada
    php artisan storage:link --force || true

    # Jalankan migrasi otomatis jika diaktifkan via environment variable
    if [ "$RUN_MIGRATIONS" = "true" ]; then
        echo "[Docker Entrypoint] Menjalankan migrasi database (RUN_MIGRATIONS=true)..."
        php artisan migrate --force || true
    fi

    # Warming cache Laravel 13 untuk performa production
    if [ "$APP_ENV" = "production" ]; then
        echo "[Docker Entrypoint] Mengoptimasi cache Laravel 13 (config, route, view)..."
        php artisan config:cache || true
        php artisan route:cache || true
        php artisan view:cache || true
    fi
fi

echo "[Docker Entrypoint] Inisialisasi selesai. Menjalankan perintah: $@"
exec "$@"
