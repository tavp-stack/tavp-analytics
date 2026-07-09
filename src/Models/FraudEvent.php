<?php

declare(strict_types=1);

namespace Tavp\Analytics\Models;

use Tavp\Core\Database\Model;

/**
 * Fraud event log — records detected suspicious activities.
 */
class FraudEvent extends Model
{
    protected string $table = 'analytics_fraud_events';

    protected array $fillable = [
        'session_id',
        'user_id',
        'ip_address',
        'event_type',
        'rule_name',
        'score',
        'details',
        'action_taken',
        'resolved_at',
        'resolved_by',
        'created_at',
    ];

    protected array $casts = [
        'score' => 'float',
        'details' => 'json',
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Scope: unresolved fraud events.
     */
    public function scopeUnresolved(): array
    {
        return $this->whereNull('resolved_at')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
