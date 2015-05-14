#!/usr/bin/php
<?php
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;
use SporkTools\Core\Job;
use SporkTools\Core\Job\Feature\FeatureInterface;

$args = $argv;

array_shift($args);

$autoload = null;
$config = null;
$root = null; 

while ($arg = array_shift($args)) {
    switch ($arg) {
    	case '-h':
    	case '--help':
    	    help();
    	    break;
    	case '-a':
    	case '--autoload':
    	    $autoload = array_shift($args);
    	    if (null === $autoload) {
    	        help();
    	    }
    	    break;
    	case '-c':
    	case '--config':
    	    $config = array_shift($args);
    	    if (null === $config) {
    	        help();
    	    }
    	    break;
    	case '-r':
    	case '--root':
    	    $root = array_shift($args);
    	    if (null === $root) {
    	        help();
    	    }
    	    break;
	    default:
	        help();
    }
}

if (null == $root) {
    $root = realpath(__DIR__ . '/../../../');
}

if (null == $autoload) {
    $autoload = 'autoload.php';
}
if (is_file($autoload)) {
    require $autoload;
} elseif (is_file($root . DIRECTORY_SEPARATOR . $autoload)) {
        require $root . DIRECTORY_SEPARATOR . $autoload;
} else {
    echo "Autoload script '$autoload' not found " . PHP_EOL;
    exit(1);
}

if (null == $config) {
    $config = 'config/application.config.php';
}
if (is_file($config)) {
    $configuration = require $config;
} elseif (is_file($root . DIRECTORY_SEPARATOR . $config)) {
    $configuration = require $root . DIRECTORY_SEPARATOR . $config;
} else {
    echo "Configuration script '$config' not found" . PHP_EOL;
    exit(1);
}

if (!is_array($configuration)) {
    echo "Configuration script '$config' is not valid. Script should return an array" . PHP_EOL;
    exit(1);
}

$serviceManagerConfig = new ServiceManagerConfig(
        isset($configuration['service_manager']) ? $configuration['service_manager'] : array());
$serviceManager = new ServiceManager($serviceManagerConfig);
$serviceManager->setService('ApplicationConfig', $configuration);
$serviceManager->get('ModuleManager')->loadModules();

if (!$serviceManager->has(Job\ServiceFactory::SERVICE)) {
    echo "Job manager not available from service manager" . PHP_EOL;
    exit(1);
}
$jobManager = $serviceManager->get(Job\ServiceFactory::SERVICE);
if ($jobManager->hasFeature(FeatureInterface::REPORTING)) {
    foreach ($jobManager->getJobs() as $job) {
        $schedule = $job->getSchedule();
        $lastScheduledRun = $schedule->getLast();
        if (null !== $lastScheduledRun) {
            $report = $job->getReport();
            $lastRun = null === $report ? null : $report->getLastRun();
            if (null === $lastRun || $lastRun < $lastScheduledRun) {
                $job->run();
                $job->save();
            }
        }
    }
}

function help() {
    echo PHP_EOL;
    echo "Usage: run [options] <job>" . PHP_EOL . PHP_EOL;
    echo "  -c | --config <file>" . PHP_EOL;
    echo "  -h | --help" . PHP_EOL;
    echo PHP_EOL;
    exit(1);
}