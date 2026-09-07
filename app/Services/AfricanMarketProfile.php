<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

class AfricanMarketProfile
{
    public function forCountry(?string $countryCode): array
    {
        return config('african_market_profiles.'.strtoupper((string) $countryCode))
            ?? config('african_market_profiles.TG');
    }

    public function forCompany(?Model $company = null): array
    {
        $company ??= app(CompanyContext::class)->getCompanyOrNull();
        $profile = $this->forCountry($company?->country_code);

        return [
            'currency' => $this->normaliseCurrency($company?->currency ?: $profile['currency']),
            'timezone' => $company?->timezone ?: $profile['timezone'],
            'locale' => $company?->locale ?: $profile['locale'],
        ];
    }

    public function format(mixed $amount, ?Model $company = null): string
    {
        $currency = $this->forCompany($company)['currency'];
        $zeroDecimalCurrencies = ['BIF', 'CDF', 'DJF', 'GNF', 'KMF', 'MGA', 'RWF', 'UGX', 'XAF', 'XOF'];
        $decimals = in_array($currency, $zeroDecimalCurrencies, true) ? 0 : 2;

        return number_format((float) $amount, $decimals, ',', ' ').' '.$currency;
    }

    private function normaliseCurrency(string $currency): string
    {
        return match (strtoupper(trim($currency))) {
            'FCFA', 'F CFA', 'CFA' => 'XOF',
            default => strtoupper(trim($currency)),
        };
    }
}
