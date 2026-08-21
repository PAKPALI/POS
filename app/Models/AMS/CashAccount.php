<?php

namespace App\Models\AMS;

use App\Traits\BelongsToCompany;
use App\Models\AMS\Setting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashAccount extends Model
{
    use BelongsToCompany, HasFactory;

    protected $table = 'cash_accounts';

    protected $fillable = [
        'company_id',
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
        // Une seule caisse principale par entreprise.
        CashAccount::where('is_default', 1)->update([
            'is_default' => 0
        ]);

        // Une même caisse ne peut jamais être principale et caisse de taxe.
        CashAccount::where('id', $cashId)->update([
            'is_default' => 1,
            'is_tax' => 0,
        ]);

        $setting = Setting::first();
        Setting::updateOrCreate(
            $setting ? ['id' => $setting->id] : [],
            [
                'default_cash_id' => $cashId,
                'tax_cash_id' => $setting?->tax_cash_id == $cashId ? null : $setting?->tax_cash_id,
            ]
        );
    }

    public static function setTaxCash($cashId)
    {
        CashAccount::where('is_tax', 1)->update([
            'is_tax' => 0
        ]);

        CashAccount::where('id', $cashId)->update([
            'is_tax' => 1,
            'is_default' => 0,
        ]);

        $setting = Setting::first();
        Setting::updateOrCreate(
            $setting ? ['id' => $setting->id] : [],
            [
                'tax_cash_id' => $cashId,
                'default_cash_id' => $setting?->default_cash_id == $cashId ? null : $setting?->default_cash_id,
            ]
        );
    }
}
