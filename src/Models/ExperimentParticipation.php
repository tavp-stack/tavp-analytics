<?php

declare(strict_types=1);

namespace Tavp\Analytics\Models;

use Tavp\Core\Database\Model;

/**
 * Experiment participation — tracks which variant a user saw.
 */
class ExperimentParticipation extends Model
{
    protected string $table = 'analytics_experiment_participations';

    protected array $fillable = [
        'experiment_id',
        'session_id',
        'user_id',
        'variant',
        'converted',
        'converted_at',
        'created_at',
    ];

    protected array $casts = [
        'converted' => 'boolean',
        'converted_at' => 'datetime',
        'created_at' => 'datetime',
    ];
}
