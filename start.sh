#!/bin/bash
set -e

MYSQL_DATA="/home/runner/mysql-data"
MYSQL_RUN="/home/runner/mysql-run"
MYSQL_SOCK="$MYSQL_RUN/mysql.sock"
MYSQL_PID="$MYSQL_RUN/mysql.pid"
MYSQL_LOG="$MYSQL_DATA/mysql.err"

mkdir -p "$MYSQL_DATA" "$MYSQL_RUN"

# Kill any stale mysqld processes
pkill -f mysqld 2>/dev/null || true
sleep 1
rm -f "$MYSQL_SOCK" "$MYSQL_SOCK.lock" "$MYSQL_PID" 2>/dev/null || true

# Initialize data dir if not already done
if [ ! -d "$MYSQL_DATA/mysql" ]; then
    echo "[start.sh] Initializing MySQL data directory..."
    mysqld --initialize-insecure \
        --datadir="$MYSQL_DATA" \
        --user=runner 2>&1
    echo "[start.sh] MySQL initialized."
fi

# Start MySQL in the background
echo "[start.sh] Starting MySQL..."
mysqld \
    --datadir="$MYSQL_DATA" \
    --socket="$MYSQL_SOCK" \
    --pid-file="$MYSQL_PID" \
    --port=3306 \
    --user=runner \
    --mysqlx=OFF \
    --log-error="$MYSQL_LOG" \
    --bind-address=127.0.0.1 &

MYSQL_BG_PID=$!
echo "[start.sh] MySQL background PID: $MYSQL_BG_PID"

# Wait for MySQL to be ready (up to 30s)
echo "[start.sh] Waiting for MySQL to be ready..."
for i in $(seq 1 30); do
    if mysqladmin -u root --socket="$MYSQL_SOCK" ping --silent 2>/dev/null; then
        echo "[start.sh] MySQL is ready after ${i}s."
        break
    fi
    sleep 1
done

# Set up database, user, and schema if not already done
mysql -u root --socket="$MYSQL_SOCK" <<'SQL'
CREATE DATABASE IF NOT EXISTS logistics_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'logistics_user'@'localhost' IDENTIFIED BY 'logistics_pass_2024';
GRANT ALL PRIVILEGES ON logistics_db.* TO 'logistics_user'@'localhost';
FLUSH PRIVILEGES;
SQL

echo "[start.sh] Database ready."

# Run the PHP install/migration script to set up tables
echo "[start.sh] Running database schema installer..."
php install-database.php 2>&1 || echo "[start.sh] Warning: install-database.php had issues (may be already installed)"

echo "[start.sh] Starting PHP built-in server on 0.0.0.0:5000..."
exec php -S 0.0.0.0:5000 -t . router.php
