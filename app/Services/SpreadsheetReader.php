<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use RuntimeException;

/**
 * Lecture minimale de fichiers CSV / XLSX (première feuille) sans dépendance externe.
 * Retourne une liste de lignes, chaque ligne étant un tableau de valeurs (chaînes ou nombres).
 */
class SpreadsheetReader
{
    public function read(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension === 'xlsx') {
            return $this->readXlsx($file->getRealPath());
        }

        if (in_array($extension, ['csv', 'txt'], true)) {
            return $this->readCsv($file->getRealPath());
        }

        throw new RuntimeException('Format non supporté. Utilisez un fichier .xlsx ou .csv.');
    }

    private function readCsv(string $path): array
    {
        $content = file_get_contents($path);
        if ($content === false) {
            throw new RuntimeException('Impossible de lire le fichier CSV.');
        }

        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        if (! mb_check_encoding($content, 'UTF-8')) {
            // Excel (Windows FR) enregistre souvent les CSV en Windows-1252.
            $content = mb_convert_encoding($content, 'UTF-8', 'Windows-1252');
        }

        $firstLine = strtok($content, "\r\n") ?: '';
        $delimiter = substr_count($firstLine, ';') >= substr_count($firstLine, ',') ? ';' : ',';

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $content);
        rewind($handle);

        $rows = [];
        while (($row = fgetcsv($handle, 0, $delimiter, '"', '')) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        return $rows;
    }

    private function readXlsx(string $path): array
    {
        if (! class_exists(\ZipArchive::class)) {
            throw new RuntimeException("L'extension PHP zip n'est pas disponible sur le serveur : importez un fichier .csv.");
        }

        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('Fichier Excel invalide ou corrompu.');
        }

        try {
            $sharedStrings = $this->xlsxSharedStrings($zip);
            $sheetXml = $zip->getFromName($this->xlsxFirstSheetPath($zip));
            if ($sheetXml === false) {
                throw new RuntimeException('Aucune feuille trouvée dans le fichier Excel.');
            }

            $sheet = simplexml_load_string($sheetXml);
            if ($sheet === false) {
                throw new RuntimeException('Feuille Excel illisible.');
            }

            $rows = [];
            foreach ($sheet->sheetData->row ?? [] as $row) {
                // Les lignes vides ne sont pas stockées : on les recrée pour garder la numérotation Excel.
                $rowNumber = (int) $row['r'];
                while ($rowNumber > 0 && count($rows) < $rowNumber - 1) {
                    $rows[] = [];
                }

                $values = [];
                foreach ($row->c as $cell) {
                    $index = $this->xlsxColumnIndex((string) $cell['r']);
                    $values[$index] = $this->xlsxCellValue($cell, $sharedStrings);
                }

                if (empty($values)) {
                    $rows[] = [];
                    continue;
                }

                $line = array_fill(0, max(array_keys($values)) + 1, '');
                foreach ($values as $index => $value) {
                    $line[$index] = $value;
                }
                $rows[] = $line;
            }

            return $rows;
        } finally {
            $zip->close();
        }
    }

    private function xlsxSharedStrings(\ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');
        if ($xml === false) {
            return [];
        }

        $strings = [];
        foreach (simplexml_load_string($xml)->si ?? [] as $item) {
            if (isset($item->t)) {
                $strings[] = (string) $item->t;
                continue;
            }

            // Texte enrichi : concaténer les morceaux.
            $text = '';
            foreach ($item->r as $run) {
                $text .= (string) $run->t;
            }
            $strings[] = $text;
        }

        return $strings;
    }

    private function xlsxFirstSheetPath(\ZipArchive $zip): string
    {
        $workbook = $zip->getFromName('xl/workbook.xml');
        $rels = $zip->getFromName('xl/_rels/workbook.xml.rels');
        if ($workbook === false || $rels === false) {
            return 'xl/worksheets/sheet1.xml';
        }

        $workbookXml = simplexml_load_string($workbook);
        $firstSheet = $workbookXml->sheets->sheet[0] ?? null;
        if (! $firstSheet) {
            return 'xl/worksheets/sheet1.xml';
        }

        $relationId = (string) $firstSheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];
        foreach (simplexml_load_string($rels)->Relationship as $relationship) {
            if ((string) $relationship['Id'] === $relationId) {
                $target = ltrim((string) $relationship['Target'], '/');
                return str_starts_with($target, 'xl/') ? $target : 'xl/' . $target;
            }
        }

        return 'xl/worksheets/sheet1.xml';
    }

    private function xlsxColumnIndex(string $reference): int
    {
        $letters = preg_replace('/[^A-Z]/', '', strtoupper($reference));
        $index = 0;
        foreach (str_split($letters) as $letter) {
            $index = $index * 26 + (ord($letter) - 64);
        }

        return max(0, $index - 1);
    }

    private function xlsxCellValue(\SimpleXMLElement $cell, array $sharedStrings)
    {
        $type = (string) $cell['t'];

        if ($type === 's') {
            return $sharedStrings[(int) $cell->v] ?? '';
        }

        if ($type === 'inlineStr') {
            return (string) ($cell->is->t ?? '');
        }

        $value = (string) ($cell->v ?? '');

        return $type === '' && is_numeric($value) ? $value + 0 : $value;
    }
}
