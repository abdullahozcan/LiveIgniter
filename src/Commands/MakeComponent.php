<?php

namespace LiveIgniter\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * LiveIgniter Make Component Command
 * 
 * Creates a new LiveIgniter component with view
 */
class MakeComponent extends BaseCommand
{
    /**
     * The Command's Group
     */
    protected $group = 'LiveIgniter';

    /**
     * The Command's Name
     */
    protected $name = 'liveigniter:make';

    /**
     * The Command's Description
     */
    protected $description = 'Creates a new LiveIgniter component with its view file.';

    /**
     * The Command's Usage
     */
    protected $usage = 'liveigniter:make <component_name> [options]';

    /**
     * The Command's Arguments
     */
    protected $arguments = [
        'component_name' => 'The name of the component to create (e.g., Counter, UserProfile)',
    ];

    /**
     * The Command's Options
     */
    protected $options = [
        '--namespace' => 'Set the root namespace. Default: "App\LiveComponents".',
        '--suffix'    => 'Append the suffix to the generated file. Default: "".',
        '--force'     => 'Force overwrite existing files.',
        '--view-only' => 'Create only the view file.',
        '--no-view'   => 'Skip creating the view file.',
    ];

    /**
     * Actually execute a command.
     */
    public function run(array $params)
    {
        $componentName = array_shift($params);

        if (empty($componentName)) {
            CLI::error('Component name is required.');
            CLI::write('Usage: ' . $this->usage);
            return;
        }

        // Get options
        $namespace = CLI::getOption('namespace') ?? 'App\LiveComponents';
        $suffix = CLI::getOption('suffix') ?? '';
        $force = CLI::getOption('force') ?? false;
        $viewOnly = CLI::getOption('view-only') ?? false;
        $noView = CLI::getOption('no-view') ?? false;

        // Clean component name
        $componentName = $this->cleanComponentName($componentName);
        $componentName .= $suffix;

        CLI::write("Creating LiveIgniter component: {$componentName}", 'yellow');

        try {
            if (!$viewOnly) {
                $this->createComponentFile($componentName, $namespace, $force);
            }

            if (!$noView) {
                $this->createViewFile($componentName, $force);
            }

            CLI::write("Component '{$componentName}' created successfully!", 'green');
            CLI::newLine();
            
            if (!$viewOnly) {
                CLI::write("Component class: {$namespace}\\{$componentName}", 'cyan');
            }
            
            if (!$noView) {
                CLI::write("View file: views/components/" . $this->getViewName($componentName) . ".php", 'cyan');
            }
            
            CLI::newLine();
            CLI::write("Usage in views:", 'yellow');
            CLI::write("<?= live_component('{$namespace}\\{$componentName}') ?>", 'white');

        } catch (\Exception $e) {
            CLI::error('Error creating component: ' . $e->getMessage());
        }
    }

    /**
     * Clean and format component name
     */
    protected function cleanComponentName(string $name): string
    {
        // Remove any file extension
        $name = preg_replace('/\.php$/', '', $name);
        
        // Convert to PascalCase
        $name = str_replace(['-', '_', ' '], '', ucwords($name, '-_ '));
        
        return $name;
    }

    /**
     * Create the component PHP class file
     */
    protected function createComponentFile(string $componentName, string $namespace, bool $force): void
    {
        $namespacePath = str_replace('\\', DIRECTORY_SEPARATOR, $namespace);
        $appPath = APPPATH;
        
        // Remove 'App' from the beginning if present
        if (strpos($namespacePath, 'App' . DIRECTORY_SEPARATOR) === 0) {
            $namespacePath = substr($namespacePath, 4);
        }
        
        $filePath = $appPath . $namespacePath . DIRECTORY_SEPARATOR . $componentName . '.php';
        $directory = dirname($filePath);

        // Create directory if it doesn't exist
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Check if file exists
        if (file_exists($filePath) && !$force) {
            CLI::error("Component file already exists: {$filePath}");
            CLI::write("Use --force to overwrite existing files.", 'yellow');
            return;
        }

        $template = $this->getComponentTemplate($componentName, $namespace);
        
        if (file_put_contents($filePath, $template) === false) {
            throw new \RuntimeException("Could not create component file: {$filePath}");
        }

        CLI::write("✓ Created component class: {$filePath}", 'green');
    }

