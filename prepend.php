<?php

require_once 'config.php';

xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);

function get_unique_filename($base_dir)
{
    do {
        $filename = $base_dir . DIRECTORY_SEPARATOR . uniqid();
    } while (file_exists($filename));

    return $filename;
}

function create_coverage_results_dir()
{
    global $config;

    if (!is_dir($config['COVERAGE_FILES_DIR'])) {
        $result = mkdir($config['COVERAGE_FILES_DIR']);

        if ($result === false) {
            throw new Exception('Failed to create directory');
        }
    }
}

function coverage_stop()
{
    global $config;

    $code_coverage = xdebug_get_code_coverage();
    xdebug_stop_code_coverage();

    create_coverage_results_dir();

    file_put_contents(
        get_unique_filename($config['COVERAGE_FILES_DIR']), 
        serialize($code_coverage)
    );
}

register_shutdown_function('coverage_stop');
 
?>
