<?php

require_once 'config.php';
require_once 'common.php';
require_once 'classes/ReduceFileToFile.php';

class SummarizeCoverageDataProcessor {
    private $coverageFiles;

    public function __construct($coverageFiles)
    {
        $this->coverageFiles = $coverageFiles;
    }

    public function process($fileName)
    {
        global $config;
        $fileCoverage = array();
        $fileExecuted = false;

        $xdebugValues = array();
        foreach ($this->coverageFiles as $coverageFile) {
            $coverage = unserialize(file_get_contents($coverageFile));

            if ($coverage === false) {
                throw new Exception("Failed to unserialize coverage data from '$coverageFile' file");
            }

            foreach ($coverage as $filePathname => $lines) {
                if ($filePathname == $fileName) {
                    $fileExecuted = true;

                    foreach ($lines as $lineNumber => $lineValue) {
                        if ($lineValue > 0) {
                            $xdebugValue = LINE_EXECUTED;
                        } else if ($lineValue == -1) {
                            $xdebugValue = LINE_NOT_EXECUTED;
                        } else if ($lineValue == -2) {
                            $xdebugValue = LINE_UNKNOWN;
                        } else {
                            throw new Exception("Unknow xdebug line value: $lineValue");
                        }
                    } 

                    if (array_key_exists($lineNumber, $xdebugValues)) { 
                        // value for this line already exists in previously processed files
                        $oldXdebugValue = $xdebugValues[$lineNumber];

                        if ($oldXdebugValue == $xdebugValue) {
                            // no changes - skip
                        } else if ($oldXdebugValue == LINE_NOT_EXECUTED && $xdebugValue == LINE_EXECUTED) {
                            $xdebugValues[$lineNumber] = $xdebugValue;
                        } else if ($oldXdebugValue == LINE_EXECUTED && $xdebugValue == LINE_NOT_EXECUTED) {
                            // line is executed at least in one file
                        } else {
                            throw new Exception("Value change prohibited. $oldXdebugValue -> $xdebugValue");
                        }
                    } else { 
                        // initial value, this line number doesn't exits in previously processed files
                        $xdebugValues[$lineNumber] = $xdebugValue;
                    }
                }
            }
        }
        
        $fileLinesCount = count(file($fileName));

        for ($lineNumber = 1; $lineNumber <= $fileLinesCount; $lineNumber++) {
            if ($fileExecuted) {
                if (array_key_exists($lineNumber, $xdebugValues)) {
                    $fileCoverage[$lineNumber] = $xdebugValues[$lineNumber];
                } else {
                    $fileCoverage[$lineNumber] = LINE_USELESS;
                }
            } else {
                $fileCoverage[$lineNumber] = LINE_NOT_EXECUTED;
            }
        }

        return $fileCoverage;
    }
}

$coverageFiles = glob($config['COVERAGE_FILES_DIR'] . DIRECTORY_SEPARATOR . '*');
$fileProcessor = new SummarizeCoverageDataProcessor($coverageFiles);
$reduceFileToFile = new ReduceFileToFile();
$reduceFileToFile->process($config['SOURCES_DIR'], $config['RESULTS_DIR'], '.line-coverage', $fileProcessor);

?>
