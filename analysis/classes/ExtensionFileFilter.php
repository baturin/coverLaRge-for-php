<?php

class ExtensionFileFilter {
    private $extension;

    public function __construct($extension)
    {
        $this->extension = $extension;
    }

    public function filter($fileName)
    {
        return preg_match('/\.' . preg_quote($this->extension) . '$/', $fileName);
    }
}

?>
