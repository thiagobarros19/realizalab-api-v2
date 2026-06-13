<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportLegacyDataCommand extends Command
{
    protected $signature = 'app:import-legacy {--path=dumps/dump.sql : Relative path to the SQL dump file} {--force : Overwrite existing data by truncating all tables first} {--password=@123Mudar : Default password to assign to all imported users}';

    protected $description = 'Import data from a legacy SQL dump into the current database';

    /** @var array<string, array<string>> */
    private const COLUMNS = [
        'users' => ['id', 'name', 'email', 'phone', 'birthday', 'email_verified_at', 'password', 'remember_token', 'created_at', 'updated_at', 'deleted_at'],
        'partners' => ['id', 'name', 'created_at', 'updated_at', 'deleted_at'],
        'patients' => ['id', 'name', 'document', 'email', 'phone', 'birthday', 'observations', 'created_at', 'updated_at', 'deleted_at'],
        'exams' => ['id', 'name', 'code', 'cost', 'price_sus', 'price_particular', 'partner_id', 'created_at', 'updated_at', 'deleted_at'],
        'orders' => ['id', 'order_type', 'patient_id', 'created_at', 'updated_at', 'deleted_at'],
        'orders_exams' => ['id', 'order_id', 'exam_id', 'exam_name', 'exam_price', 'created_at', 'updated_at', 'deleted_at'],
    ];

    /** @var array<int, string> */
    private array $partnerMap = [];

    /** @var array<int, string> */
    private array $patientMap = [];

    /** @var array<int, string> */
    private array $examMap = [];

    /** @var array<int, string> */
    private array $orderMap = [];

    public function handle(): int
    {
        $path = base_path($this->option('path'));

        if (! file_exists($path)) {
            $this->error("Dump file not found at: {$path}");

            return self::FAILURE;
        }

        if ($this->databaseHasData()) {
            if (! $this->option('force')) {
                $this->error('Database already contains imported data. Run with --force to truncate and re-import.');

                return self::FAILURE;
            }

            $this->warn('--force detected. Truncating tables before import...');
            $this->truncateTables();
            $this->newLine();
        }

        $this->info("Reading dump from: {$path}");
        $this->newLine();

        DB::transaction(function () use ($path): void {
            $this->importUsers($path);
            $this->importPartners($path);
            $this->importPatients($path);
            $this->importExams($path);
            $this->importOrders($path);
            $this->importOrderExams($path);
        });

        $this->newLine();
        $this->info('Import completed successfully.');

        return self::SUCCESS;
    }

    private function databaseHasData(): bool
    {
        return DB::table('users')->exists()
            || DB::table('partners')->exists()
            || DB::table('patients')->exists()
            || DB::table('exams')->exists()
            || DB::table('orders')->exists()
            || DB::table('order_exams')->exists();
    }

    private function truncateTables(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach (['order_exams', 'orders', 'exams', 'patients', 'partners', 'users'] as $table) {
            DB::table($table)->truncate();
            $this->line("  Truncated <fg=yellow>{$table}</>");
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function importUsers(string $path): void
    {
        $this->line('<fg=cyan>Importing users...</>');
        $inserts = [];

        foreach ($this->streamRows($path, 'users') as $row) {
            if ($row['deleted_at'] !== null) {
                continue;
            }

            $inserts[] = [
                'id' => strtolower(Str::ulid()),
                'name' => $row['name'],
                'email' => $row['email'],
                'phone' => $row['phone'],
                'birthday' => $row['birthday'],
                'email_verified_at' => $row['email_verified_at'],
                'password' => bcrypt((string) $this->option('password')),
                'remember_token' => $row['remember_token'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
                'deleted_at' => null,
            ];
        }

        DB::table('users')->insert($inserts);
        $this->line('  <fg=green>✓</> '.count($inserts).' users imported.');
    }

    private function importPartners(string $path): void
    {
        $this->line('<fg=cyan>Importing partners...</>');
        $inserts = [];

        foreach ($this->streamRows($path, 'partners') as $row) {
            if ($row['deleted_at'] !== null) {
                continue;
            }

            $ulid = strtolower(Str::ulid());
            $this->partnerMap[(int) $row['id']] = $ulid;

            $inserts[] = [
                'id' => $ulid,
                'name' => $row['name'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
                'deleted_at' => null,
            ];
        }

        DB::table('partners')->insert($inserts);
        $this->line('  <fg=green>✓</> '.count($inserts).' partners imported.');
    }

    private function importPatients(string $path): void
    {
        $this->line('<fg=cyan>Importing patients...</>');
        $inserts = [];

        foreach ($this->streamRows($path, 'patients') as $row) {
            if ($row['deleted_at'] !== null) {
                continue;
            }

            $ulid = strtolower(Str::ulid());
            $this->patientMap[(int) $row['id']] = $ulid;

            $inserts[] = [
                'id' => $ulid,
                'name' => $row['name'],
                'document' => $row['document'],
                'email' => $row['email'],
                'phone' => $row['phone'],
                'birthday' => $row['birthday'],
                'observations' => $row['observations'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
                'deleted_at' => null,
            ];
        }

        foreach (array_chunk($inserts, 200) as $chunk) {
            DB::table('patients')->insert($chunk);
        }

        $this->line('  <fg=green>✓</> '.count($inserts).' patients imported.');
    }

    private function importExams(string $path): void
    {
        $this->line('<fg=cyan>Importing exams...</>');
        $inserts = [];

        foreach ($this->streamRows($path, 'exams') as $row) {
            if ($row['deleted_at'] !== null) {
                continue;
            }

            $ulid = strtolower(Str::ulid());
            $this->examMap[(int) $row['id']] = $ulid;

            $partnerId = $row['partner_id'] !== null
                ? ($this->partnerMap[(int) $row['partner_id']] ?? null)
                : null;

            $inserts[] = [
                'id' => $ulid,
                'name' => $row['name'],
                'code' => $row['code'],
                'cost' => $row['cost'],
                'price_sus' => $row['price_sus'],
                'price_particular' => $row['price_particular'],
                'partner_id' => $partnerId,
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
                'deleted_at' => null,
            ];
        }

        foreach (array_chunk($inserts, 500) as $chunk) {
            DB::table('exams')->insert($chunk);
        }

        $this->line('  <fg=green>✓</> '.count($inserts).' exams imported.');
    }

    private function importOrders(string $path): void
    {
        $this->line('<fg=cyan>Importing orders...</>');
        $inserts = [];

        foreach ($this->streamRows($path, 'orders') as $row) {
            if ($row['deleted_at'] !== null) {
                continue;
            }

            $ulid = strtolower(Str::ulid());
            $this->orderMap[(int) $row['id']] = $ulid;

            $patientId = $row['patient_id'] !== null
                ? ($this->patientMap[(int) $row['patient_id']] ?? null)
                : null;

            $inserts[] = [
                'id' => $ulid,
                'type' => strtolower($row['order_type']), // SUS → sus, PARTICULAR → particular
                'patient_id' => $patientId,
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
                'deleted_at' => null,
            ];
        }

        foreach (array_chunk($inserts, 200) as $chunk) {
            DB::table('orders')->insert($chunk);
        }

        $this->line('  <fg=green>✓</> '.count($inserts).' orders imported.');
    }

    private function importOrderExams(string $path): void
    {
        $this->line('<fg=cyan>Importing order exams...</>');
        $inserts = [];

        foreach ($this->streamRows($path, 'orders_exams') as $row) {
            // old table had soft deletes — skip deleted rows
            if ($row['deleted_at'] !== null) {
                continue;
            }

            $orderId = $this->orderMap[(int) $row['order_id']] ?? null;
            $examId = $this->examMap[(int) $row['exam_id']] ?? null;

            if ($orderId === null || $examId === null) {
                continue; // parent was soft-deleted — skip orphan
            }

            $inserts[] = [
                'id' => strtolower(Str::ulid()),
                'order_id' => $orderId,
                'exam_id' => $examId,
                'exam_name' => $row['exam_name'],
                'exam_price' => $row['exam_price'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
            ];
        }

        foreach (array_chunk($inserts, 500) as $chunk) {
            DB::table('order_exams')->insert($chunk);
        }

        $this->line('  <fg=green>✓</> '.count($inserts).' order exams imported.');
    }

    /**
     * Stream parsed rows for a given table from the SQL dump file.
     *
     * @return \Generator<array<string, string|null>>
     */
    private function streamRows(string $path, string $table): \Generator
    {
        $columns = self::COLUMNS[$table];
        $prefix = "INSERT INTO `{$table}` VALUES ";
        $handle = fopen($path, 'r');

        while (($line = fgets($handle)) !== false) {
            $line = rtrim($line, "\r\n");

            if (! str_starts_with($line, $prefix)) {
                continue;
            }

            $valuesStr = substr($line, strlen($prefix));
            $valuesStr = rtrim($valuesStr, ';');

            foreach ($this->parseTuples($valuesStr) as $values) {
                yield array_combine($columns, $values);
            }
        }

        fclose($handle);
    }

    /**
     * Parse a MySQL VALUES string into an array of row arrays.
     * Handles quoted strings (with \' and \\ escapes), NULL, and numeric values.
     *
     * @return array<array<string|null>>
     */
    private function parseTuples(string $str): array
    {
        $rows = [];
        $row = [];
        $buf = '';
        $inStr = false;
        $depth = 0;
        $i = 0;
        $len = strlen($str);

        while ($i < $len) {
            $c = $str[$i];

            if ($inStr) {
                if ($c === '\\' && $i + 1 < $len) {
                    $next = $str[$i + 1];
                    $buf .= match ($next) {
                        'n' => "\n",
                        'r' => "\r",
                        't' => "\t",
                        default => $next,
                    };
                    $i += 2;

                    continue;
                }

                if ($c === "'") {
                    $inStr = false;
                    $i++;

                    continue;
                }

                $buf .= $c;
                $i++;

                continue;
            }

            // outside string
            if ($c === "'") {
                $inStr = true;
                $i++;

                continue;
            }

            if ($c === '(') {
                $depth++;
                $i++;

                continue;
            }

            if ($c === ')') {
                $depth--;
                $row[] = $buf === 'NULL' ? null : $buf;
                $buf = '';

                if ($depth === 0) {
                    $rows[] = $row;
                    $row = [];
                }

                $i++;

                continue;
            }

            if ($c === ',' && $depth === 1) {
                // field separator inside a row tuple
                $row[] = $buf === 'NULL' ? null : $buf;
                $buf = '';
                $i++;

                continue;
            }

            if ($c === ',' && $depth === 0) {
                // separator between row tuples — skip
                $i++;

                continue;
            }

            $buf .= $c;
            $i++;
        }

        return $rows;
    }
}
