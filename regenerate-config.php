<?php
//phpcs:disable

use Magento\Framework\App\Bootstrap;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Config\File\ConfigFilePool;

try {
    require __DIR__ . '/app/bootstrap.php';
} catch (\Exception $e) {
    echo "Autoload error:" . $e->getMessage();
    exit(1);
}

$bootstrap = Bootstrap::create(BP, $_SERVER);

$objectManager = $bootstrap->getObjectManager();

$loader = $objectManager->create('Magento\Framework\Module\ModuleList\Loader');
$deploymentConfigWriter = $objectManager->create('Magento\Framework\App\DeploymentConfig\Writer');
$deploymentConfigReader = $objectManager->create('Magento\Framework\App\DeploymentConfig\Reader');
$output = $objectManager->create('Symfony\Component\Console\Output\ConsoleOutput');

try {

    $all = array_keys($loader->load());

    $configPool = new ConfigFilePool();
    $configFiles = $configPool->getPaths();

    $deploymentConfig = $deploymentConfigReader->load();

    $currentModules = $deploymentConfig[ConfigOptionsListConstants::KEY_MODULES] ?? [];

    $result = [];

    $output->writeln('<info>Regenerating configuration file...</info>');

    foreach ($all as $module) {
        if ((isset($currentModules[$module]) && !$currentModules[$module])) {
            $result[$module] = 0;
        } else {
            $result[$module] = 1;
        }
    }

    $deploymentConfigWriter->saveConfig([ConfigFilePool::APP_CONFIG => ['modules' => $result]], true);

    $output->writeln('<info>Configuration file written.</info>');
} catch (\Exception $e) {
    $output->writeln('<error>' . $e->getMessage() . '</error>');
}
