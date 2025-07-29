<?php

namespace LiveIgniter\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * LiveIgniter List Components Command
 * 
 * Lists all LiveIgniter components in the project
 */
class ListComponents extends BaseCommand
{
    /**
     * The Command's Group
     */
    protected $group = 'LiveIgniter';

    /**
     * The Command's Name
     */
    protected $name = 'liveigniter:list';

    /**
     * The Command's Description
     */
    protected $description = 'Lists all LiveIgniter components in your project.';

    /**
     * The Command's Usage
     */
    protected $usage = 'liveigniter:list [options]';

    /**
     * The Command's Options
     */
    protected $options = [
        '--path'      => 'Show full file paths.',
        '--methods'   => 'Show public methods for each component.',
        '--namespace' => 'Filter by namespace (default: App\LiveComponents).',
    ];

    /**
     * Actually execute a command.
     */
    public function run(array $params)
    {
        $showPaths = CLI::getOption('path') ?? false;
        $showMethods = CLI::getOption('methods') ?? false;
        $namespace = CLI::getOption('namespace') ?? 'App\LiveComponents';

        CLI::write('🔍 LiveIgniter Components', 'yellow');
        CLI::newLine();

        try {
            $components = $this->findComponents($namespace);

            if (empty($components)) {
                CLI::write('No LiveIgniter components found.', 'yellow');
                CLI::newLine();
                CLI::write('Create your first component with:', 'white');
                CLI::write('php spark liveigniter:make MyComponent', 'cyan');
                return;
            }

            $this->displayComponents($components, $showPaths, $showMethods);

            CLI::newLine();
            CLI::write("Found " . count($components) . " component(s).", 'green');

        } catch (\Exception $e) {
            CLI::error('Error listing components: ' . $e->getMessage());
        }
    }

    /**
     * Find all LiveIgniter components
     */
    protected function findComponents(string $namespace): array
    {
        $components = [];
        $namespacePath = str_replace('\\', DIRECTORY_SEPARATOR, $namespace);
        
        // Remove 'App' from the beginning if present
        if (strpos($namespacePath, 'App' . DIRECTORY_SEPARATOR) === 0) {
            $namespacePath = substr($namespacePath, 4);
        }
        
        $searchPath = APPPATH . $namespacePath;

        if (!is_dir($searchPath)) {
            return $components;
        }

        $files = $this->getPhpFiles($searchPath);

        foreach ($files as $file) {
            $className = $this->getClassNameFromFile($file);
            
            if ($className && $this->isLiveComponent($file)) {
                $components[] = [
                    'name' => $className,
                    'file' => $file,
                    'namespace' => $namespace,
                    'methods' => $this->getPublicMethods($file)
                ];
            }
        }

        return $components;
    }

    /**
     * Get all PHP files recursively
     */
    protected function getPhpFiles(string $directory): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    /**
     * Get class name from file
     */
    protected function getClassNameFromFile(string $filePath): ?string
    {
        $content = file_get_contents($filePath);
        
        if (preg_match('/class\s+(\w+)/', $content, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Check if file contains a LiveComponent class
     */
    protected function isLiveComponent(string $filePath): bool
    {
        $content = file_get_contents($filePath);
        
        return strpos($content, 'extends LiveComponent') !== false ||
               strpos($content, 'use LiveIgniter\\LiveComponent') !== false;
    }

    /**
     * Get public methods from file
     */
    protected function getPublicMethods(string $filePath): array
    {
        $content = file_get_contents($filePath);
        $methods = [];

        if (preg_match_all('/public\s+function\s+(\w+)\s*\([^)]*\)/', $content, $matches)) {
            foreach ($matches[1] as $method) {
                // Skip magic methods and mount/render
                if (!in_array($method, ['__construct', '__destruct', 'mount', 'render'])) {
                    $methods[] = $method;
                }
            }
        }

        return $methods;
    }

    /**
     * Display components information
     */
    protected function displayComponents(array $components, bool $showPaths, bool $showMethods): void
    {
        foreach ($components as $component) {
            CLI::write("📦 {$component['namespace']}\\{$component['name']}", 'cyan');
            
            if ($showPaths) {
                CLI::write("   📁 {$component['file']}", 'light_gray');
            }

            if ($showMethods && !empty($component['methods'])) {
                CLI::write("   🔧 Methods: " . implode(', ', $component['methods']), 'light_gray');
            }

            // Check if view file exists
            $viewName = $this->getViewName($component['name']);
            $viewPath = APPPATH . 'Views/components/' . $viewName . '.php';
            
            if (file_exists($viewPath)) {
                CLI::write("   👁️  View: components/{$viewName}.php", 'green');
            } else {
                CLI::write("   ⚠️  View: components/{$viewName}.php (missing)", 'yellow');
            }

            CLI::newLine();
        }
    }

    /**
     * Get view name from component name
     */
    protected function getViewName(string $componentName): string
    {
        // Convert PascalCase to kebab-case
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $componentName));
    }
}
