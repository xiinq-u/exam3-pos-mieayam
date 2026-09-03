<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database';

    protected $description = 'Membuat backup database MySQL di storage/app/backups';

    public function handle(): int
    {
        $connection = config('database.connections.'.config('database.default'));

        if (! in_array($connection['driver'] ?? null, ['mysql', 'mariadb'], true)) {
            $this->error('Backup hanya mendukung koneksi MySQL atau MariaDB.');

            return self::FAILURE;
        }

        $path = config('backup.path');
        File::ensureDirectoryExists($path);
        $filename = 'database-'.now()->format('Ymd-His').'.sql';
        $backupPath = $path.DIRECTORY_SEPARATOR.$filename;

        $command = [
            $this->mysqldumpBinary(),
            '--host='.$connection['host'],
            '--port='.(string) $connection['port'],
            '--user='.$connection['username'],
            '--single-transaction',
            '--routines',
            '--events',
            '--result-file='.$backupPath,
            $connection['database'],
        ];

        $result = Process::timeout(300)
            ->env(['MYSQL_PWD' => (string) $connection['password']])
            ->run($command);

        if ($result->failed()) {
            $this->error('Backup database gagal: '.$result->errorOutput());

            return self::FAILURE;
        }

        $this->info('Backup database berhasil dibuat: '.$filename);

        return self::SUCCESS;
    }

    private function mysqldumpBinary(): string
    {
        $binary = config('backup.mysqldump_binary');
        $xamppBinary = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';

        if ($binary === 'mysqldump' && PHP_OS_FAMILY === 'Windows' && File::exists($xamppBinary)) {
            return $xamppBinary;
        }

        return $binary;
    }
}
