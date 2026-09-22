<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Audience values the app already saves. The original column only allowed
     * all and teachers_only, so Grade Level posts failed on Postgres and MySQL.
     *
     * @var list<string>
     */
    private array $values = [
        'all',
        'teachers_only',
        'grade_teachers',
        'specific_teacher',
        'grade',
    ];

    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            $this->expandPostgres();
        } elseif ($driver === 'mysql') {
            $this->expandMysql();
        } elseif ($driver === 'sqlite') {
            $this->expandSqlite();
        }
    }

    public function down(): void
    {
        // Keep the wider list. Narrowing it would reject posts already saved
        // for a grade or a specific teacher.
    }

    private function expandPostgres(): void
    {
        DB::statement(<<<'SQL'
DO $$
DECLARE
    constraint_name text;
BEGIN
    FOR constraint_name IN
        SELECT conname
        FROM pg_constraint
        WHERE conrelid = 'posts'::regclass
          AND contype = 'c'
          AND pg_get_constraintdef(oid) ILIKE '%audience%'
    LOOP
        EXECUTE format('ALTER TABLE posts DROP CONSTRAINT %I', constraint_name);
    END LOOP;
END $$;
SQL);

        $list = implode(', ', array_map(
            fn (string $value) => "'".$value."'",
            $this->values
        ));

        DB::statement(
            "ALTER TABLE posts ADD CONSTRAINT posts_audience_check CHECK (audience::text IN ({$list}))"
        );
    }

    private function expandMysql(): void
    {
        $list = implode("','", $this->values);

        DB::statement(
            "ALTER TABLE posts MODIFY audience ENUM('{$list}') NOT NULL DEFAULT 'all'"
        );
    }

    private function expandSqlite(): void
    {
        $row = DB::selectOne("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = 'posts'");
        $sql = $row->sql ?? '';

        if (! str_contains(strtolower($sql), 'check')) {
            return;
        }

        $allowed = implode(', ', array_map(
            fn (string $value) => "'".$value."'",
            $this->values
        ));

        $updated = preg_replace(
            '/check\s*\(\s*"audience"\s+in\s*\([^)]*\)\s*\)/i',
            'check ("audience" in ('.$allowed.'))',
            $sql,
            1,
            $count
        );

        if ($count !== 1 || ! is_string($updated)) {
            return;
        }

        DB::statement('PRAGMA foreign_keys = OFF');
        DB::statement('ALTER TABLE posts RENAME TO posts_audience_old');
        DB::statement($updated);
        DB::statement('INSERT INTO posts SELECT * FROM posts_audience_old');
        DB::statement('DROP TABLE posts_audience_old');
        DB::statement('PRAGMA foreign_keys = ON');
    }
};
