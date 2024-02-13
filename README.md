<!-- Shields -->
[![Contributors][contributors-shield]][contributors-url]
[![Forks][forks-shield]][forks-url]
[![Stargazers][stars-shield]][stars-url]
[![Issues][issues-shield]][issues-url]
[![License][license-shield]][license-url]
![PHP 8.2](https://github.com/helsingborg-stad/Blade/actions/workflows/php-test.yaml/badge.svg)


<a href="https://github.com/helsingborg-stad/Blade">
    <img src="docs/images/hbg-github-logo-combo.png" alt="Logo" width="300">
</a>

# Blade

Use [Laravel Blade](https://laravel.com/docs/blade) in any PHP project.\
**If you don't know about Blade yet, please refer to the [official documentation](https://laravel.com/docs/blade).**
  
[Report Bug](https://github.com/helsingborg-stad/Blade/issues) · [Request Feature](https://github.com/helsingborg-stad/Blade/issues)
  

## Requirements
- PHP ^7.4 | ^8.0
- Composer


## Installation

```php
composer require helsingborg-stad/Blade
```

## Usage

### Initialize blade engine:
This can be done either as a local instance or as a global reusable instance. The global instance is recommended better performance and less memory usage.

#### Locally
```php
$viewPaths    = ['path/to/view/files'];
$cachePath    = 'path/to/cache/files';
$container    = new \Illuminate\Container\Container();
$bladeService = new BladeService($viewPaths, $cachePath, $container);
```

#### Globally (for convenient reuse)
```php
$viewPaths    = ['path/to/view/files'];
$cachePath    = 'path/to/cache/files';
$bladeService = GlobalBladeService::getInstance($viewPaths, $cachePath);

// You can now reuse the same instance by calling:
$sameInstance = GlobalBladeService::getInstance($viewPaths, $cachePath);
```

### Render view

```php
$viewFile = 'foo.blade.php';
$html     = $bladeService->makeView($viewFile)->render();
```

#### Render view with variables

```php
$viewFile = 'foo.blade.php';
$html = $bladeService->makeView($viewFile, ['name' => 'John Doe'])->render();
```


### Register a custom directive

```php
$bladeService->registerDirective('datetime', function ($expression) {
    return "<?php echo with({$expression})->format('F d, Y'); ?>";
});

// The directive can now be used by adding the @datetime directive to a view file.
```

### Register a component

```php
$bladeService->registerComponent('foo', function ($view) {
    $view->with('name', 'John Doe');
});

// The component can now be used by adding @component('foo')@endcomponent to a view file.
```

### Register a component directive
If you have already registered a component. That component can be added as a directive by doing the following:
```php
$bladeService->registerComponentDirective('componentname', 'directivename');

// This will register a directive that can be used by adding @directivename()@enddirectivename to a view file, and it will output the component.
```

### Add additional view file paths
If you need to add more view file paths after initializing the Blade Service, this can be done by calling `BladeService::addViewPath`
```php
$bladeService->addViewPath('extra/view/path');
```

> [!WARNING]
> For every unique view path added, performance will be affected. This is due to the fact that the Blade Service will have to search through all view paths to find the correct view file. Therefore, it is recommended to add as few view paths as possible.

## Testing

### Unit tests
```bash
composer test
```

### Code coverage
```bash
composer test:coverage
```

<!-- MARKDOWN LINKS & IMAGES -->
<!-- https://www.markdownguide.org/basic-syntax/#reference-style-links -->
[contributors-shield]: https://img.shields.io/github/contributors/helsingborg-stad/Blade.svg?style=flat-square
[contributors-url]: https://github.com/helsingborg-stad/Blade/graphs/contributors
[forks-shield]: https://img.shields.io/github/forks/helsingborg-stad/Blade.svg?style=flat-square
[forks-url]: https://github.com/helsingborg-stad/Blade/network/members
[stars-shield]: https://img.shields.io/github/stars/helsingborg-stad/Blade.svg?style=flat-square
[stars-url]: https://github.com/helsingborg-stad/Blade/stargazers
[issues-shield]: https://img.shields.io/github/issues/helsingborg-stad/Blade.svg?style=flat-square
[issues-url]: https://github.com/helsingborg-stad/Blade/issues
[license-shield]: https://img.shields.io/github/license/helsingborg-stad/Blade.svg?style=flat-square
[license-url]: https://raw.githubusercontent.com/helsingborg-stad/Blade/main/LICENSE