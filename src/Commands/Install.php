<?php

namespace LiveIgniter\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * LiveIgniter Install Command
 * 
 * Sets up LiveIgniter in a CodeIgniter 4 project
 */
class Install extends BaseCommand
{
    /**
     * The Command's Group
     */
    protected $group = 'LiveIgniter';

    /**
     * The Command's Name
     */
    protected $name = 'liveigniter:install';

    /**
     * The Command's Description
     */
    protected $description = 'Installs and configures LiveIgniter in your CodeIgniter 4 project.';

    /**
     * The Command's Usage
     */
    protected $usage = 'liveigniter:install [options]';

    /**
     * The Command's Options
     */
    protected $options = [
        '--skip-publish' => 'Skip publishing assets and config files.',
        '--skip-routes'  => 'Skip adding routes to Routes.php.',
        '--force'        => 'Force overwrite existing files.',
    ];

    /**
     * Actually execute a command.
     */
    public function run(array $params)
    {
        $skipPublish = CLI::getOption('skip-publish') ?? false;
        $skipRoutes = CLI::getOption('skip-routes') ?? false;
        $force = CLI::getOption('force') ?? false;

        CLI::write('🚀 Installing LiveIgniter...', 'yellow');
        CLI::newLine();

        try {
            // Check if CodeIgniter 4 is installed
            if (!$this->isCodeIgniter4()) {
                CLI::error('This command must be run in a CodeIgniter 4 project.');
                return;
            }

            // Publish files
            if (!$skipPublish) {
                $this->publishFiles($force);
            }

            // Setup routes
            if (!$skipRoutes) {
                $this->setupRoutes($force);
            }

            // Create example component
            $this->createExampleComponent($force);

            CLI::newLine();
            CLI::write('✅ LiveIgniter installation completed!', 'green');
            CLI::newLine();
            $this->showNextSteps();

        } catch (\Exception $e) {
            CLI::error('Installation failed: ' . $e->getMessage());
        }
    }

    /**
     * Check if this is a CodeIgniter 4 project
     */
    protected function isCodeIgniter4(): bool
    {
        return defined('APPPATH') && file_exists(APPPATH . 'Config/App.php');
    }

    /**
     * Publish necessary files
     */
    protected function publishFiles(bool $force): void
    {
        CLI::write('📦 Publishing files...', 'cyan');

        // Publish config files
        $this->publishConfig($force);
        
        // Publish assets
        $this->publishAssets($force);
        
        // Publish views
        $this->publishViews($force);
        
        CLI::write('✓ Files published successfully', 'green');
    }
    
