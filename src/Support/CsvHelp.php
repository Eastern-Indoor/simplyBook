<?php

namespace Jkdow\SimplyBook\Support;

class CsvHelp
{
    protected static $storagePath;

    public static function init($storageDir)
    {
        self::$storagePath = $storageDir;
        if (!file_exists(self::$storagePath)) {
            mkdir(self::$storagePath, 0755, true);
        }
    }
    /**
     * Exports an array of associative arrays to a CSV file.
     *
     * @param array  $data     The data to export.
     * @param string $filePath The file path where the CSV should be saved.
     * @return bool  Returns true on success, false on failure.
     */
    public static function exportToCSV(array $data, string $filePath): bool
    {
        if (empty($data)) {
            return false;
        }

        $fileHandle = fopen(self::$storagePath . $filePath, 'w');
        if (!$fileHandle) {
            return false;
        }

        // Use the keys of the first record as CSV headers.
        fputcsv($fileHandle, array_keys($data[0]));

        // Write each data row.
        foreach ($data as $row) {
            fputcsv($fileHandle, $row);
        }

        fclose($fileHandle);
        return true;
    }

    public static function importFromCSV(string $filePath): array
    {
        $fileHandle = fopen(self::$storagePath . $filePath, 'r');
        if (!$fileHandle) {
            return [];
        }
        $data = [];
        $headers = fgetcsv($fileHandle);
        while (($row = fgetcsv($fileHandle)) !== false) {
            $data[] = array_combine($headers, $row);
        }
        fclose($fileHandle);
        return $data;
    }

    public static function fileExists($filePath): bool
    {
        return file_exists(self::$storagePath . $filePath);
    }
}
