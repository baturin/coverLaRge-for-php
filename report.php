<?php

require_once 'config.php';

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($config['SOURCES_DIR']));

define('LINE_NOT_EXECUTED', 1);
define('LINE_UNKNOWN', 2); 
define('LINE_USELESS', 3); // comments, blank lines, etc
define('LINE_EXECUTED', 4);

function styled_line($number, $line, $color)
{
    return '<div style="background-color: ' . $color . '">' . 
        $lineNumber . '&nbsp;' . htmlentities($line) . 
        '</div>';
}

foreach ($iterator as $file) {
    if ($file->isFile()) {
        if (preg_match('/\.php$/', $file->getFilename())) {
            $relative_path = preg_replace('/^' . preg_quote($config['SOURCES_DIR'], '/') . '/', '', $file->getPath());

            $results_path = $config['RESULTS_DIR'] . DIRECTORY_SEPARATOR . $relative_path;
            $results_file = $results_path . DIRECTORY_SEPARATOR . $file->getFilename() . '.html';

            if (!is_dir($results_path)) {
                mkdir($results_path, 0777, true);
            }

            $fileCoverage = array();

            $fileExecuted = false;

            foreach (glob($config['COVERAGE_FILES_DIR'] . DIRECTORY_SEPARATOR . '*') as $coverageFile ) {
                $coverage = unserialize(file_get_contents($coverageFile));
                foreach ($coverage as $filePathname => $lines) {
                    if ($filePathname == $file->getPathname()) {
                        $fileExecuted = true;
                        foreach ($lines as $lineNumber => $lineValue) {
                            if ($lineValue > 0) {
                                $fileCoverage[$lineNumber] = LINE_EXECUTED;
                            } else if ($lineValue == -1) {
                                $fileCoverage[$lineNumber] = LINE_DEAD;
                            } else if ($lineValue == -2) {
                                $fileCoverage[$lineNumber] = LINE_UNUSED;
                            } else {
                                $fileCoverage[$lineNumber] = LINE_NOT_EXECUTED;
                            }
                        }                    
                    }
                }
            }

            $fileLines = file($file->getPathname());
            $fp = fopen($results_file, 'w');

            if ($fp == false) {
                throw new Exception("Failed to open result file '$results_file' for writing");
            }
        
            $lineNumber = 1;
            foreach ($fileLines as $line) {
                if ($fileExecuted) {
                    if (array_key_exists($lineNumber, $fileCoverage)) {
                        if ($fileCoverage[$lineNumber] == LINE_DEAD) {
                            fwrite($fp,  styled_line($lineNumber, $line, 'red'));
                        } else if ($fileCoverage[$lineNumber] == LINE_UNUSED) {
                            fwrite($fp, styled_line($lineNumber, $line, 'grey'));
                        } else if ($fileCoverage[$lineNumber] == LINE_EXECUTED) {
                            fwrite($fp, styled_line($lineNumber, $line, 'green'));
                        } else {
                            throw new Exception();
                        }
                    } else {
                        fwrite($fp, styled_line($lineNumber, $line, 'white'));
                    } 
                } else {
                    fwrite($fp, styled_line($lineNumber, $line, 'red'));
                }

                $lineNumber++;
            }

            fclose($fp);
        }
    }
}

?>
