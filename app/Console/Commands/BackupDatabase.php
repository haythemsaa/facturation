<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:run {--database : Backup database only} {--storage : Backup storage files only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create backup of database and/or storage files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting backup process...');

        $backupDatabase = $this->option('database') || (!$this->option('database') && !$this->option('storage'));
        $backupStorage = $this->option('storage') || (!$this->option('database') && !$this->option('storage'));

        $backupPath = storage_path('app/backups/' . date('Y-m-d_H-i-s'));

        if (!file_exists($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        if ($backupDatabase) {
            $this->backupDatabase($backupPath);
        }

        if ($backupStorage) {
            $this->backupStorage($backupPath);
        }

        $this->info('Backup completed successfully!');
        $this->info('Backup location: ' . $backupPath);

        // Clean old backups (keep last 30 days)
        $this->cleanOldBackups();

        return Command::SUCCESS;
    }

    /**
     * Backup database.
     */
    protected function backupDatabase(string $backupPath): void
    {
        $this->info('Backing up database...');

        $database = config('database.connections.pgsql.database');
        $username = config('database.connections.pgsql.username');
        $password = config('database.connections.pgsql.password');
        $host = config('database.connections.pgsql.host');
        $port = config('database.connections.pgsql.port', 5432);

        $filename = $backupPath . '/database.sql';

        // PostgreSQL dump command
        $command = sprintf(
            'PGPASSWORD=%s pg_dump -h %s -p %s -U %s -F p -b -v -f %s %s 2>&1',
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($filename),
            escapeshellarg($database)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            $this->error('Database backup failed!');
            $this->error(implode("\n", $output));
        } else {
            // Compress the SQL file
            exec("gzip -f $filename");
            $this->info('Database backed up successfully: database.sql.gz');
        }
    }

    /**
     * Backup storage files.
     */
    protected function backupStorage(string $backupPath): void
    {
        $this->info('Backing up storage files...');

        $storagePath = storage_path('app');
        $filename = $backupPath . '/storage.tar.gz';

        // Create tar.gz archive
        $command = sprintf(
            'tar -czf %s -C %s .',
            escapeshellarg($filename),
            escapeshellarg($storagePath)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            $this->error('Storage backup failed!');
        } else {
            $size = $this->formatBytes(filesize($filename));
            $this->info("Storage backed up successfully: storage.tar.gz ($size)");
        }
    }

    /**
     * Clean old backups (older than 30 days).
     */
    protected function cleanOldBackups(): void
    {
        $this->info('Cleaning old backups...');

        $backupsDir = storage_path('app/backups');

        if (!is_dir($backupsDir)) {
            return;
        }

        $directories = glob($backupsDir . '/*', GLOB_ONLYDIR);
        $thirtyDaysAgo = strtotime('-30 days');

        $cleaned = 0;

        foreach ($directories as $dir) {
            $dirTime = filemtime($dir);

            if ($dirTime < $thirtyDaysAgo) {
                $this->deleteDirectory($dir);
                $cleaned++;
            }
        }

        if ($cleaned > 0) {
            $this->info("Cleaned $cleaned old backup(s).");
        } else {
            $this->info('No old backups to clean.');
        }
    }

    /**
     * Delete directory recursively.
     */
    protected function deleteDirectory(string $dir): bool
    {
        if (!is_dir($dir)) {
            return false;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . '/' . $file;

            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }

        return rmdir($dir);
    }

    /**
     * Format bytes to human readable.
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
