# Astatine Template Engine

A very simple and lightweight PHP template engine with a syntax inspired by Laravel's Blade.
It's designed to be easy to use and integrate into your PHP projects without unnecessary complexity.

## ✨ Features

- __Clean Syntax:__ Uses `@` directives and `{{ }}` echo statements, familiar to Laravel developers.

- __Template Inheritance:__ Supports `@extends`, `@section`, and `@yield` for building layouts.

- __Includes:__ Reuse template partials with the `@include` directive.

- __Control Structures:__ Provides convenient directives like `@if`, `@else`, `@foreach`, `@while`, etc.

- __Echoing Data:__ Automatically escapes output with `{{ $variable }}` for security. Use `{!! $variable !!}` for unescaped data.

- __Lightweight & Fast:__ Compiles templates into plain PHP code for optimal performance.


## 🚀 Installation

You can install the package via Composer:
```bash
composer require andrexm/astatine
```

## 📝 Basic Usage

Here's a quick example of how to use Astatine.

### Configuration

First, you need to set up the engine with paths to your template directories.

```php
<?php

require 'vendor/autoload.php';

use Astatine\Engine;

$engine = Engine::getInstance();
$engine::config(
    "views", // Directory for your template files 
    "cache/views", // Directory for your compiled files (must be writable)
    ".php" // OPTIONAL: by default, the engine will look for .blade.php files
);

// Render a template
$engine::render('index', ['name' => 'John Doe']);
```

See `examples/basics.php` file. Also, as mentioned in the example above, the engine will look
for `.blade.php` files, in order to allow you to use extensions for syntax highlighting in Blade. If
you want to change that, just set the `$extension` to `".php"` (as in the example above) or
other stuff.

Also, since you should provide a cache directory, you have to first create that directory:
```bash
mkdir -p cache/views
```


### Example Template (templates/index.at.php)

Here is an example to show how similar it is to Blade. Actually, there is a single difference you
should be aware of: commands that define blocks must end with a `:`, like `@if(...): ... @endif`.
Follow the example below to understand better.

```html
{{-- Template Inheritance Example --}}
@extends('layouts.master')

@section('title', 'Home Page')

@section('content'):
    <h1>Hello, {{ $name }}!</h1>

    @if (count($items) > 0):
        <ul>
            @foreach ($items as $item):
                <li>{{ $item }}</li>
            @endforeach
        </ul>
    @else:
        <p>No items found.</p>
    @endif

    @include('partials.footer')
@endsection
```

### Example Layout (templates/layouts/master.at.php)

```html
<!DOCTYPE html>
<html>
<head>
    <title>@yield('title')</title>
</head>
<body>
    <header>
        {{-- Site header --}}
    </header>

    <main>
        @yield('content')
    </main>

    <footer>
        @yield('footer')
    </footer>
</body>
</html>
```

## 🧠 Template Syntax Overview

Astatine uses `@` prefixed directives and `{{ }}` for echoed variables.

### Control Structures

```php
@if (condition):
    ...
@elseif (condition)
    ...
@else:
    ...
@endif

@foreach ($array as $key => $value):
    ...
@foreach ($array as $value):
    ...
@endforeach

@for ($i = 0; $i < 10; $i++):
    ...
@endfor

@while (condition):
    ...
@endwhile
```

### Template Inheritance

- `@extends('layout.name')`: Specifies the layout the template inherits from.

- `@section('name'): ... @endsection`: Defines a section block.

- `@yield('name')`: In a layout, displays the content of a section.


### Including Partials
- `@include('path.to.partial')`: Includes another template. Partials always have access to parent data.


### Comments
- `{{-- This is a comment --}}`: Comments are not rendered in the final HTML.


### Echoing Data
- `{{ $variable }}`: Echoes escaped data (prevents XSS).

- `{!! $variable !!}`: Echoes unescaped data (use carefully!).


## 🤝 Contributing
Contributions are welcome! Please feel free to submit a Pull Request.

Fork the repository.

1. Create your feature branch (`git checkout -b feature/amazing-feature`).

2. Commit your changes (`git commit -m 'Add some amazing feature'`).

3. Push to the branch (`git push origin feature/amazing-feature`).

4. Open a Pull Request.


## 📄 License
The MIT License (MIT). Please see [License File](LICENSE) for more information.
