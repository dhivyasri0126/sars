<?php

namespace App\Export;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelExport extends AbstractExport
{
    public function export(): void
    {
        $this->setHeaders('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Write headers
        if (!empty($this->data)) {
            $headers = array_keys(reset($this->data));
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '1', $header);
                $col++;
            }
        }
        
        // Write data rows
        $row = 2;
        foreach ($this->data as $dataRow) {
            $col = 'A';
            foreach ($dataRow as $value) {
                $sheet->setCellValue($col . $row, $value);
                $col++;
            }
            $row++;
        }
        
        // Auto-size columns
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }
} 