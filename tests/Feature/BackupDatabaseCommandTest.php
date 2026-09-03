<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;

test('backup database command runs mysqldump without exposing the password in its arguments', function () {
    $originalDefault = config('database.default');
    $originalMysql = config('database.connections.mysql');
    config()->set('database.default', 'mysql');
    config()->set('database.connections.mysql', [
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'pos_test',
        'username' => 'backup_user',
        'password' => 'secret-password',
    ]);
    config()->set('backup.path', storage_path('framework/testing-backups'));
    Process::fake();

    try {
        Artisan::call('backup:database');

        expect(Artisan::output())->toContain('Backup database berhasil dibuat');
        Process::assertRan(function ($process) {
            return str_contains($process->command[0], 'mysqldump')
                && in_array('--user=backup_user', $process->command, true)
                && ! in_array('secret-password', $process->command, true);
        });
    } finally {
        config()->set('database.default', $originalDefault);
        config()->set('database.connections.mysql', $originalMysql);
    }
});

test('backup database command rejects a non MySQL connection', function () {
    $originalDefault = config('database.default');
    $originalSqlite = config('database.connections.sqlite');
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite', ['driver' => 'sqlite']);

    try {
        $exitCode = Artisan::call('backup:database');

        expect($exitCode)->toBe(1);
        expect(Artisan::output())->toContain('Backup hanya mendukung koneksi MySQL atau MariaDB');
    } finally {
        config()->set('database.default', $originalDefault);
        config()->set('database.connections.sqlite', $originalSqlite);
    }
});
