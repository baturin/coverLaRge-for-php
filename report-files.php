<?php

require_once 'config.php';
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

$fileProcessor = new FormatFileAccordingToCoverageProcessor($coverageFiles);
$reduceFileToFile = new ReduceFileToFile();
$reduceFileToFile->process($config['RESULTS_DIR'], '/home/alexey/phppgadmin/html-result', '.html', $fileProcessor, new AllFileFilter());

?>
