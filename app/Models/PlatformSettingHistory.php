<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformSettingHistory extends Model
{
    protected $fillable = ['key', 'old_value', 'new_value', 'reason', 'platform_admin_id'];

    public function admin()
    {
        return $this->belongsTo(PlatformAdmin::class, 'platform_admin_id');
    }
}
