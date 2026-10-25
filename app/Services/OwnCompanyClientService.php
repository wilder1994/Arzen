<?php

namespace App\Services;

use App\Models\Client;
use App\Models\CompanySetting;

/**
 * Mi empresa también opera como cliente: tiene instalaciones, puestos, armerillo y trabajadores.
 */
class OwnCompanyClientService
{
    public function sync(CompanySetting $company): Client
    {
        $client = Client::ownCompany() ?? new Client();

        $client->fill([
            'party_type' => Client::PARTY_LEGAL,
            'name' => filled($company->legal_name) ? $company->legal_name : __('Mi empresa'),
            'legal_name' => $company->legal_name,
            'document_type' => 'NIT',
            'nit' => filled($company->nit) ? $company->nit : '-',
            'legal_representative' => $company->legal_rep_name,
            'email' => $company->email,
            'phone' => $company->phone,
            'address' => $company->address,
            'neighborhood' => $company->neighborhood,
            'city' => $company->city,
            'department' => $company->department,
            'latitude' => $company->latitude,
            'longitude' => $company->longitude,
        ]);
        $client->is_own_company = true;
        $client->save();

        return $client;
    }
}
