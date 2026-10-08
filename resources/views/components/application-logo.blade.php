@php($companySettings = \App\Models\CompanySetting::current())
<img src="{{ $companySettings->logoUrl() }}" alt="{{ $companySettings->legal_name ?: 'Arzen' }}" {{ $attributes->merge(['class' => 'h-12 w-auto object-contain']) }} />
