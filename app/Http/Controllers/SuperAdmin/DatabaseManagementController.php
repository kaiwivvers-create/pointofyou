<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class DatabaseManagementController extends Controller
{
    public function index()
    {
        $backupFiles = [];
        $backupPath = storage_path('app/private/backups');
        
        if (file_exists($backupPath)) {
            $backupFiles = collect(glob($backupPath . '/*.{sql,sqlite,json}', GLOB_BRACE))
                ->map(fn ($file) => [
                    'name' => basename($file),
                    'path' => 'backups/' . basename($file),
                    'size' => filesize($file),
                    'modified' => filemtime($file),
                ])
                ->sortByDesc('modified')
                ->values();
        }

        return view('super-admin.database-management.index', compact('backupFiles'));
    }

    public function export(Request $request)
    {
        try {
            $format = $request->input('format', 'sqlite');
            $filename = 'backup_' . now()->format('Y_m_d_His') . '.' . $format;
            $path = storage_path('app/private/backups/' . $filename);

            if (!file_exists(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            $dbConfig = config('database.connections.' . config('database.default'));
            
            if ($dbConfig['driver'] === 'sqlite') {
                $databasePath = $dbConfig['database'];
                // If it's an absolute path, use it directly; otherwise use database_path()
                // Check for Windows absolute paths (C:\) and Unix absolute paths (/)
                $isAbsolutePath = str_starts_with($databasePath, DIRECTORY_SEPARATOR) || 
                                   (strlen($databasePath) >= 2 && $databasePath[1] === ':' && ctype_alpha($databasePath[0]));
                
                $sourcePath = $isAbsolutePath ? $databasePath : database_path($databasePath);
                
                if (!file_exists($sourcePath)) {
                    throw new \Exception('Database file not found at: ' . $sourcePath);
                }

                if ($format === 'sqlite') {
                    // Copy the database file directly
                    copy($sourcePath, $path);
                } elseif ($format === 'sql') {
                    // Generate SQL dump using PHP
                    $this->exportSqliteToSql($sourcePath, $path);
                } elseif ($format === 'json') {
                    // Export to JSON
                    $this->exportSqliteToJson($path);
                } else {
                    throw new \Exception('Invalid format. Use "sqlite", "sql", or "json".');
                }
            } else {
                // For MySQL/MariaDB
                $command = sprintf(
                    'mysqldump -h%s -u%s -p%s %s > %s',
                    $dbConfig['host'],
                    $dbConfig['username'],
                    $dbConfig['password'],
                    $dbConfig['database'],
                    $path
                );

                $process = Process::fromShellCommandline($command);
                $process->run();

                if (!$process->isSuccessful()) {
                    throw new ProcessFailedException($process);
                }
            }

            $this->logActivity('database_export', ['format' => $format, 'filename' => $filename]);

            return back()->with('success', 'Database exported successfully as ' . $filename);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to export database: ' . $e->getMessage());
        }
    }

    private function logActivity($action, $details = [])
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'action' => $action,
            'model_type' => 'DatabaseManagement',
            'model_id' => null,
            'old_values' => null,
            'new_values' => $details,
            'metadata' => [],
        ]);
    }

    private function exportSqliteToSql($sourcePath, $targetPath)
    {
        $pdo = new \PDO('sqlite:' . $sourcePath);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $sql = "-- SQLite Database Export\n";
        $sql .= "-- Generated: " . now()->toDateTimeString() . "\n\n";

        // Get all tables
        $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            // Get CREATE TABLE statement
            $createTable = $pdo->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='$table'")->fetchColumn();
            $sql .= $createTable . ";\n\n";

            // Get all data
            $stmt = $pdo->query("SELECT * FROM $table");
            $columns = $pdo->query("PRAGMA table_info($table)")->fetchAll(\PDO::FETCH_ASSOC);
            $columnNames = array_column($columns, 'name');

            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $values = array_map(function($val) {
                    if ($val === null) return 'NULL';
                    if (is_bool($val)) return $val ? '1' : '0';
                    if (is_numeric($val)) return $val;
                    return "'" . addslashes($val) . "'";
                }, $row);

                $sql .= "INSERT INTO $table (" . implode(', ', $columnNames) . ") VALUES (" . implode(', ', $values) . ");\n";
            }
            $sql .= "\n";
        }

        file_put_contents($targetPath, $sql);
    }

    private function exportSqliteToJson($targetPath)
    {
        $data = [];
        $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");

        foreach ($tables as $table) {
            $tableName = $table->name;
            $rows = DB::table($tableName)->get()->toArray();
            $data[$tableName] = $rows;
        }

        file_put_contents($targetPath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function import(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file',
        ]);

        try {
            $file = $request->file('backup_file');
            $dbConfig = config('database.connections.' . config('database.default'));
            
            if ($dbConfig['driver'] === 'sqlite') {
                // For SQLite, replace the database file
                $targetPath = database_path($dbConfig['database']);
                
                // Backup current database
                if (file_exists($targetPath)) {
                    $backupPath = $targetPath . '.backup_' . now()->format('Y_m_d_His');
                    copy($targetPath, $backupPath);
                }
                
                // Copy uploaded file to database location
                copy($file->getRealPath(), $targetPath);
            } else {
                // For MySQL/MariaDB
                $path = $file->getRealPath();
                
                $command = sprintf(
                    'mysql -h%s -u%s -p%s %s < %s',
                    $dbConfig['host'],
                    $dbConfig['username'],
                    $dbConfig['password'],
                    $dbConfig['database'],
                    $path
                );

                $process = Process::fromShellCommandline($command);
                $process->run();

                if (!$process->isSuccessful()) {
                    throw new ProcessFailedException($process);
                }
            }

            $this->logActivity('database_import', ['filename' => $file->getClientOriginalName()]);

            return back()->with('success', 'Database imported successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to import database: ' . $e->getMessage());
        }
    }

    public function downloadBackup($filename)
    {
        $path = 'backups/' . $filename;
        
        if (!Storage::disk('local')->exists($path)) {
            return back()->with('error', 'Backup file not found.');
        }

        $this->logActivity('backup_download', ['filename' => $filename]);

        return Storage::disk('local')->download($path);
    }

    public function deleteBackup($filename)
    {
        $path = 'backups/' . $filename;
        
        if (!Storage::disk('local')->exists($path)) {
            return back()->with('error', 'Backup file not found.');
        }

        Storage::disk('local')->delete($path);
        $this->logActivity('backup_delete', ['filename' => $filename]);

        return back()->with('success', 'Backup deleted successfully.');
    }

    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            
            $this->logActivity('cache_clear');
            
            return back()->with('success', 'All caches cleared successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to clear cache: ' . $e->getMessage());
        }
    }

    public function optimize()
    {
        try {
            Artisan::call('optimize');
            
            $this->logActivity('application_optimize');
            
            return back()->with('success', 'Application optimized successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to optimize: ' . $e->getMessage());
        }
    }

    public function migrate()
    {
        try {
            Artisan::call('migrate --force');
            
            $this->logActivity('database_migrate');
            
            return back()->with('success', 'Database migrations ran successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to run migrations: ' . $e->getMessage());
        }
    }

    public function seed(Request $request)
    {
        $request->validate([
            'seeder' => 'nullable|string',
        ]);

        try {
            if ($request->filled('seeder')) {
                Artisan::call('db:seed', ['--class' => $request->seeder, '--force' => true]);
            } else {
                Artisan::call('db:seed', ['--force' => true]);
            }
            
            $this->logActivity('database_seed', ['seeder' => $request->filled('seeder') ? $request->seeder : 'all']);
            
            return back()->with('success', 'Database seeded successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to seed database: ' . $e->getMessage());
        }
    }
}
