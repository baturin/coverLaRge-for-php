<?php

class FilesDirectoryIterator implements Iterator {
    private $filter;

    // iterator state variables
    private $dirIterator;
    private $currentFile;
    private $number;
    
    public function __construct($rootDir, $filter) 
    {
        $this->filter = $filter;
        $this->dirIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootDir));

        $this->currentFile = null;
        $this->number = null;
    }

    private function nextFileName()
    {
        while ($this->dirIterator->valid()) {
            $file = $this->dirIterator->current();
            $this->dirIterator->next();

            if ($file->isFile() && $this->filter->filter($file->getFilename())) {
                $this->currentFile = $file->getPathname();
                $this->number++;
                return;
            }
        }

        $this->currentFile = null;
        $this->number = null;
    }

    public function rewind() 
    {
        $this->dirIterator->rewind();
        $this->nextFileName();
    }

    public function current() 
    {
        return $this->currentFile;
    }

    public function key() 
    {
        return $this->number;
    }

    public function next() 
    {
        $this->nextFileName();
    }

    public function valid() 
    {
        return $this->number !== null;
    }
}

?>
