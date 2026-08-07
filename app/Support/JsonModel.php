<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

/**
 * A minimal file-backed "model" that stores its rows as a JSON array
 * in storage/app/data/{file}. Built so app/Models/WorkType and
 * app/Models/TimeLog can drop in without a MySQL database at all —
 * useful on free hosts (e.g. InfinityFree) where the DB quota is the
 * limiting factor, not the amount of data.
 *
 * Not a general-purpose ORM: no relationships, no query builder, no
 * transactions. Fine for a low-traffic, effectively single-user app;
 * writes are protected with flock() so two overlapping requests won't
 * corrupt the file, but this is not built for high concurrency.
 */
abstract class JsonModel
{
    protected static string $file;

    protected static function path(): string
    {
        $dir = storage_path('app/data');

        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0775, true);
        }

        return $dir . '/' . static::$file;
    }

    protected static function readRaw(): array
    {
        $path = static::path();

        if (!File::exists($path)) {
            return [];
        }

        $contents = File::get($path);

        return $contents !== '' ? (json_decode($contents, true) ?: []) : [];
    }

    protected static function writeRaw(array $rows): void
    {
        $path = static::path();

        $fp = fopen($path, 'c+');

        if ($fp === false) {
            throw new \RuntimeException("Cannot open {$path} for writing. Check folder permissions.");
        }

        flock($fp, LOCK_EX);
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode(array_values($rows), JSON_PRETTY_PRINT));
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    /** All rows as a Collection of stdClass objects (so Blade's -> syntax keeps working). */
    public static function all(): Collection
    {
        return collect(static::readRaw())->map(fn ($row) => (object) $row);
    }

    public static function find(int|string $id): ?object
    {
        return static::all()->first(fn ($row) => (int) $row->id === (int) $id);
    }

    public static function create(array $attributes): object
    {
        $rows = static::readRaw();

        $nextId = 1;
        foreach ($rows as $row) {
            $nextId = max($nextId, ($row['id'] ?? 0) + 1);
        }

        $attributes = array_merge(['id' => $nextId], $attributes, [
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]);

        $rows[] = $attributes;
        static::writeRaw($rows);

        return (object) $attributes;
    }

    public static function updateRow(int|string $id, array $attributes): ?object
    {
        $rows = static::readRaw();
        $updated = null;

        foreach ($rows as &$row) {
            if ((int) $row['id'] === (int) $id) {
                $row = array_merge($row, $attributes, ['updated_at' => now()->toDateTimeString()]);
                $updated = $row;
                break;
            }
        }
        unset($row);

        if ($updated) {
            static::writeRaw($rows);
        }

        return $updated ? (object) $updated : null;
    }

    public static function deleteRow(int|string $id): bool
    {
        $rows = static::readRaw();
        $filtered = array_values(array_filter($rows, fn ($row) => (int) $row['id'] !== (int) $id));

        if (count($filtered) === count($rows)) {
            return false;
        }

        static::writeRaw($filtered);

        return true;
    }
}
