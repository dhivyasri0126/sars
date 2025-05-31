<?php
class SimpleXLSXGen {
    private $data = [];
    private $filename = 'export.csv';
    
    public function __construct($filename = 'export.csv') {
        $this->filename = $filename;
    }
    
    public function addRow($row) {
        $this->data[] = $row;
    }
    
    public function download() {
        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $this->filename . '"');
        
        // Create output stream
        $output = fopen('php://output', 'w');
        
        // Add UTF-8 BOM for proper Excel encoding
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Add headers
        if (!empty($this->data)) {
            fputcsv($output, array_keys($this->data[0]));
        }
        
        // Add data rows
        foreach ($this->data as $row) {
            fputcsv($output, $row);
        }
        
        // Close the output stream
        fclose($output);
        exit;
    }
} 