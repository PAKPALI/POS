<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','function','text',];

    public function user(){
        return  $this ->belongsTo(User::class);
    }

    public function region()
    {
        return $this->belongsTo(Regions::class, 'region_id', 'id');
    }
}
