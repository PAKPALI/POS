<?php

namespace App\Models\AMS;

use App\Models\AMS\Setting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashAccount extends Model
{
    use HasFactory;

    protected $table = 'cash_accounts';

    protected $fillable = [
        'name',
        'code',
        'balance',
        'currency',
        'is_default',
        'is_tax',
        'status',
        'description',
        'created_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
    
    public static function setDefaultCash($cashId)
    {
        // desactivate all cashes
        CashAccount::where('is_default', 1)->update([
            'is_default' => 0
        ]);

        // Activate the selected cash
        CashAccount::where('id', $cashId)->update([
            'is_default' => 1
        ]);

        //sync with settings
        Setting::updateOrCreate(
            ['id' => 1],
            ['default_cash_id' => $cashId]
        );
    }

    public static function setTaxCash($cashId)
    {
        CashAccount::where('is_tax', 1)->update([
            'is_tax' => 0
        ]);

        CashAccount::where('id', $cashId)->update([
            'is_tax' => 1
        ]);

        // sync avec settings
        Setting::updateOrCreate(
            ['id' => 1],
            ['tax_cash_id' => $cashId]
        );
    }
}