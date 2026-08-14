<?php
declare(strict_types=1);

namespace App\Database;

use PDO;
use RuntimeException;
use Throwable;

final class MigrationRunner
{
    public function __construct(private readonly PDO $pdo, private readonly string $directory) {}

    public function run(): int
    {
        if (!is_dir($this->directory)) throw new RuntimeException("Diretório de migrações não encontrado: {$this->directory}");
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS schema_migrations (migration VARCHAR(190) PRIMARY KEY, executed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB');
        $lock = (int)$this->pdo->query("SELECT GET_LOCK('paineldecomando_migrations', 30)")->fetchColumn();
        if ($lock !== 1) throw new RuntimeException('Não foi possível obter o bloqueio das migrações.');

        try {
            $files = glob($this->directory . '/*.sql') ?: [];
            sort($files, SORT_NATURAL);
            $executed = 0;
            $exists = $this->pdo->prepare('SELECT 1 FROM schema_migrations WHERE migration = :migration');
            $record = $this->pdo->prepare('INSERT INTO schema_migrations (migration) VALUES (:migration)');
            foreach ($files as $file) {
                $migration = basename($file);
                $exists->execute(['migration' => $migration]);
                if ($exists->fetchColumn()) continue;
                $sql = file_get_contents($file);
                if (!is_string($sql) || trim($sql) === '') throw new RuntimeException("Migração vazia: {$migration}");
                $statements = preg_split('/;\s*(?:\r?\n|$)/', trim($sql)) ?: [];
                foreach ($statements as $statement) {
                    if (trim($statement) !== '') $this->pdo->exec($statement);
                }
                $record->execute(['migration' => $migration]);
                $executed++;
            }
            return $executed;
        } finally {
            try { $this->pdo->query("SELECT RELEASE_LOCK('paineldecomando_migrations')"); }
            catch (Throwable) {}
        }
    }
}
