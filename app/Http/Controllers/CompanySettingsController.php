<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\CompanySetting;
use App\Models\File;
use App\Models\Installation;
use App\Support\Installations\InstallationAudit;
use App\Services\CompanyLetterheadService;
use App\Services\OwnCompanyClientService;
use App\Support\MapCoordinates;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class CompanySettingsController extends Controller
{
    public function edit(Request $request, CompanyLetterheadService $letterhead)
    {
        $this->authorizeAdmin($request);

        $company = CompanySetting::current();
        $letterheadImages = $letterhead->imagesFor($company);

        $returnInstallation = null;
        $ownCompany = Client::ownCompany();
        $returnId = $request->integer('return_installation');
        if ($ownCompany && $returnId !== 0) {
            $returnInstallation = Installation::matchingPrincipal($returnId, $ownCompany->id);
        }

        return view('company.edit', [
            'company' => $company,
            'hasLetterheadHeader' => $letterheadImages['header'] !== null,
            'hasLetterheadFooter' => $letterheadImages['footer'] !== null,
            'missingLetterFields' => $company->missingLetterFields(),
            'returnInstallation' => $returnInstallation,
        ]);
    }

    public function update(Request $request, CompanyLetterheadService $letterhead, OwnCompanyClientService $ownClient)
    {
        $this->authorizeAdmin($request);

        $returnInstallation = null;
        $installationBefore = null;
        $ownCompany = Client::ownCompany();
        $returnId = $request->integer('return_installation');
        if ($ownCompany && $returnId !== 0) {
            $returnInstallation = Installation::matchingPrincipal($returnId, $ownCompany->id);
            $installationBefore = $returnInstallation?->only(['name', 'address', 'city', 'department']);
        }
        $note = trim((string) $request->input('change_note', ''));

        $data = $request->validate([
            'legal_name' => ['nullable', 'string', 'max:255'],
            'nit' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'department' => ['nullable', 'string', 'max:120'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'coords_source' => ['nullable', 'string', 'max:20'],
            'letter_city' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:60'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'legal_rep_name' => ['nullable', 'string', 'max:255'],
            'legal_rep_document' => ['nullable', 'string', 'max:40'],
            'legal_rep_document_city' => ['nullable', 'string', 'max:120'],
            'agent_name' => ['nullable', 'string', 'max:255'],
            'agent_document' => ['nullable', 'required_with:agent_name', 'string', 'max:40'],
            'agent_document_city' => ['nullable', 'required_with:agent_name', 'string', 'max:120'],
            'internal_code_prefix' => ['nullable', 'string', 'max:10', 'regex:/^[A-Za-z0-9\-_.]*$/'],
            'logo' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'letterhead' => ['nullable', 'file', 'mimes:docx', 'max:10240'],
            'remove_logo' => ['nullable', 'boolean'],
            'remove_letterhead' => ['nullable', 'boolean'],
        ], [
            'internal_code_prefix.regex' => __('El prefijo solo admite letras, números, guion, guion bajo y punto.'),
        ]);

        if ($request->hasFile('letterhead') && ! $letterhead->hasHeaderImage($request->file('letterhead')->getRealPath())) {
            throw ValidationException::withMessages([
                'letterhead' => __('El Word no tiene una imagen en el encabezado. Inserta el membrete como imagen en el encabezado (y el pie, si aplica).'),
            ]);
        }

        $company = CompanySetting::firstOrCreateSingleton();
        $storedPaths = [];
        $filesToDelete = [];

        try {
            DB::transaction(function () use ($request, $data, $company, $ownClient, &$storedPaths, &$filesToDelete) {
                $attributes = MapCoordinates::apply(collect($data)
                    ->except(['logo', 'letterhead', 'remove_logo', 'remove_letterhead'])
                    ->map(fn ($value) => is_string($value) ? trim($value) : $value)
                    ->all());
                $attributes['internal_code_prefix'] = strtoupper((string) ($attributes['internal_code_prefix'] ?? ''));
                $attributes['updated_by'] = $request->user()->id;

                foreach (['logo' => 'logo_file_id', 'letterhead' => 'letterhead_file_id'] as $input => $column) {
                    $previous = $company->{$column} ? File::find($company->{$column}) : null;

                    if ($request->hasFile($input)) {
                        $file = $this->storeUpload($request->file($input), $request);
                        $storedPaths[] = $file->path;
                        $attributes[$column] = $file->id;
                    } elseif ($request->boolean('remove_'.$input)) {
                        $attributes[$column] = null;
                    } else {
                        continue;
                    }

                    if ($previous) {
                        $filesToDelete[] = $previous;
                    }
                }

                $company->update($attributes);
                $ownClient->sync($company);
            });
        } catch (Throwable $exception) {
            foreach ($storedPaths as $path) {
                Storage::disk('local')->delete($path);
            }

            throw $exception;
        }

        foreach ($filesToDelete as $file) {
            Storage::disk($file->disk)->delete($file->path);
            $file->delete();
        }

        if ($returnInstallation) {
            $returnInstallation->refresh();
            InstallationAudit::record($request->user()?->id, 'installation_updated', $returnInstallation, $installationBefore, $note);

            return redirect()
                ->route('installations.edit', $returnInstallation)
                ->with('status', __('Instalación actualizada.'));
        }

        return redirect()
            ->route('company.edit')
            ->with('status', __('Datos de la empresa actualizados.'));
    }

    public function logo()
    {
        $company = CompanySetting::current();
        $file = $company->logoFile;

        if (! $file || ! Storage::disk($file->disk)->exists($file->path)) {
            return redirect(asset(CompanySetting::FALLBACK_LOGO));
        }

        return response()->file(Storage::disk($file->disk)->path($file->path), [
            'Content-Type' => $file->mime_type,
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }

    public function letterheadImage(Request $request, string $part, CompanyLetterheadService $letterhead)
    {
        $this->authorizeAdmin($request);
        abort_unless(in_array($part, ['header', 'footer'], true), 404);

        $path = $letterhead->imagesFor(CompanySetting::current())[$part] ?? null;
        abort_unless($path && is_file($path), 404);

        return response()->file($path);
    }

    private function storeUpload(UploadedFile $upload, Request $request): File
    {
        $path = $upload->store('company', 'local');

        return File::create([
            'disk' => 'local',
            'path' => $path,
            'original_name' => $upload->getClientOriginalName(),
            'mime_type' => $upload->getClientMimeType(),
            'size' => $upload->getSize(),
            'checksum' => hash_file('sha256', $upload->getRealPath()),
            'uploaded_by' => $request->user()?->id,
        ]);
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->canViewModule('company'), 403);
    }
}
