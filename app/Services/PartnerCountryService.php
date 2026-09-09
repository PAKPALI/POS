<?php

namespace App\Services;

use InvalidArgumentException;

class PartnerCountryService
{
    public function __construct(private PlatformConfigurationService $configuration) {}

    public function catalog(): array
    {
        return config('partners.country_catalog', []);
    }

    public function activeCountries(): array
    {
        $fallback = config('partners.active_countries', ['TG']);
        $stored = $this->configuration->get('partners.active_countries', json_encode($fallback));
        $codes = is_array($stored) ? $stored : json_decode((string) $stored, true);
        $codes = array_values(array_unique(array_filter(
            array_map(fn ($code) => strtoupper((string) $code), is_array($codes) ? $codes : []),
            fn (string $code) => array_key_exists($code, $this->catalog())
        )));

        if ($codes === []) {
            $codes = ['TG'];
        }

        return array_map(fn (string $code) => ['code' => $code] + $this->catalog()[$code], $codes);
    }

    public function activeCodes(): array
    {
        return array_column($this->activeCountries(), 'code');
    }

    public function activeCountry(string $code): ?array
    {
        foreach ($this->activeCountries() as $country) {
            if ($country['code'] === strtoupper($code)) {
                return $country;
            }
        }

        return null;
    }

    public function normalizePhone(string $countryCode, string $number): string
    {
        $country = $this->activeCountry($countryCode);
        if (!$country) {
            throw new InvalidArgumentException('Le pays de résidence sélectionné n’est pas actif.');
        }

        $digits = preg_replace('/\D+/', '', $number);
        $callingCode = ltrim($country['dial_code'], '+');
        if (str_starts_with($digits, $callingCode)) {
            $digits = substr($digits, strlen($callingCode));
        }

        $length = strlen($digits);
        if ($length < $country['phone_min_length'] || $length > $country['phone_max_length']) {
            throw new InvalidArgumentException(sprintf(
                'Le numéro pour %s doit contenir entre %d et %d chiffres.',
                $country['name'],
                $country['phone_min_length'],
                $country['phone_max_length'],
            ));
        }

        return $country['dial_code'].$digits;
    }
}
