## About
**Component.php** is a component-based system designed to increase site development effectivity.

## How to use

### Structure and running

#### 1. Class autoloader
Register your class autoloader.  
##### Example: *autoloader.php*
```php
spl_autoload_register(function ($class_name) {
    include $class_name . '.php';
});

```

#### 2. Create component
Component's folder structure */components/layout*  
**Notice:** *layout* is a component name and a class name in case if you want to create a model to this component.
- /layout.tpl - html template file **(required file)**
- /layout.php - model class file
##### How to define:
```php
namespace Components\Layout;
use Component;
class Layout extends Component {
  public function __construct() {
    echo 'Component class loaded!';
  }
}
```
- /layout.css - autoloaded css
- /layout.js - autoloaded js

You have to create **.tpl** file, the others is optional.

#### 3. Define component
```php
  $view = new Component('/components/layout');
```

#### 4. Make some operations
```php
  $view -> set('helloMsg', 'Hello World!');
```

#### 5. Display result
```php
  $view -> render();
```

## TPL tags
- **{ $propName }** - displays setted variable value
- **{ $propArrayName.keyName }** - displays setted array variable value
- **{ $propName | pipeName1:arg1:arg2, pipeName2 }** - returns value result from pipe's functions *(/pipes/pipeName.php)*
- **{ if ... }** - if statement
- **{ elseif ... }** - elseif statement
- **{ else }** - else statement
- **{ endif }** - ends if statement
- **{ foreach $propArrayName as varName }** - starts foreach loop and define variable
- **{ .varName }** - displays variable value defined in foreach loop
- **{ endforeach }** - ends foreach loop
- **{ component $propName }** or **{ component Namespace/To/Component }** - loads other component
- **{ path string }** - returns path of the component and adds '/string' suffix
- **{ function funcName }** - displays returned value from model function
- **{% css %}** - includes all css files from components
- **{% js %}** - includes all js files from components
