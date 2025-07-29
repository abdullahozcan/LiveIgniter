# LiveIgniter Spark Commands Usage Examples

## Installation and Setup

### 1. Install LiveIgniter
```bash
# Complete installation (recommended)
php spark liveigniter:install

# Skip publishing files (if already done)
php spark liveigniter:install --skip-publish

# Skip route setup
php spark liveigniter:install --skip-routes

# Force overwrite existing files
php spark liveigniter:install --force
```

### 2. Publish Files Separately
```bash
# Publish all files
php spark liveigniter:publish

# Publish only assets (JavaScript files)
php spark liveigniter:publish --assets

# Publish only configuration files
php spark liveigniter:publish --config

# Publish only example views
php spark liveigniter:publish --views

# Force overwrite existing files
php spark liveigniter:publish --force
```

## Component Development

### 3. Create Components
```bash
# Basic component creation
php spark liveigniter:make Counter

# Custom namespace
php spark liveigniter:make UserProfile --namespace="App\Components"

# Create with suffix
php spark liveigniter:make Button --suffix="Component"

# Create only view file (component class exists)
php spark liveigniter:make Counter --view-only

# Create only component class (view exists)
php spark liveigniter:make Counter --no-view

# Force overwrite existing files
php spark liveigniter:make Counter --force
```

### Examples of Generated Components:
```bash
# Creates: App\LiveComponents\Counter + views/components/counter.php
php spark liveigniter:make Counter

# Creates: App\LiveComponents\UserProfile + views/components/user-profile.php
php spark liveigniter:make UserProfile

# Creates: App\LiveComponents\TodoList + views/components/todo-list.php
php spark liveigniter:make TodoList

# Creates: App\Components\ContactForm + views/components/contact-form.php
php spark liveigniter:make ContactForm --namespace="App\Components"
```

## Project Management

### 4. List Components
```bash
# List all components
php spark liveigniter:list

# Show full file paths
php spark liveigniter:list --path

# Show public methods for each component
php spark liveigniter:list --methods

# Filter by namespace
php spark liveigniter:list --namespace="App\Components"
```

### 5. Clean Up
```bash
# Clean expired sessions and cache
php spark liveigniter:clean

# Clean only component sessions
php spark liveigniter:clean --sessions

# Clean only component cache
php spark liveigniter:clean --cache

# Set custom age limit (default: 3600 seconds)
php spark liveigniter:clean --age=7200

# Dry run (see what would be cleaned)
php spark liveigniter:clean --dry-run
```

## Typical Workflow

### Starting a New Project
```bash
# 1. Install CodeIgniter 4
composer create-project codeigniter4/appstarter my-project

# 2. Install LiveIgniter
cd my-project
composer require liveigniter/liveigniter

# 3. Set up LiveIgniter
php spark liveigniter:install

# 4. Create your first component
php spark liveigniter:make TodoApp

# 5. Check what was created
php spark liveigniter:list --methods
```

### Creating Multiple Components
```bash
# User management components
php spark liveigniter:make UserList
php spark liveigniter:make UserForm
php spark liveigniter:make UserProfile

# E-commerce components
php spark liveigniter:make ProductCatalog
php spark liveigniter:make ShoppingCart
php spark liveigniter:make Checkout

# Check all components
php spark liveigniter:list --path --methods
```

### Maintenance
```bash
# Weekly cleanup
php spark liveigniter:clean --age=86400

# Check what would be cleaned
php spark liveigniter:clean --dry-run

# Re-publish updated assets
php spark liveigniter:publish --assets --force
```

## Advanced Usage

### Custom Namespaces
```bash
# Create admin components
php spark liveigniter:make AdminDashboard --namespace="App\Admin\Components"
php spark liveigniter:make UserManager --namespace="App\Admin\Components"

# Create API components
php spark liveigniter:make ApiStatus --namespace="App\Api\Components"

# List specific namespace
php spark liveigniter:list --namespace="App\Admin\Components"
```

### Component Variations
```bash
# Form components
php spark liveigniter:make ContactForm
php spark liveigniter:make LoginForm
php spark liveigniter:make RegistrationForm

# Display components
php spark liveigniter:make DataTable
php spark liveigniter:make Chart
php spark liveigniter:make Modal

# Interactive components
php spark liveigniter:make ImageUploader
php spark liveigniter:make FileManager
php spark liveigniter:make RealTimeChat
```

### Development Tips
```bash
# Create component without view (you'll create custom view)
php spark liveigniter:make CustomWidget --no-view

# Create view only (component exists)
php spark liveigniter:make CustomWidget --view-only

# Force recreate everything
php spark liveigniter:make MyComponent --force

# Check what components need views
php spark liveigniter:list --path
```
