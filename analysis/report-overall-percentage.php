<?php

require_once 'common.php';
require_once 'classes/ReduceFileToFile.php';
require_once 'classes/AllFileFilter.php';
require_once 'classes/FilesDirectoryIterator.php';

class CoveragePercentageProcessor {
    private $totalLines;
    private $uncoveredLines;

    public function __construct()
    {
        $this->totalLines = 0;
        $this->uncoveredLines = 0;
    }

    public function process($fileName) {
        $linesData = unserialize(file_get_contents($fileName));
        $this->totalLines += count($linesData['linesCoverage']);

        foreach ($linesData['linesCoverage'] as $lineStatus) {
            if ($lineStatus == LINE_NOT_EXECUTED) {
                $this->uncoveredLines++;
            } 
        }
    }

    public function getTotalLines() {
        return $this->totalLines;
    }

    public function getUncoveredLines() {
        return $this->uncoveredLines;
    }

    public function getCoveredLines() {
        return $this->getTotalLines() - $this->getUncoveredLines();
    }
}

function printUsage() {
    echo 'Print total coverage statistics.' . PHP_EOL;
    echo 'Usage:' . PHP_EOL;
    echo '  php report-overall-percentage.php PREPROCESSED_DATA_DIRECTORY' . PHP_EOL;
    echo '    where:' . PHP_EOL;
    echo '      PREPROCESSED_DATA_DIRECTORY - directory with data prepared with prepare.php script' . PHP_EOL;
    echo '    all directories must not contain trailing slash' . PHP_EOL;
}

if (count($argv) != 2) {
    echo 'Invalid arguments.' . PHP_EOL;
    echo PHP_EOL;
    printUsage();
    exit(1);
}

$preprocessedDataDir = $argv[1];

$processor = new CoveragePercentageProcessor();
foreach (new FilesDirectoryIterator($preprocessedDataDir, new AllFileFilter()) as $fileName) {
    $processor->process($fileName);
}

function formatPercents($floatValue)
{
    return sprintf('%.2f', $floatValue) . '%'; 
}

echo 'Total lines: ' . $processor->getTotalLines() . PHP_EOL;
echo 'Covered lines: ' . $processor->getCoveredLines() . PHP_EOL;
echo 'Uncovered lines: ' . $processor->getUncoveredLines() . PHP_EOL;
echo 'Cover percentage: ' . formatPercents($processor->getCoveredLines() / $processor->getTotalLines()) .  PHP_EOL;

?>
