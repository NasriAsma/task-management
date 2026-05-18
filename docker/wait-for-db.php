<?php
$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$db   = getenv('DB_DATABASE') ?: 'forge';
$user = getenv('DB_USERNAME') ?: 'forge';
$pass = getenv('DB_PASSWORD') ?: '';
$tries = 0;
$maxTries = 60;
$waitSeconds = 2;

echo "Waiting for database at {$host}:{$port} (db={$db})\n";

while (true) {
    try {
        $pdo = new PDO("mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ]);
        echo "Database connection successful.\n";
        
        // Extra wait to ensure MySQL is fully ready (InnoDb initialization, etc)
        sleep(3);
        
        echo "Database is ready for migrations.\n";
        exit(0);
    } catch (Throwable $e) {
        $tries++;
        fwrite(STDOUT, "DB unavailable (attempt {$tries}/{$maxTries}): {$e->getMessage()}\n");
        if ($tries >= $maxTries) {
            fwrite(STDERR, "Reached max retries waiting for database.\n");
            exit(1);
        }
        sleep($waitSeconds);
    }
}
