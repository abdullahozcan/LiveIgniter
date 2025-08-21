# LiveIgniter

[![GitHub](https://img.shields.io/github/license/abdullahozcan/LiveIgniter)](https://github.com/abdullahozcan/LiveIgniter/blob/main/LICENSE)
[![GitHub stars](https://img.shields.io/github/stars/abdullahozcan/LiveIgniter)](https://github.com/abdullahozcan/LiveIgniter/stargazers)
[![GitHub forks](https://img.shields.io/github/forks/abdullahozcan/LiveIgniter)](https://github.com/abdullahozcan/LiveIgniter/network)
[![GitHub issues](https://img.shields.io/github/issues/abdullahozcan/LiveIgniter)](https://github.com/abdullahozcan/LiveIgniter/issues)
[![PHP Version](https://img.shields.io/packagist/php-v/liveigniter/liveigniter)](https://packagist.org/packages/liveigniter/liveigniter)
[![Packagist Version](https://img.shields.io/packagist/v/liveigniter/liveigniter)](https://packagist.org/packages/liveigniter/liveigniter)

🚀 **A Livewire-like reactive component system for CodeIgniter 4**

LiveIgniter brings the power of reactive components to CodeIgniter 4, allowing you to build dynamic, interactive web applications with minimal JavaScript. Inspired by Laravel Livewire, LiveIgniter provides a seamless way to create reactive components that automatically sync with the server.

## ✨ Features

- 🔄 **Reactive Components** - Build interactive UI components with automatic server synchronization
- ⚡ **Real-time Updates** - Components update automatically without page refreshes
- 🎯 **HTML Directives** - Use familiar `x-igniter-click`, `x-igniter-model` syntax directly in your HTML
- 🎯 **Alpine.js Integration** - Built on top of Alpine.js for client-side reactivity
- 🛡️ **CSRF Protection** - Built-in security features
- 📱 **Offline Support** - Graceful handling of offline states
- 🎨 **Flexible Styling** - Use any CSS framework or custom styles
- 🔧 **Easy Integration** - Simple installation and setup process
- 🛠️ **Spark Commands** - Generate components with built-in CLI tools
- 📊 **Debug Tools** - Development tools for debugging components

## 📋 Requirements

- PHP 7.4 or higher
- CodeIgniter 4.0 or higher
- Alpine.js (automatically loaded if not present)

## 🚀 Installation

### Via Composer (Recommended)

Install LiveIgniter via Composer:

```bash
composer require liveigniter/liveigniter
```

### Via Git Clone

You can also clone the repository directly:

```bash
git clone https://github.com/abdullahozcan/LiveIgniter.git
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
    <h3>Count: <?= $count ?></h3>
    <button x-igniter-click="increment">+</button>
    <button x-igniter-click="decrement">-</button>
    <button x-igniter-click="reset" x-igniter-confirm="Are you sure?">Reset</button>
    
    <!-- Two-way data binding -->
    <form x-igniter-submit="updateMessage">
        <input type="text" x-igniter-model="tempMessage" placeholder="New message">
        <button type="submit">Update</button>
    </form>    <!-- Keyboard shortcuts -->
    <div igniter:keydown.enter="increment" tabindex="0">
        Press Enter to increment
    </div>
    
    <!-- Auto-refresh and lazy loading -->
    <div igniter:poll="60:refresh">Auto-refreshes every minute</div>
    <div igniter:lazy="loadMore">Loads when visible</div>
    
    <!-- Offline indicator -->
    <div igniter:offline>You are offline</div>
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

#### Primary Functions
- `live_igniter('method')` - Generate x-igniter-click directive
- `live_model('property')` - Generate x-igniter-model directive  
- `live_loading('method')` - Generate x-igniter-loading directive
- `live_component_data(array)` - Generate x-data from component properties

#### Usage Examples
```php
<!-- Component with auto-generated x-data -->
<div id="<?= $componentId ?>"<?= live_component_data(compact('count', 'message', 'loading')) ?>>
    <h3>Count: <span x-text="count"></span></h3>
    <button<?= live_igniter('increment') ?>>+1</button>
    
    <!-- Reactive display -->
    <div x-show="count >= 10" x-transition>
        Congratulations! You reached 10!
    </div>
</div>

<!-- Individual helpers -->
<button<?= live_igniter('save', ['param1', 'param2']) ?>>Save</button>
<input<?= live_model('message') ?> type="text">
<div<?= live_loading('save') ?>>Saving...</div>
```

#### Direct HTML Usage
You can also use directives directly in your HTML:

```html
<!-- Click events -->
<button x-igniter-click="increment">+1</button>
<button x-igniter-click="save('param1', 'param2')">Save</button>

<!-- Form handling -->
<form x-igniter-submit="handleForm">
    <input x-igniter-model="message" type="text">
    <button type="submit">Submit</button>
</form>

<!-- Input events -->
<input x-igniter-change="handleChange" type="text">
<input x-igniter-input="handleInput" type="text"> <!-- Debounced -->
```

#### Legacy Support
- `live_wire('method')` - Alias for live_igniter() (deprecated)

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

We welcome contributions! Please see our [Contributing Guide](https://github.com/abdullahozcan/LiveIgniter/blob/main/CONTRIBUTING.md) for details.

### How to Contribute

1. Fork the repository on GitHub
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Setup

```bash
git clone https://github.com/abdullahozcan/LiveIgniter.git
cd LiveIgniter
composer install
```

## 📄 License

LiveIgniter is open-sourced software licensed under the [MIT license](https://github.com/abdullahozcan/LiveIgniter/blob/main/LICENSE).

## 🙏 Credits

- Inspired by [Laravel Livewire](https://laravel-livewire.com/)
- Built for [CodeIgniter 4](https://codeigniter.com/)
- Powered by [Alpine.js](https://alpinejs.dev/)

## 🔗 Quick Links

- 📦 [Packagist Package](https://packagist.org/packages/liveigniter/liveigniter)
- 📚 [Documentation](https://github.com/abdullahozcan/LiveIgniter/wiki)
- 🎯 [Examples](https://github.com/abdullahozcan/LiveIgniter/tree/main/examples)
- 🐛 [Report Issues](https://github.com/abdullahozcan/LiveIgniter/issues/new)
- 💡 [Feature Requests](https://github.com/abdullahozcan/LiveIgniter/discussions/new)

## 📞 Support

- 📧 Email: support@liveigniter.com
- 🐛 Issues: [GitHub Issues](https://github.com/abdullahozcan/LiveIgniter/issues)
- 💬 Discussions: [GitHub Discussions](https://github.com/abdullahozcan/LiveIgniter/discussions)

---

Made with ❤️ by the LiveIgniter Team