    /**
     * Create the component view file
     */
    protected function createViewFile(string $componentName, bool $force): void
    {
        $viewsPath = APPPATH . 'Views' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR;
        $viewName = $this->getViewName($componentName);
        $filePath = $viewsPath . $viewName . '.php';

        // Create directory if it doesn't exist
        if (!is_dir($viewsPath)) {
            mkdir($viewsPath, 0755, true);
        }

        // Check if file exists
        if (file_exists($filePath) && !$force) {
            CLI::error("View file already exists: {$filePath}");
            CLI::write("Use --force to overwrite existing files.", 'yellow');
            return;
        }

        $template = $this->getViewTemplate($componentName, $viewName);
        
        if (file_put_contents($filePath, $template) === false) {
            throw new \RuntimeException("Could not create view file: {$filePath}");
        }

        CLI::write("✓ Created view file: {$filePath}", 'green');
    }

    /**
     * Get view name from component name
     */
    protected function getViewName(string $componentName): string
    {
        // Convert PascalCase to kebab-case
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $componentName));
    }

    /**
     * Get component class template
     */
    protected function getComponentTemplate(string $componentName, string $namespace): string
    {
        return <<<PHP
<?php

namespace {$namespace};

use LiveIgniter\LiveComponent;

/**
 * {$componentName} Component
 * 
 * Generated by LiveIgniter
 */
class {$componentName} extends LiveComponent
{
    /**
     * Component properties
     */
    public string \$message = 'Hello from {$componentName}!';
    
    /**
     * Component initialization
     */
    public function mount(): void
    {
        // Initialize component state
    }
    
    /**
     * Example method - you can call this from the view
     */
    public function updateMessage(string \$newMessage): void
    {
        \$this->message = \$newMessage;
    }
    
    /**
     * Another example method
     */
    public function reset(): void
    {
        \$this->message = 'Hello from {$componentName}!';
    }
}
PHP;
    }

    /**
     * Get view template
     */
    protected function getViewTemplate(string $componentName, string $viewName): string
    {
        return <<<PHP
<div id="<?= \$componentId ?>" class="live-component {$viewName}-component" x-data="{
    message: '<?= esc(\$message) ?>',
    loading: false,
    tempMessage: ''
}">
    <div class="card">
        <div class="card-header">
            <h5><?= esc(\$message) ?></h5>
        </div>
        
        <div class="card-body">
            <div class="mb-3">
                <label for="message-input" class="form-label">Update Message:</label>
                <div class="input-group">
                    <input 
                        type="text" 
                        id="message-input"
                        class="form-control"
                        placeholder="Enter new message..."
                        igniter:model="tempMessage"
                    >
                    <button 
                        igniter:click="updateMessage"
                        igniter:target="updateMessage"
                        class="btn btn-primary"
                        :disabled="loading"
                    >
                        <span igniter:loading="updateMessage">
                            <i class="spinner-border spinner-border-sm me-1"></i>
                        </span>
                        <span igniter:loading.remove="updateMessage">Update</span>
                    </button>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <button 
                    igniter:click="reset"
                    igniter:target="reset"
                    class="btn btn-secondary" 
                    :disabled="loading"
                >
                    <span igniter:loading="reset">
                        <i class="spinner-border spinner-border-sm me-1"></i>
                    </span>
                    <span igniter:loading.remove="reset">Reset</span>
                </button>
            </div>
        </div>
        
        <div class="card-footer text-muted">
            <small>Component: {$componentName} | ID: <?= \$componentId ?></small>
        </div>
    </div>
</div>

<style>
.{$viewName}-component {
    max-width: 600px;
    margin: 1rem auto;
}

.live-component[x-cloak] {
    display: none !important;
}
</style>
PHP;
    }
}
