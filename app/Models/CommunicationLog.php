<?php
namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class CommunicationLog extends Model
{
    use BelongsToCompany;
    protected $fillable = ['company_id','channel','function','recipient','country_code','units','provider_message_id','sent_at'];
    protected $casts = ['sent_at' => 'datetime', 'units' => 'integer'];
}
