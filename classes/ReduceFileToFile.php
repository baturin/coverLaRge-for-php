<?php

class ReduceFileToFile {
    public function process($srcDir, $dstDir, $newFilesSuffix, $fileProcessor)
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($srcDir));

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                if (preg_match('/\.php$/', $file->getFilename())) {
                    $relative_path = preg_replace('/^' . preg_quote($srcDir, '/') . '/', '', $file->getPath());

                    $results_path = $dstDir . DIRECTORY_SEPARATOR . $relative_path;
                    $results_file = $results_path . DIRECTORY_SEPARATOR . $file->getFilename() . $newFilesSuffix;
                    if (!is_dir($results_path)) {
                        mkdir($results_path, 0777, true);
                    }

                    $result = $fileProcessor->process($file->getPathname());
                    file_put_contents($results_file, serialize($result));
                }
            }
        }
    }    
}

?>