    /**
     * Publish config files
     */
    protected function publishConfig(bool $force): void
    {
        // Publish LiveIgniter config
        $sourceConfig = __DIR__ . '/../Config/LiveIgniter.php';
        $targetConfig = APPPATH . 'Config/LiveIgniter.php';

        if (!file_exists($targetConfig) || $force) {
            // Ensure target directory exists
            $targetDir = dirname($targetConfig);
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            // Read source config and modify namespace
            $configContent = file_get_contents($sourceConfig);
            $configContent = str_replace('namespace LiveIgniter\Config;', 'namespace Config;', $configContent);

            if (file_put_contents($targetConfig, $configContent) !== false) {
                CLI::write("✓ Published config: {$targetConfig}", 'green');
            }
        } else {
            CLI::write('⚠ Config file already exists', 'yellow');
        }
        
        // Publish Services extension
        $targetServices = APPPATH . 'Config/Services.php';
        
        if (!file_exists($targetServices)) {
            // Create basic Services.php
            $servicesContent = '<?php

namespace Config;

use CodeIgniter\Config\BaseService;
use LiveIgniter\ComponentManager;

class Services extends \CodeIgniter\Config\Services
{
    /**
     * Get LiveIgniter Component Manager instance
     */
    public static function liveigniterManager(bool $getShared = true): ComponentManager
    {
        if ($getShared) {
            return static::getSharedInstance(\'liveigniterManager\');
        }
        
        return new ComponentManager();
    }
}';
            
            if (file_put_contents($targetServices, $servicesContent) !== false) {
                CLI::write("✓ Created services: {$targetServices}", 'green');
            }
        } else {
            // Check if our method exists
            $existingContent = file_get_contents($targetServices);
            
            if (strpos($existingContent, 'liveigniterManager') === false) {
                CLI::write('⚠ Please add liveigniterManager method to your Services.php', 'yellow');
            }
        }
    }
    
    /**
     * Publish assets
     */
    protected function publishAssets(bool $force): void
    {
        $sourceAsset = __DIR__ . '/../../public/liveigniter.js';
        $targetAsset = FCPATH . 'assets/js/liveigniter.js';

        if (!file_exists($targetAsset) || $force) {
            // Ensure target directory exists
            $targetDir = dirname($targetAsset);
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            if (copy($sourceAsset, $targetAsset)) {
                CLI::write("✓ Published asset: {$targetAsset}", 'green');
            }
        } else {
            CLI::write('⚠ Asset file already exists', 'yellow');
        }
    }
    
    /**
     * Publish views
     */
    protected function publishViews(bool $force): void
    {
        $sourceView = __DIR__ . '/../../views/components/counter.php';
        $targetView = APPPATH . 'Views/components/counter.php';

        if (!file_exists($targetView) || $force) {
            // Ensure target directory exists
            $targetDir = dirname($targetView);
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            if (copy($sourceView, $targetView)) {
                CLI::write("✓ Published view: {$targetView}", 'green');
            }
        } else {
            CLI::write('⚠ View file already exists', 'yellow');
        }
    }

    /**
     * Setup routes
     */
    protected function setupRoutes(bool $force): void
    {
        CLI::write('🛣️  Setting up routes...', 'cyan');

        $routesFile = APPPATH . 'Config/Routes.php';
        
        if (!file_exists($routesFile)) {
            CLI::error('Routes.php file not found.');
            return;
        }

        $routesContent = file_get_contents($routesFile);
        
        // Check if LiveIgniter routes are already added
        if (strpos($routesContent, 'LiveIgniterRoutes.php') !== false) {
            CLI::write('⚠ LiveIgniter routes already configured.', 'yellow');
            return;
        }

        $routeCode = "\n\n// LiveIgniter Routes\n";
        $routeCode .= "\$routes->group('', ['namespace' => 'LiveIgniter\\Controllers'], function(\$routes) {\n";
        $routeCode .= "    require_once ROOTPATH . 'vendor/liveigniter/liveigniter/routes/LiveIgniterRoutes.php';\n";
        $routeCode .= "});\n";

        // Find the last line before the closing tag
        $lastPos = strrpos($routesContent, '?>');
        if ($lastPos !== false) {
            $routesContent = substr_replace($routesContent, $routeCode . '?>', $lastPos, 2);
        } else {
            $routesContent .= $routeCode;
        }

        if (file_put_contents($routesFile, $routesContent) === false) {
            throw new \RuntimeException("Could not update routes file: {$routesFile}");
        }

        CLI::write("✓ Routes configured in: {$routesFile}", 'green');
    }

    /**
     * Create example component
     */
    protected function createExampleComponent(bool $force): void
    {
        CLI::write('📝 Creating example component...', 'cyan');

        // Create Counter component class
        $componentClass = APPPATH . 'LiveComponents/Counter.php';
        
        if (!file_exists($componentClass) || $force) {
            // Ensure directory exists
            $dir = dirname($componentClass);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            $counterCode = '<?php

namespace App\LiveComponents;

use LiveIgniter\LiveComponent;

class Counter extends LiveComponent
{
    public int $count = 0;
    public string $message = "Hello from LiveIgniter!";
    
    public function increment(): void
    {
        $this->count++;
    }
    
    public function decrement(): void
    {
        $this->count--;
    }
    
    public function reset(): void
    {
        $this->count = 0;
        $this->message = "Counter reset!";
    }
}';
            
            if (file_put_contents($componentClass, $counterCode) !== false) {
                CLI::write("✓ Created component: {$componentClass}", 'green');
            }
        } else {
            CLI::write('⚠ Example component already exists', 'yellow');
        }
    }

    /**
     * Show next steps to the user
     */
    protected function showNextSteps(): void
    {
        CLI::write('🎯 Next Steps:', 'yellow');
        CLI::newLine();
        
        CLI::write('1. Include Alpine.js in your layout (before closing </head> tag):', 'white');
        CLI::write('   <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>', 'cyan');
        CLI::newLine();
        
        CLI::write('2. Include LiveIgniter JavaScript (before closing </body> tag):', 'white');
        CLI::write('   <script src="<?= base_url(\'assets/js/liveigniter.js\') ?>"></script>', 'cyan');
        CLI::newLine();
        
        CLI::write('3. Use the example Counter component in your views:', 'white');
        CLI::write('   <?= live_component(\'App\\LiveComponents\\Counter\') ?>', 'cyan');
        CLI::newLine();
        
        CLI::write('4. Create new components with:', 'white');
        CLI::write('   php spark liveigniter:make YourComponentName', 'cyan');
        CLI::newLine();
        
        CLI::write('📚 Documentation: https://github.com/liveigniter/liveigniter', 'blue');
        CLI::write('🐛 Issues: https://github.com/liveigniter/liveigniter/issues', 'blue');
    }
}
