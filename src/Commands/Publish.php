<?php

namespace LiveIgniter\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * LiveIgniter Publish Command
 * 
 * Publishes LiveIgniter assets and config files
 */
class Publish extends BaseCommand
{
    /**
     * The Command's Group
     */
    protected $group = 'LiveIgniter';

    /**
     * The Command's Name
     */
    protected $name = 'liveigniter:publish';

    /**
     * The Command's Description
     */
    protected $description = 'Publishes LiveIgniter assets and configuration files.';

    /**
     * The Command's Usage
     */
    protected $usage = 'liveigniter:publish [options]';

    /**
     * The Command's Options
     */
    protected $options = [
        '--assets'  => 'Publish only asset files (JS, CSS).',
        '--config'  => 'Publish only configuration files.',
        '--views'   => 'Publish example view files.',
        '--force'   => 'Force overwrite existing files.',
    ];

    /**
     * Actually execute a command.
     */
    public function run(array $params)
    {
        $assetsOnly = CLI::getOption('assets') ?? false;
        $configOnly = CLI::getOption('config') ?? false;
        $viewsOnly = CLI::getOption('views') ?? false;
        $force = CLI::getOption('force') ?? false;

        CLI::write('Publishing LiveIgniter files...', 'yellow');
        CLI::newLine();

        try {
            if ($configOnly || (!$assetsOnly && !$viewsOnly)) {
                $this->publishConfig($force);
            }

            if ($assetsOnly || (!$configOnly && !$viewsOnly)) {
                $this->publishAssets($force);
            }

            if ($viewsOnly || (!$assetsOnly && !$configOnly)) {
                $this->publishViews($force);
            }

            CLI::newLine();
            CLI::write('LiveIgniter files published successfully!', 'green');
            CLI::newLine();
            CLI::write('Next steps:', 'yellow');
            CLI::write('1. Include liveigniter.js in your layout:', 'white');
            CLI::write('   <script src="/assets/js/liveigniter.js"></script>', 'cyan');
            CLI::write('2. Include Alpine.js before LiveIgniter:', 'white');
            CLI::write('   <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>', 'cyan');
            CLI::write('3. Add routes to your app/Config/Routes.php:', 'white');
            CLI::write('   $routes->group(\'\', [\'namespace\' => \'LiveIgniter\\Controllers\'], function($routes) {', 'cyan');
            CLI::write('       require_once ROOTPATH . \'vendor/liveigniter/liveigniter/routes/LiveIgniterRoutes.php\';', 'cyan');
            CLI::write('   });', 'cyan');

        } catch (\Exception $e) {
            CLI::error('Error publishing files: ' . $e->getMessage());
        }
    }

    /**
     * Publish configuration files
     */
    protected function publishConfig(bool $force): void
    {
        // Publish LiveIgniter config
        $sourceConfig = __DIR__ . '/../Config/LiveIgniter.php';
        $targetConfig = APPPATH . 'Config/LiveIgniter.php';

        if (file_exists($targetConfig) && !$force) {
            CLI::write('⚠ Config file already exists: ' . $targetConfig, 'yellow');
            CLI::write('Use --force to overwrite existing files.', 'yellow');
        } else {
            // Ensure target directory exists
            $targetDir = dirname($targetConfig);
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            // Read source config and modify namespace
            $configContent = file_get_contents($sourceConfig);
            $configContent = str_replace('namespace LiveIgniter\Config;', 'namespace Config;', $configContent);
            $configContent = str_replace('use CodeIgniter\Config\BaseConfig;', "use CodeIgniter\Config\BaseConfig;\nuse LiveIgniter\Config\LiveIgniter as BaseLiveIgniter;", $configContent);
            $configContent = str_replace('class LiveIgniter extends BaseConfig', 'class LiveIgniter extends BaseLiveIgniter', $configContent);

            if (file_put_contents($targetConfig, $configContent) === false) {
                throw new \RuntimeException("Could not create config file: {$targetConfig}");
            }

            CLI::write("✓ Published config: {$targetConfig}", 'green');
        }
        
        // Publish Services extension
        $sourceServices = __DIR__ . '/../Config/AppServices.php';
        $targetServices = APPPATH . 'Config/Services.php';
        
        if (!file_exists($targetServices)) {
            // If Services.php doesn't exist, create it with our content
            $servicesContent = file_get_contents($sourceServices);
            
            if (file_put_contents($targetServices, $servicesContent) === false) {
                throw new \RuntimeException("Could not create services file: {$targetServices}");
            }
            
            CLI::write("✓ Created services: {$targetServices}", 'green');
        } else {
            // If Services.php exists, check if our method is already there
            $existingContent = file_get_contents($targetServices);
            
            if (strpos($existingContent, 'liveigniterManager') === false) {
                // Add our method to existing Services class
                $methodCode = '
    /**
     * Get LiveIgniter Component Manager instance
     */
    public static function liveigniterManager(bool $getShared = true): \LiveIgniter\ComponentManager
    {
        if ($getShared) {
            return static::getSharedInstance(\'liveigniterManager\');
        }
        
        return new \LiveIgniter\ComponentManager();
    }';
                
                // Insert before the last closing brace
                $pos = strrpos($existingContent, '}');
                if ($pos !== false) {
                    $newContent = substr_replace($existingContent, $methodCode . "\n}", $pos, 1);
                    
                    if ($force || CLI::prompt('Services.php exists. Add LiveIgniter service method?', ['y', 'n']) === 'y') {
                        if (file_put_contents($targetServices, $newContent) === false) {
                            throw new \RuntimeException("Could not update services file: {$targetServices}");
                        }
                        CLI::write("✓ Updated services: {$targetServices}", 'green');
                    }
                }
            } else {
                CLI::write('⚠ LiveIgniter service method already exists in Services.php', 'yellow');
            }
        }
    }

    /**
     * Publish asset files
     */
    protected function publishAssets(bool $force): void
    {
        $sourceJs = __DIR__ . '/../../public/liveigniter.js';
        $targetDir = FCPATH . 'assets/js/';
        $targetJs = $targetDir . 'liveigniter.js';

        // Ensure target directory exists
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        if (file_exists($targetJs) && !$force) {
            CLI::write('⚠ Asset file already exists: ' . $targetJs, 'yellow');
            CLI::write('Use --force to overwrite existing files.', 'yellow');
            return;
        }

        if (!copy($sourceJs, $targetJs)) {
            throw new \RuntimeException("Could not copy asset file to: {$targetJs}");
        }

        CLI::write("✓ Published asset: {$targetJs}", 'green');
    }

    /**
     * Publish example view files
     */
    protected function publishViews(bool $force): void
    {
        $sourceViews = __DIR__ . '/../../views/components/';
        $targetViews = APPPATH . 'Views/components/';

        // Ensure target directory exists
        if (!is_dir($targetViews)) {
            mkdir($targetViews, 0755, true);
        }

        $files = glob($sourceViews . '*.php');
        
        foreach ($files as $sourceFile) {
            $filename = basename($sourceFile);
            $targetFile = $targetViews . $filename;

            if (file_exists($targetFile) && !$force) {
                CLI::write("⚠ View file already exists: {$targetFile}", 'yellow');
                continue;
            }

            if (!copy($sourceFile, $targetFile)) {
                CLI::write("✗ Could not copy view file: {$targetFile}", 'red');
                continue;
            }

            CLI::write("✓ Published view: {$targetFile}", 'green');
        }
    }
}
