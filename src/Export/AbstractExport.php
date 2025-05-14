<?php

namespace App\Export;

abstract class AbstractExport
{
    protected $data;
    protected $filename;

    public function __construct(array $data, string $filename)
    {
        $this->data = $data;
        $this->filename = $filename;
    }

    abstract public function export(): void;
    
    protected function setHeaders(string $contentType): void
    {
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . $this->filename . '"');
        header('Cache-Control: max-age=0');
    }
} 