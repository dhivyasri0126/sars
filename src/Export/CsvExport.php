<?php

namespace App\Export;

class CsvExport extends AbstractExport
{
    public function export(): void
    {
        $this->setHeaders('text/csv');
        
        $output = fopen('php://output', 'w');
        
        // Write headers
        if (!empty($this->data)) {
            fputcsv($output, array_keys(reset($this->data)));
        }
        
        // Write data rows
        foreach ($this->data as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
    }
} 