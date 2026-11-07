<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Post;
use App\Models\User;
use App\Models\Weapon;
use App\Models\WeaponHistory;
use App\Models\Worker;
use Illuminate\Support\Carbon;

class WeaponHistoryService
{
    public function record(Weapon $weapon, ?User $user, string $kind, string $body): WeaponHistory
    {
        $body = trim($body);
        if ($body === '') {
            $body = '—';
        }

        return WeaponHistory::create([
            'weapon_id' => $weapon->id,
            'user_id' => $user?->id,
            'kind' => $kind,
            'body' => $body,
        ]);
    }

    public function recordCreated(Weapon $weapon, ?User $user, ?string $notes = null): void
    {
        $lines = [
            __('Arma registrada en el sistema.'),
            __('Serie: :serial', ['serial' => $weapon->serial_number ?? '—']),
            __('Tipo: :type', ['type' => $weapon->weapon_type ?? '—']),
        ];

        $notes = $notes !== null ? trim($notes) : '';
        if ($notes !== '') {
            $lines[] = '';
            $lines[] = __('Notas:');
            $lines[] = $notes;
        }

        $this->record($weapon, $user, WeaponHistory::KIND_CREATED, implode("\n", $lines));
    }

    public function recordManualNote(Weapon $weapon, ?User $user, string $notes): void
    {
        $notes = trim($notes);
        if ($notes === '') {
            return;
        }

        $this->record($weapon, $user, WeaponHistory::KIND_NOTE, __('Notas:')."\n".$notes);
    }

    /**
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     */
    public function recordWeaponUpdate(Weapon $weapon, ?User $user, array $before, array $after, ?string $notesFromForm = null): void
    {
        $fieldLabels = $this->trackedFieldLabels();
        $changes = [];

        foreach ($fieldLabels as $field => $label) {
            $old = $this->normalizeFieldValue($field, $before[$field] ?? null);
            $new = $this->normalizeFieldValue($field, $after[$field] ?? null);

            if ($old !== $new) {
                $changes[] = __(':label: :from → :to', [
                    'label' => $label,
                    'from' => $old === '' ? '—' : $old,
                    'to' => $new === '' ? '—' : $new,
                ]);
            }
        }

        $notesFromForm = $notesFromForm !== null ? trim($notesFromForm) : '';

        if ($changes === [] && $notesFromForm === '') {
            return;
        }

        $lines = [__('Actualización de la información del arma.')];

        if ($changes !== []) {
            $lines[] = '';
            $lines[] = __('Cambios:');
            foreach ($changes as $line) {
                $lines[] = '• '.$line;
            }
        }

        if ($notesFromForm !== '') {
            $lines[] = '';
            $lines[] = __('Notas:');
            $lines[] = $notesFromForm;
        }

        $this->record($weapon, $user, WeaponHistory::KIND_UPDATE, implode("\n", $lines));
    }

    public function recordDestinationAssignment(
        Weapon $weapon,
        ?User $user,
        Client $client,
        User $responsible,
        ?string $reason,
        bool $reassigned,
        ?Client $previousClient = null,
    ): void {
        $title = $reassigned
            ? __('Destino operativo actualizado.')
            : __('Destino operativo asignado.');

        $lines = [
            $title,
            __('Cliente: :name', ['name' => $client->name]),
            __('Responsable: :name', ['name' => $responsible->name]),
        ];

        if ($reassigned && $previousClient) {
            $lines[] = __('Cliente anterior: :name', ['name' => $previousClient->name]);
        }

        $reason = $reason !== null ? trim($reason) : '';
        if ($reason !== '') {
            $lines[] = '';
            $lines[] = __('Observaciones:');
            $lines[] = $reason;
        }

        $this->record($weapon, $user, WeaponHistory::KIND_DESTINATION, implode("\n", $lines));
    }

    public function recordDestinationRetired(Weapon $weapon, ?User $user, Client $client, ?string $reason = null): void
    {
        $lines = [
            __('Destino operativo retirado.'),
            __('Cliente: :name', ['name' => $client->name]),
        ];

        $reason = $reason !== null ? trim($reason) : '';
        if ($reason !== '') {
            $lines[] = '';
            $lines[] = __('Observaciones:');
            $lines[] = $reason;
        }

        $this->record($weapon, $user, WeaponHistory::KIND_DESTINATION, implode("\n", $lines));
    }

