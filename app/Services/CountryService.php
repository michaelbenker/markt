<?php

namespace App\Services;

class CountryService
{
    private static ?array $countries = null;

    public static function getCountries(): array
    {
        if (self::$countries === null) {
            $json = file_get_contents(config_path('countries.json'));
            $data = json_decode($json, true);
            self::$countries = $data['countries'] ?? [];
        }

        return self::$countries;
    }

    public static function getCountriesForSelect(): array
    {
        $countries = self::getCountries();
        $options = [];

        // DACH-Länder zuerst
        $dachCountries = array_filter($countries, fn($country) => $country['group'] === 'DACH');
        foreach ($dachCountries as $country) {
            $options[$country['name']] = $country['name'];
        }

        // Trennlinie
        $options['---'] = '---';

        // Andere europäische Länder alphabetisch
        $europeCountries = array_filter($countries, fn($country) => $country['group'] === 'Europe');
        usort($europeCountries, fn($a, $b) => strcmp($a['name'], $b['name']));
        
        foreach ($europeCountries as $country) {
            $options[$country['name']] = $country['name'];
        }

        return $options;
    }

    public static function getCountryByName(string $name): ?array
    {
        $countries = self::getCountries();
        foreach ($countries as $country) {
            if ($country['name'] === $name) {
                return $country;
            }
        }
        return null;
    }

    public static function getCountryByCode(string $code): ?array
    {
        $countries = self::getCountries();
        foreach ($countries as $country) {
            if ($country['code'] === $code) {
                return $country;
            }
        }
        return null;
    }
}