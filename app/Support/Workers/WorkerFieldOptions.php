<?php

namespace App\Support\Workers;

final class WorkerFieldOptions
{
    public const DEFAULT_NATIONALITY = 'Colombiana';

    /** @var list<string> */
    public const BLOOD_GROUPS = ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'Pendiente'];

    /** @var list<string> */
    public const EDUCATION = ['Primaria', 'Bachillerato', 'Educación superior', 'Otro'];

    /** @var list<string> */
    public const RISK_LEVELS = ['I', 'II', 'III', 'IV', 'V'];

    /**
     * @return list<string>
     */
    public static function nationalities(): array
    {
        return [
            'Colombiana',
            'Venezolana',
            'Ecuatoriana',
            'Peruana',
            'Brasileña',
            'Panameña',
            'Mexicana',
            'Argentina',
            'Chilena',
            'Boliviana',
            'Paraguaya',
            'Uruguaya',
            'Española',
            'Estadounidense',
            'Canadiense',
            'Cubana',
            'Dominicana',
            'Haitiana',
            'Costarricense',
            'Nicaragüense',
            'Hondureña',
            'Guatemalteca',
            'Salvadoreña',
            'Puertorriqueña',
            'Italiana',
            'Francesa',
            'Alemana',
            'Portuguesa',
            'Británica',
            'China',
            'Japonesa',
            'Coreana',
            'India',
        ];
    }

    /**
     * @param  list<string>  $options
     * @return list<string>
     */
    public static function including(?string $current, array $options): array
    {
        $current = trim((string) $current);

        if ($current === '' || in_array($current, $options, true)) {
            return $options;
        }

        return array_merge($options, [$current]);
    }
}
