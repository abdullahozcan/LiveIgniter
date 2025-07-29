# LiveIgniter

🚀 **A Livewire-like reactive component system for CodeIgniter 4**

LiveIgniter brings the power of reactive components to CodeIgniter 4, allowing you to build dynamic, interactive web applications with minimal JavaScript. Inspired by Laravel Livewire, LiveIgniter provides a seamless way to create reactive components that automatically sync with the server.

## ✨ Features

- 🔄 **Reactive Components** - Build interactive UI components with automatic server synchronization
- ⚡ **Real-time Updates** - Components update automatically without page refreshes
- 🎯 **Alpine.js Integration** - Built on top of Alpine.js for client-side reactivity
- 🛡️ **CSRF Protection** - Built-in security features
- 📱 **Offline Support** - Graceful handling of offline states
- 🎨 **Flexible Styling** - Use any CSS framework or custom styles
- 🔧 **Easy Integration** - Simple installation and setup process
- 📊 **Debug Tools** - Development tools for debugging components

## 📋 Requirements

- PHP 7.4 or higher
- CodeIgniter 4.0 or higher
- Alpine.js (automatically loaded if not present)

## 🚀 Installation

Install LiveIgniter via Composer:

```bash
composer require liveigniter/liveigniter
```

### Quick Setup

Run the install command to set up LiveIgniter automatically:

```bash
php spark liveigniter:install
```

This will:
- Publish configuration files
- Copy JavaScript assets
- Set up routes
- Create an example Counter component

### Manual Installation

1. Clone this repository into your CodeIgniter 4 project
2. Add the namespace to your `app/Config/Autoload.php`:

```php
$psr4 = [
    'LiveIgniter' => ROOTPATH . 'vendor/liveigniter/liveigniter/src',
];
```

3. Include the routes in your `app/Config/Routes.php`:

```php
$routes->group('', ['namespace' => 'LiveIgniter\Controllers'], function($routes) {
    require_once ROOTPATH . 'vendor/liveigniter/liveigniter/routes/LiveIgniterRoutes.php';
});
```

4. Publish assets and config:

```bash
php spark liveigniter:publish
```

## 🎯 Quick Start

### 1. Create a Component

Create a new component by extending the `LiveComponent` class:

```php
<?php

namespace App\LiveComponents;

use LiveIgniter\LiveComponent;

class Counter extends LiveComponent
{
    public int $count = 0;
    public string $message = 'Hello from LiveIgniter!';
    
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
    }
}
```

### 2. Create a View

Create a view file in `app/Views/components/counter.php`:

```php
<div id="<?= $componentId ?>" x-data="{ count: <?= $count ?> }">
    <h2 x-text="'<?= esc($message) ?>'"></h2>
    <p>Count: <span x-text="count"></span></p>
    
    <button <?= live_wire('increment') ?>>+</button>
    <button <?= live_wire('decrement') ?>>-</button>
    <button <?= live_wire('reset') ?>>Reset</button>
</div>
```

### 3. Include in Your View

Use the component in any CodeIgniter view:

```php
<!DOCTYPE html>
<html>
<head>
    <title>LiveIgniter Example</title>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="/liveigniter/assets/js/liveigniter.js"></script>
</head>
<body>
    <?= live_component('App\LiveComponents\Counter') ?>
</body>
</html>
```

## 📚 Documentation

### Component Lifecycle

Components go through several lifecycle hooks:

- `mount()` - Called when the component is first created
- `render()` - Called to render the component HTML
- Method calls - Handle user interactions

### Helper Functions

LiveIgniter provides several helper functions for your views:

- `live_wire('method')` - Bind method calls to elements
- `live_model('property')` - Two-way data binding for form inputs
- `live_loading('method')` - Show loading states
- `live_offline()` - Handle offline states
- `live_emit('event')` - Emit events to other components

### Events

Components can communicate with each other using events:

```php
// Emit an event
$this->emit('user.updated', $userId, $userData);

// Listen for events
$this->listen('user.updated', function($userId, $userData) {
    // Handle the event
});
```

### Property Types

LiveIgniter supports various property types:

```php
class MyComponent extends LiveComponent
{
    public string $name = '';
    public int $age = 0;
    public array $items = [];
    public bool $isActive = false;
    public ?User $user = null;
}
```

## 🎯 Spark Commands

LiveIgniter provides several Spark commands to help you develop faster:

### Create Components

```bash
# Create a new component with view
php spark liveigniter:make UserProfile

# Create with custom namespace
php spark liveigniter:make UserProfile --namespace="App\Components"

# Create only the view file
php spark liveigniter:make UserProfile --view-only

# Create only the component class
php spark liveigniter:make UserProfile --no-view

# Force overwrite existing files
php spark liveigniter:make UserProfile --force
```

### Install & Setup

```bash
# Install LiveIgniter (recommended for new projects)
php spark liveigniter:install

# Publish only assets
php spark liveigniter:publish --assets

# Publish only config files
php spark liveigniter:publish --config

# Publish example views
php spark liveigniter:publish --views
```

### Management Commands

```bash
# List all components in your project
php spark liveigniter:list

# Show component methods
php spark liveigniter:list --methods

# Show file paths
php spark liveigniter:list --path

# Clean expired sessions and cache
php spark liveigniter:clean

# Clean only sessions
php spark liveigniter:clean --sessions

# Clean only cache
php spark liveigniter:clean --cache

# Dry run (see what would be cleaned)
php spark liveigniter:clean --dry-run
```

## 🔧 Configuration

Create a configuration file at `app/Config/LiveIgniter.php`:

```php
<?php

namespace Config;

use LiveIgniter\Config\LiveIgniter as BaseLiveIgniter;

class LiveIgniter extends BaseLiveIgniter
{
    public bool $debug = true;
    public int $sessionLifetime = 3600;
    public string $assetsUrl = '/liveigniter/assets/';
    // ... other configuration options
}
```

## 🛠️ Advanced Usage

### Custom Component Methods

```php
class UserProfile extends LiveComponent
{
    public User $user;
    public string $name = '';
    public string $email = '';
    
    public function mount(User $user): void
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
    }
    
    public function save(): void
    {
        $this->user->update([
            'name' => $this->name,
            'email' => $this->email
        ]);
        
        session()->setFlashdata('success', 'Profile updated!');
    }
    
    public function cancel(): void
    {
        $this->name = $this->user->name;
        $this->email = $this->user->email;
    }
}
```

### Real-time Updates

```php
class ChatComponent extends LiveComponent
{
    public array $messages = [];
    
    public function mount(): void
    {
        $this->refreshMessages();
        
        // Listen for new messages
        $this->listen('message.sent', [$this, 'onMessageSent']);
    }
    
    public function sendMessage(string $message): void
    {
        // Save message
        $messageModel = new MessageModel();
        $messageModel->save([
            'user_id' => auth()->id(),
            'message' => $message
        ]);
        
        // Emit to other components
        $this->emit('message.sent', $message);
    }
    
    public function onMessageSent(): void
    {
        $this->refreshMessages();
    }
    
    private function refreshMessages(): void
    {
        $this->messages = (new MessageModel())->findAll();
    }
}
```

## 🔐 Security

LiveIgniter includes several security features:

- CSRF protection for all AJAX requests
- Input sanitization
- Method validation
- Rate limiting
- Origin validation

## 🐛 Debugging

Enable debug mode in your configuration:

```php
public bool $debug = true;
```

Access debug endpoints (development only):
- `/liveigniter/debug/components` - View active components
- `/liveigniter/debug/sessions` - View session data

## 📝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

## 📄 License

LiveIgniter is open-sourced software licensed under the [MIT license](LICENSE).

## 🙏 Credits

- Inspired by [Laravel Livewire](https://laravel-livewire.com/)
- Built for [CodeIgniter 4](https://codeigniter.com/)
- Powered by [Alpine.js](https://alpinejs.dev/)

## 📞 Support

- 📧 Email: support@liveigniter.com
- 🐛 Issues: [GitHub Issues](https://github.com/liveigniter/liveigniter/issues)
- 💬 Discussions: [GitHub Discussions](https://github.com/liveigniter/liveigniter/discussions)

---

Made with ❤️ by the LiveIgniter Team
