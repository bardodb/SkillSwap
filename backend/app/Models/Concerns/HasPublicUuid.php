<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasPublicUuid
{
    public static function bootHasPublicUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Guard Postgres uuid columns against invalid input (avoids SQLSTATE 22P02 → 500).
     */
    public static function isValidPublicUuid(mixed $value): bool
    {
        if (! is_string($value) || $value === '') {
            return false;
        }

        return (bool) preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $value
        );
    }

    /**
     * Expose uuid as client-facing `id`; omit integer PK and raw uuid column from JSON.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $array = parent::toArray();

        if (! empty($this->uuid)) {
            $array['id'] = $this->uuid;
        }

        unset($array['uuid']);

        return $array;
    }
}
