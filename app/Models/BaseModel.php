<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Every business-domain model extends this: soft deletes + audit stamping
 * (created_by/updated_by/deleted_by) + activity logging are wired once here.
 */
abstract class BaseModel extends Model
{
    use HasAudit;
    use LogsActivity;
    use SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->logFillable()
            ->dontSubmitEmptyLogs()
            ->useLogName($this->getTable());
    }
}
