<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class RegionCatalog
{
    private function regions(): array
    {
        return File::json(resource_path('data/regions-id.json'));
    }

    public function all(): array
    {
        $catalog = [];
        foreach ($this->provinceNames() as $province) {
            $catalog[$province] = $this->regenciesFor($province);
        }

        return $catalog;
    }

    public function provinceNames(): array
    {
        return array_keys($this->regions());
    }

    public function regenciesFor(string $province): array
    {
        $official = $this->regions()[$province] ?? [];
        $normalized = array_map(fn (string $name): string => preg_replace('/^(Kabupaten|Kota) /u', '', $name), $official);
        $counts = array_count_values($normalized);

        return array_map(function (string $name) use ($counts): array {
            $value = preg_replace('/^(Kabupaten|Kota) /u', '', $name);
            if ($counts[$value] > 1) {
                $value = $name;
            }

            return ['value' => $value, 'label' => $name];
        }, $official);
    }
}
