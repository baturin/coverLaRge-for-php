<?php

require_once 'common.php';
require_once 'classes/ReduceFileToFile.php';
require_once 'classes/AllFileFilter.php';

class FormatFileAccordingToCoverageProcessor {
    private function styledLine($number, $line, $color)
    {
        return '<div style="background-color: ' . $color . '; white-space: pre; font-family: monospace;">' . 
            $number. '&nbsp;' . htmlentities($line) . 
            '</div>';
    }

    public function process($fileName) {
        echo $fileName . "\n";
        $linesData = unserialize(file_get_contents($fileName));

        $colorsByLineType = array(
            LINE_NOT_EXECUTED => 'red',
            LINE_UNKNOWN => 'grey',
            LINE_USELESS => 'white',
            LINE_EXECUTED => 'green'
        );

        $lineNumber = 1;
        
        $maxLineNumber = count($linesData['linesCoverage']) + 1;
        $lineNumberLength = strlen($maxLineNumber);

        $result = '';
        foreach (file($linesData['sourceFile']) as $line) {
            $result .= $this->styledLine(str_pad($lineNumber, $lineNumberLength, ' ', 'STR_PAD_BOTH'), $line, $colorsByLineType[$linesData['linesCoverage'][$lineNumber]]);
            $lineNumber++;
        }

        return $result;
    }
}

function printUsage() {
    echo 'Make HTML files for each source code file with coverage information.' . PHP_EOL;
    echo 'Usage:' . PHP_EOL;
    echo '  php report-html-file-coverage.php PREPROCESSED_DATA_DIRECTORY HTML_FILES_DIRECTORY' . PHP_EOL;
    echo '    where:' . PHP_EOL;
    echo '      PREPROCESSED_DATA_DIRECTORY - directory with data prepared with prepare.php script' . PHP_EOL;
    echo '      HTML_FILES_DIRECTORY - directory to put HTML files to' . PHP_EOL;
    echo '    all directories must not contain trailing slash' . PHP_EOL;
}

if (count($argv) != 3) {
    echo 'Invalid arguments.' . PHP_EOL;
    echo PHP_EOL;
    printUsage();
    exit(1);
}

$preprocessedDataDir = $argv[1];
$htmlFilesDir = $argv[2];

$fileProcessor = new FormatFileAccordingToCoverageProcessor();
$reduceFileToFile = new ReduceFileToFile();
$reduceFileToFile->process($preprocessedDataDir, $htmlFilesDir, '.html', $fileProcessor, new AllFileFilter());

?>
