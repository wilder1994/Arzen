<?php

namespace Tests\Unit;

use App\Services\Imports\ClientImportProcessor;
use App\Services\Imports\ImportTemplateExporter;
use App\Services\Imports\VestImportProcessor;
use App\Services\Imports\VestImportTemplateExporter;
use App\Services\Imports\WeaponImportProcessor;
use App\Support\SimpleSpreadsheetExporter;
use Tests\TestCase;
use ZipArchive;

class ImportTemplateExporterTest extends TestCase
{
    public function test_weapon_template_contains_expected_sheets_and_headers(): void
    {
        $exporter = new ImportTemplateExporter(new SimpleSpreadsheetExporter);
        $response = $exporter->streamWeaponTemplate();

        ob_start();
        $response->sendContent();
        $binary = ob_get_clean();

        $this->assertNotFalse($binary);
        $this->assertStringStartsWith('PK', $binary);

        $temporaryPath = tempnam(sys_get_temp_dir(), 'weapon-template-test-');
        file_put_contents($temporaryPath, $binary);

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($temporaryPath) === true);

        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $instructionXml = $zip->getFromName('xl/worksheets/sheet2.xml');
        $zip->close();
        @unlink($temporaryPath);

        $this->assertIsString($workbookXml);
        $this->assertStringContainsString('name="Datos"', $workbookXml);
        $this->assertStringContainsString('name="Instructivo"', $workbookXml);

        $this->assertIsString($sheetXml);
        foreach (WeaponImportProcessor::templateHeaders() as $header) {
            $this->assertStringContainsString($header, $sheetXml);
        }

        $this->assertIsString($instructionXml);
        $this->assertStringContainsString('Columna', $instructionXml);
        $this->assertStringContainsString('Clave principal del lote', $instructionXml);
    }

    public function test_client_template_contains_expected_sheets_and_headers(): void
    {
        $exporter = new ImportTemplateExporter(new SimpleSpreadsheetExporter);
        $response = $exporter->streamClientTemplate();

        ob_start();
        $response->sendContent();
        $binary = ob_get_clean();

        $this->assertNotFalse($binary);

        $temporaryPath = tempnam(sys_get_temp_dir(), 'client-template-test-');
        file_put_contents($temporaryPath, $binary);

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($temporaryPath) === true);

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        @unlink($temporaryPath);

        $this->assertIsString($sheetXml);
        foreach (ClientImportProcessor::templateHeaders() as $header) {
            $this->assertStringContainsString($header, $sheetXml);
        }
        $this->assertStringNotContainsString('Accesos', $sheetXml);
        $this->assertStringNotContainsString('Supervisión', $sheetXml);
    }

    public function test_worker_template_has_the_ficha_and_an_instruction_sheet(): void
    {
        $exporter = new ImportTemplateExporter(new SimpleSpreadsheetExporter);
        $response = $exporter->streamWorkerTemplate();

        ob_start();
        $response->sendContent();
        $binary = ob_get_clean();

        $temporaryPath = tempnam(sys_get_temp_dir(), 'worker-template-test-');
        file_put_contents($temporaryPath, $binary);

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($temporaryPath) === true);
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $instructionXml = $zip->getFromName('xl/worksheets/sheet2.xml');
        $zip->close();
        @unlink($temporaryPath);

        $this->assertIsString($workbookXml);
        $this->assertStringContainsString('name="Empleados"', $workbookXml);
        $this->assertStringContainsString('name="Instrucciones"', $workbookXml);
        $this->assertIsString($sheetXml);
        $this->assertStringContainsString('Escolaridad', $sheetXml);
        $this->assertStringContainsString('Tipo de vinculación', $sheetXml);
        $this->assertStringContainsString('Nivel de riesgo ARL', $sheetXml);
        $this->assertStringNotContainsString('Razón Social Cliente Final', $sheetXml);
        $this->assertIsString($instructionXml);
        $this->assertStringContainsString('Si no existe, se crea.', $instructionXml);
    }

    public function test_vest_template_preserves_data_sheet_and_adds_instruction_sheet(): void
    {
        $templatePath = base_path(VestImportTemplateExporter::TEMPLATE_PATH);
        if (! is_file($templatePath)) {
            $this->markTestSkipped('Plantilla Chalecos.xlsx no disponible en el entorno de pruebas.');
        }

        $exporter = new VestImportTemplateExporter(new SimpleSpreadsheetExporter);
        $response = $exporter->stream();

        ob_start();
        $response->sendContent();
        $binary = ob_get_clean();

        $this->assertNotFalse($binary);
        $this->assertStringStartsWith('PK', $binary);

        $temporaryPath = tempnam(sys_get_temp_dir(), 'vest-template-test-');
        file_put_contents($temporaryPath, $binary);

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($temporaryPath) === true);

        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $dataSheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $instructionXml = $zip->getFromName('xl/worksheets/sheet2.xml');
        $zip->close();
        @unlink($temporaryPath);

        $this->assertIsString($workbookXml);
        $this->assertStringContainsString('name="Hoja1"', $workbookXml);
        $this->assertStringContainsString('name="Instructivo"', $workbookXml);
        $this->assertIsString($dataSheetXml);
        $this->assertStringContainsString('<sheetData>', $dataSheetXml);
        $this->assertIsString($instructionXml);
        $this->assertStringContainsString('Clave principal del lote', $instructionXml);
        $this->assertStringContainsString(VestImportProcessor::templateInstructions()[0][0], $instructionXml);
    }
}
