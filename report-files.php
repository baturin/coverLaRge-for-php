
function styled_line($number, $line, $color)
{
    return '<div style="background-color: ' . $color . '">' . 
        $lineNumber . '&nbsp;' . htmlentities($line) . 
        '</div>';
}

            $relative_path = preg_replace('/^' . preg_quote($config['SOURCES_DIR'], '/') . '/', '', $file->getPath());

            $results_path = $config['RESULTS_DIR'] . DIRECTORY_SEPARATOR . $relative_path;
            $results_file = $results_path . DIRECTORY_SEPARATOR . $file->getFilename() . '.html';
            if (!is_dir($results_path)) {
                mkdir($results_path, 0777, true);
            }

            $fileLines = file($file->getPathname());
            $fp = fopen($results_file, 'w');
            if ($fp == false) {
                throw new Exception("Failed to open result file '$results_file' for writing");
            }
