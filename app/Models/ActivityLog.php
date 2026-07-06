<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

#[Fillable([
    'user_id',
    'user_name',
    'action',
    'subject_type',
    'subject_id',
    'subject_label',
    'changes',
])]
class ActivityLog extends Model
{
    public const ?string UPDATED_AT = null;

    private const int MAX_VALUE_LENGTH = 300;

    private const array MASKED_ATTRIBUTES = ['password', 'remember_token'];

    private const array IGNORED_ATTRIBUTES = ['created_at', 'updated_at'];

    protected function casts(): array
    {
        return [
            'changes' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public static function record(Model $subject, string $action): void
    {
        $changes = match ($action) {
            'created' => static::changesForCreate($subject),
            'updated' => static::changesForUpdate($subject),
            default => null,
        };

        if ($action === 'updated' && $changes === []) {
            return;
        }

        static::create([
            'user_id' => Auth::id(),
            'user_name' => Auth::user()?->name,
            'action' => $action,
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'subject_label' => static::labelFor($subject),
            'changes' => $changes,
        ]);
    }

    /**
     * @return array<string, array{old: mixed, new: mixed}>
     */
    protected static function changesForCreate(Model $subject): array
    {
        $changes = [];

        foreach ($subject->getAttributes() as $attribute => $value) {
            if (in_array($attribute, self::IGNORED_ATTRIBUTES) || $attribute === $subject->getKeyName()) {
                continue;
            }

            if ($value === null || $value === '') {
                continue;
            }

            $changes[$attribute] = [
                'old' => null,
                'new' => static::presentValue($attribute, $value),
            ];
        }

        return $changes;
    }

    /**
     * @return array<string, array{old: mixed, new: mixed}>
     */
    protected static function changesForUpdate(Model $subject): array
    {
        $changes = [];

        foreach ($subject->getChanges() as $attribute => $value) {
            if (in_array($attribute, self::IGNORED_ATTRIBUTES)) {
                continue;
            }

            $changes[$attribute] = [
                'old' => static::presentValue($attribute, $subject->getOriginal($attribute)),
                'new' => static::presentValue($attribute, $value),
            ];
        }

        return $changes;
    }

    protected static function presentValue(string $attribute, mixed $value): mixed
    {
        if (in_array($attribute, self::MASKED_ATTRIBUTES)) {
            return '••••••••';
        }

        if ($value instanceof \BackedEnum) {
            $value = $value->value;
        }

        if ($value instanceof \DateTimeInterface) {
            $value = $value->format('Y-m-d H:i:s');
        }

        if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        if (is_string($value)) {
            return Str::limit($value, self::MAX_VALUE_LENGTH);
        }

        return $value;
    }

    protected static function labelFor(Model $subject): ?string
    {
        foreach (['title', 'name', 'slug'] as $attribute) {
            $value = $subject->getAttribute($attribute);

            if (is_string($value) && $value !== '') {
                return Str::limit($value, 120);
            }
        }

        return null;
    }
}
