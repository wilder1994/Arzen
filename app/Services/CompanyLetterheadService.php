<?php

namespace App\Services;

use App\Models\CompanySetting;
use App\Models\File;
use Illuminate\Support\Facades\File as FileFacade;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class CompanyLetterheadService
{
    /**
     * @return array{header: ?string, footer: ?string}
     */
    public function imagesFor(?CompanySetting $settings = null): array
    {
        $settings ??= CompanySetting::current();
        $file = $settings->letterheadFile;

        if (! $file || ! Storage::disk($file->disk)->exists($file->path)) {
            return ['header' => null, 'footer' => null];
        }

        return $this->extract(Storage::disk($file->disk)->path($file->path), 'file-'.$file->id);
    }

    /**
     * Header and footer images of a .docx: the largest raster image referenced by its header/footer parts,
     * since footers often carry small icons next to the actual letterhead strip.
     *
     * @return array{header: ?string, footer: ?string}
     */
    public function extract(string $docxPath, string $cacheKey): array
    {
        $result = ['header' => null, 'footer' => null];
        $targetDir = storage_path('app/tmp/letterhead/'.$cacheKey);

        foreach (['header', 'footer'] as $part) {
            $cached = glob($targetDir.'/'.$part.'.*') ?: [];
            if ($cached !== []) {
                $result[$part] = $cached[0];
            }
        }

        if ($result['header'] !== null || $result['footer'] !== null) {
            return $result;
        }

        $zip = new ZipArchive();
        if ($zip->open($docxPath) !== true) {
            return $result;
        }

        FileFacade::ensureDirectoryExists($targetDir);

        foreach (['header', 'footer'] as $part) {
            $media = $this->firstMediaForPart($zip, $part);
            if ($media === null) {
                continue;
            }

            $contents = $zip->getFromName('word/'.$media);
            if ($contents === false) {
                continue;
            }

            $extension = strtolower(pathinfo($media, PATHINFO_EXTENSION)) ?: 'png';
            $target = $targetDir.'/'.$part.'.'.$extension;
            file_put_contents($target, $contents);
            $result[$part] = $target;
        }

        $zip->close();

        return $result;
    }

    public function hasHeaderImage(string $docxPath): bool
    {
        $zip = new ZipArchive();
        if ($zip->open($docxPath) !== true) {
            return false;
        }

        $found = $this->firstMediaForPart($zip, 'header') !== null;
        $zip->close();

        return $found;
    }

    private function firstMediaForPart(ZipArchive $zip, string $part): ?string
    {
        $relationFiles = [];
        for ($index = 0; $index < $zip->numFiles; $index++) {
            $name = (string) $zip->getNameIndex($index);
            if (preg_match('#^word/_rels/'.$part.'\d*\.xml\.rels$#', $name)) {
                $relationFiles[] = $name;
            }
        }

        $best = null;
        $bestSize = -1;

        foreach ($relationFiles as $relationFile) {
            $xml = (string) $zip->getFromName($relationFile);
            preg_match_all('#Target="(media/[^"]+\.(?:png|jpe?g|gif|bmp))"#i', $xml, $matches);

            foreach ($matches[1] as $media) {
                $size = (int) ($zip->statName('word/'.$media)['size'] ?? 0);
                if ($size > $bestSize) {
                    [$best, $bestSize] = [$media, $size];
                }
            }
        }

        return $best;
    }
}
