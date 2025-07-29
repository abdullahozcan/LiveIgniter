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

        // Use the publish command
        $command = command('liveigniter:publish');
        if ($force) {
            $command->run(['--force']);
        } else {
            $command->run([]);
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

        // Use the make component command
        $makeCommand = command('liveigniter:make');
        $options = ['Counter'];
        
        if ($force) {
            $options[] = '--force';
        }

        $makeCommand->run($options);
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
