<!-- Use this file to provide workspace-specific custom instructions to Copilot. For more details, visit https://code.visualstudio.com/docs/copilot/copilot-customization#_use-a-githubcopilotinstructionsmd-file -->

# LiveIgniter Copilot Instructions

This is a CodeIgniter 4 Composer package called **LiveIgniter** - a Livewire-like reactive component system.

## Project Structure
- `src/` - Core LiveIgniter classes
  - `LiveComponent.php` - Base component class
  - `ComponentManager.php` - Component lifecycle and method call handler
  - `Controllers/` - HTTP controllers for AJAX endpoints
  - `Config/` - Configuration and service providers
- `public/liveigniter.js` - Alpine.js integration and client-side functionality
- `views/components/` - Component view templates
- `routes/LiveIgniterRoutes.php` - Route definitions
- `examples/` - Example components and usage

## Development Guidelines

### Component Development
- All components should extend `LiveIgniter\LiveComponent`
- Use public properties for reactive data
- Implement `mount()` for component initialization
- Method names should follow camelCase convention
- Use type hints for all method parameters and return types

### View Templates
- Component views should be placed in `views/components/`
- Use Alpine.js directives for client-side reactivity
- Always include the `$componentId` in the root element
- Use LiveIgniter helper functions: `live_wire()`, `live_model()`, etc.

### JavaScript Integration
- Build on Alpine.js for client-side functionality
- Use the LiveIgniter global object for component management
- Follow the Alpine.js directive patterns for custom functionality

### Security Considerations
- Always validate and sanitize user input
- Use CSRF protection for AJAX requests
- Implement rate limiting for component method calls
- Validate component method existence and accessibility

### CodeIgniter 4 Integration
- Follow CodeIgniter 4 naming conventions and patterns
- Use CodeIgniter's service container and dependency injection
- Implement proper error handling and logging
- Use CodeIgniter's validation and security features

### Coding Standards
- Follow PSR-12 coding standards
- Use proper PHP DocBlocks for all classes and methods
- Implement proper error handling and exceptions
- Write clean, readable, and maintainable code

### Testing
- Write unit tests for all component functionality
- Test AJAX endpoints and error handling
- Verify security measures and edge cases
- Test client-side JavaScript functionality

When generating code for this project, consider:
1. CodeIgniter 4 framework patterns and conventions
2. Laravel Livewire-inspired architecture
3. Alpine.js integration patterns
4. PHP 7.4+ features and best practices
5. Security best practices for web applications
