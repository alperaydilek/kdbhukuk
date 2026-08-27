<?php

namespace App\Models;

use Database\Factories\TaskFactory;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    public const STATUS_TODO = 'yapilacak';

    public const STATUS_IN_PROGRESS = 'devam-ediyor';

    public const STATUS_DONE = 'tamamlandi';

    public const STATUSES = [
        self::STATUS_TODO => 'Yapılacak',
        self::STATUS_IN_PROGRESS => 'Yapılıyor',
        self::STATUS_DONE => 'Tamamlandı',
    ];

    public const PRIORITIES = [
        'dusuk' => 'Düşük',
        'normal' => 'Normal',
        'yuksek' => 'Yüksek',
    ];

    protected $fillable = [
        'title',
        'description',
        'assigned_to',
        'assigned_by',
        'due_date',
        'priority',
        'status',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $task): void {
            if (blank($task->assigned_by) && Auth::check()) {
                $task->assigned_by = Auth::id();
            }
        });

        static::created(function (self $task): void {
            if ((int) $task->assigned_to === (int) $task->assigned_by) {
                return;
            }

            $assignee = $task->assignee;

            if (! $assignee) {
                return;
            }

            $assignerName = $task->assigner?->name ?? 'Bir ekip üyesi';

            Notification::make()
                ->title('Size yeni bir görev atandı')
                ->body("{$assignerName}, \"{$task->title}\" görevini size atadı.")
                ->icon('heroicon-o-clipboard-document-check')
                ->actions([
                    Action::make('view')
                        ->label('Görevi Aç')
                        ->url('/admin/tasks/'.$task->id.'/edit'),
                ])
                ->sendToDatabase($assignee);
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function isOverdue(): bool
    {
        return $this->status !== self::STATUS_DONE
            && $this->due_date !== null
            && $this->due_date->isPast();
    }

    public function markStatus(string $status): void
    {
        $this->status = $status;
        $this->completed_at = $status === self::STATUS_DONE ? now() : null;
        $this->save();
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeNotDone(Builder $query): Builder
    {
        return $query->where('status', '!=', self::STATUS_DONE);
    }
}
