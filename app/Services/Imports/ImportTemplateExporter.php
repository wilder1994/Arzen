<?php

namespace App\Services\Imports;

use App\Support\SimpleSpreadsheetExporter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportTemplateExporter
{
    public function __construct(private readonly SimpleSpreadsheetExporter $spreadsheetExporter)
    {
    }

    public function streamWeaponTemplate(): StreamedResponse
    {
        return $this->spreadsheetExporter->streamTwoSheet(
            filename: 'formato-carga-armas.xlsx',
            dataSheetName: 'Datos',
            dataHeaders: WeaponImportProcessor::templateHeaders(),
            instructionSheetName: 'Instructivo',
            instructionHeaders: self::instructionHeaders(),
            instructionRows: WeaponImportProcessor::templateInstructions(),
            dataColumnWidths: [18, 18, 16, 12, 12, 14, 16, 32],
            instructionColumnWidths: [32, 12, 14, 72],
        );
    }

    public function streamClientTemplate(): StreamedResponse
    {
        return $this->spreadsheetExporter->streamTwoSheet(
            filename: 'formato-carga-clientes.xlsx',
            dataSheetName: 'Clientes',
            dataHeaders: ClientImportProcessor::templateHeaders(),
            instructionSheetName: 'Instrucciones',
            instructionHeaders: self::instructionHeaders(),
            instructionRows: ClientImportProcessor::templateInstructions(),
            dataColumnWidths: [16, 28, 28, 14, 18, 26, 16, 26, 26, 22, 30, 18, 18],
            instructionColumnWidths: [32, 12, 14, 72],
        );
    }

    public function streamWorkerTemplate(): StreamedResponse
    {
        return $this->spreadsheetExporter->streamTwoSheet(
            filename: 'formato-carga-trabajadores.xlsx',
            dataSheetName: 'Empleados',
            dataHeaders: WorkerImportProcessor::templateHeaders(),
            instructionSheetName: 'Instrucciones',
            instructionHeaders: self::instructionHeaders(),
            instructionRows: WorkerImportProcessor::templateInstructions(),
            dataColumnWidths: array_fill(0, count(WorkerImportProcessor::templateHeaders()), 24),
            instructionColumnWidths: [42, 12, 22, 78],
        );
    }

    /**
     * @return array<int, string>
     */
    private static function instructionHeaders(): array
    {
        return ['Columna', 'Obligatorio', 'Formato', 'Descripción'];
    }
}