    public function recordInternalAssignment(
        Weapon $weapon,
        ?User $user,
        ?Post $post,
        ?Worker $worker,
        ?string $reason,
        ?int $ammoCount = null,
        ?int $providerCount = null,
        bool $replaced = false,
    ): void {
        $lines = [
            $replaced ? __('Asignación interna actualizada.') : __('Asignación interna registrada.'),
        ];

        if ($post) {
            $lines[] = __('Puesto: :name', ['name' => $post->name]);
        }

        if ($worker) {
            $lines[] = __('Trabajador: :name', ['name' => $worker->name]);
        }

        if ($ammoCount !== null) {
            $lines[] = __('Cant. munición: :count', ['count' => $ammoCount]);
        }

        if ($providerCount !== null) {
            $lines[] = __('Cnt. proveedor: :count', ['count' => $providerCount]);
        }

        $reason = $reason !== null ? trim($reason) : '';
        if ($reason !== '') {
            $lines[] = '';
            $lines[] = __('Observaciones:');
            $lines[] = $reason;
        }

        $this->record($weapon, $user, WeaponHistory::KIND_INTERNAL, implode("\n", $lines));
    }

    public function recordInternalRetired(Weapon $weapon, ?User $user): void
    {
        $this->record(
            $weapon,
            $user,
            WeaponHistory::KIND_INTERNAL,
            __('Asignación interna retirada.')
        );
    }

    public function recordIncident(
        Weapon $weapon,
        ?User $user,
        string $headline,
        ?string $observation = null,
        ?string $detail = null,
    ): void {
        $lines = [$headline];

        $observation = $observation !== null ? trim($observation) : '';
        if ($observation !== '') {
            $lines[] = '';
            $lines[] = __('Observación:');
            $lines[] = $observation;
        }

        $detail = $detail !== null ? trim($detail) : '';
        if ($detail !== '') {
            $lines[] = '';
            $lines[] = $detail;
        }

        $this->record($weapon, $user, WeaponHistory::KIND_INCIDENT, implode("\n", $lines));
    }

    public function recordTransfer(
        Weapon $weapon,
        ?User $user,
        string $headline,
        ?string $note = null,
        ?string $detail = null,
    ): void {
        $lines = [$headline];

        $detail = $detail !== null ? trim($detail) : '';
        if ($detail !== '') {
            $lines[] = $detail;
        }

        $note = $note !== null ? trim($note) : '';
        if ($note !== '') {
            $lines[] = '';
            $lines[] = __('Nota:');
            $lines[] = $note;
        }

        $this->record($weapon, $user, WeaponHistory::KIND_TRANSFER, implode("\n", $lines));
    }

    public function recordDocument(
        Weapon $weapon,
        ?User $user,
        string $documentName,
        ?string $observations,
        ?string $status,
        ?string $validUntil = null,
    ): void {
        $lines = [
            __('Documento cargado.'),
            __('Archivo: :name', ['name' => $documentName]),
        ];

        if ($validUntil) {
            $lines[] = __('Vence: :date', ['date' => $validUntil]);
        }

        if ($status) {
            $lines[] = __('Estado: :status', ['status' => $status]);
        }

        $observations = $observations !== null ? trim($observations) : '';
        if ($observations !== '') {
            $lines[] = '';
            $lines[] = __('Observaciones:');
            $lines[] = $observations;
        }

        $this->record($weapon, $user, WeaponHistory::KIND_DOCUMENT, implode("\n", $lines));
    }

    public function recordRevistaPhotosApproved(
        Weapon $weapon,
        User $reviewer,
        User $fieldUser,
        int $photoCount,
    ): void {
        $now = now()->timezone(config('app.timezone'));

        $lines = [
            __('Imágenes oficiales del arma actualizadas desde Revista armas.'),
            __('Fecha: :date', ['date' => $now->format('d/m/Y H:i')]),
            __('Fotos actualizadas: :count', ['count' => $photoCount]),
            __('Usuario de campo: :name', ['name' => $fieldUser->name]),
        ];

        $this->record($weapon, $reviewer, WeaponHistory::KIND_PHOTOS, implode("\n", $lines));
    }

    /**
     * @return array<string, string>
     */
    public function trackedFieldLabels(): array
    {
        return [
            'internal_code' => __('Código interno'),
            'serial_number' => __('Número de serie'),
            'weapon_type' => __('Tipo de arma'),
            'caliber' => __('Calibre'),
            'brand' => __('Marca'),
            'capacity' => __('Capacidad'),
            'ownership_entity' => __('Entidad propietaria'),
            'permit_type' => __('Tipo de permiso'),
            'permit_number' => __('Número de permiso'),
            'permit_expires_at' => __('Fecha de vencimiento'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function weaponSnapshot(Weapon $weapon): array
    {
        return $weapon->only(array_merge(array_keys($this->trackedFieldLabels()), ['notes']));
    }

    private function normalizeFieldValue(string $field, mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if ($field === 'permit_expires_at' && $value) {
            try {
                return Carbon::parse($value)->format('Y-m-d');
            } catch (\Throwable) {
                return (string) $value;
            }
        }

        if ($field === 'permit_type') {
            return match ((string) $value) {
                'porte' => __('Porte'),
                'tenencia' => __('Tenencia'),
                default => (string) $value,
            };
        }

        return trim((string) $value);
    }
}
