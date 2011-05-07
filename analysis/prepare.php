<?php

require_once 'common.php';
require_once 'classes/ReduceFileToFile.php';
require_once 'classes/ExtensionFileFilter.php';
require_once 'classes/FilesDirectoryIterator.php';
require_once 'classes/AllFileFilter.php';

class SummarizeCoverageDataProcessor {
    private $coverageFiles;

    public function __construct($coverageFiles)
    {
        $this->coverageFiles = $coverageFiles;
    }

    public function process($fileName)
    {
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

        return serialize(array(
            'sourceFile' => $fileName,
            'linesCoverage' => $fileCoverage
        ));
    }
}

function printUsage() {
    echo 'Utility that prepares raw XDebug coverage data for report-*.php tools.' . PHP_EOL;
    echo 'Usage: ' . PHP_EOL;
    echo '  php prepare.php SOURCES_DIRECTORY RAW_DATA_DIRECTORY PREPROCESSED_DATA_DIRECTORY' . PHP_EOL;
    echo '    where:' . PHP_EOL;
    echo '      SOURCES_DIRECTORY - directory with tested product sources (in the same location as on a computer with XDebug)' . PHP_EOL;
    echo '      RAW_DATA_DIRECTORY - directory with raw php coverage data (produced by XDebug and serialized by prepend.php script)' . PHP_EOL;
    echo '      PREPROCESSED_DATA_DIRECTORY - directory to put pre-processed data that could be used by report-*.php tools' . PHP_EOL;
    echo '    all directories must not contain trailing slash' . PHP_EOL;
}

if (count($argv) != 4) {
    echo 'Invalid arguments.' . PHP_EOL;
    echo PHP_EOL;
    printUsage();
    exit(1);
}

$sourcesDir = $argv[1];
$rawDataDir = $argv[2];
$preprocessedDataDir = $argv[3];

$coverageFiles = new FilesDirectoryIterator($rawDataDir, new AllFileFilter());
$fileProcessor = new SummarizeCoverageDataProcessor($coverageFiles);
$reduceFileToFile = new ReduceFileToFile();
$reduceFileToFile->process($sourcesDir, $preprocessedDataDir, '.line-coverage', $fileProcessor, new ExtensionFileFilter('php'));

?>
