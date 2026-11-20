<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PDO;
use Exception;

class SetupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:setup {--fresh : Drop existing database and recreate from scratch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically creates the MySQL database, imports campus navigation data, and runs migrations.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $connection = config('database.default', 'mysql');
        $dbConfig = config("database.connections.{$connection}");

        if ($connection !== 'mysql') {
            $this->info("Current database connection is [{$connection}]. Running standard migrations...");
            $this->call('migrate', ['--force' => true]);
            return Command::SUCCESS;
        }

        $host = $dbConfig['host'] ?? '127.0.0.1';
        $port = $dbConfig['port'] ?? '3306';
        $database = $dbConfig['database'] ?? 'campus_navigation_db';
        $username = $dbConfig['username'] ?? 'root';
        $password = $dbConfig['password'] ?? '';

        $this->info("Connecting to MySQL server at {$host}:{$port} as '{$username}'...");

        try {
            // 1. Connect to MySQL server without selecting a database
            $pdo = new PDO(
                "mysql:host={$host};port={$port}",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]
            );

            // If --fresh is passed, drop the database first
            if ($this->option('fresh')) {
                $this->warn("Option --fresh detected: dropping database `{$database}` if it exists...");
                $pdo->exec("DROP DATABASE IF EXISTS `{$database}`;");
            }

            // 2. Automatically create database if it doesn't exist
            $this->info("Ensuring database `{$database}` exists...");
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            $this->info("Database `{$database}` is ready.");

            // 3. Connect to the specific database with multi-query support
            $dbPdo = new PDO(
                "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
                ]
            );

            // 4. Run Laravel migrations (tables + campus navigation schema)
            $this->info("Running Laravel migrations...");
            $this->call('migrate', ['--force' => true]);

            // 5. Seed the database with campus navigation data
            $this->info("Seeding campus navigation data...");
            $this->call('db:seed', ['--force' => true]);

            $this->newLine();
            $this->info("Database setup finished successfully! You can now run the app.");
            return Command::SUCCESS;

        } catch (Exception $e) {
            $this->error("Database setup failed: " . $e->getMessage());
            $this->line("Ensure MySQL is running (e.g. in XAMPP or Laragon) and credentials in .env are correct.");
            return Command::FAILURE;
        }
    }
}
