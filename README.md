IDE Helper Generator  - Beta 
===========================================================

PHP ReflectionExtension's Information Rewind to PHP Code As Stub.

# Purpose
Code Completion under IDE.

## support

* 0.x： php 7.2 - 7.4
* 1.x： php 7.4 - 8.x developing


## USAGE

```shell
$ php bin/ide-helper-generator generator:ext {extension name} --dir={dir} 
$ php bin/ide-helper-generator --help
$ php bin/ide-helper-generator generator:ext --help
```

## USAGE Example

```shell
$ ./bin/ide-helper-generator generator:ext swoole
```

```shell
$ php -d extension=/home/you/php/modules/ast.so bin/ide-helper-generator generator:ext swoole
```


## TODO: BUILDING phar



## MOSTELY YOU DON'T NEED

  - http://stackoverflow.com/questions/30328805/phpstorm-how-to-add-method-stubs-from-a-pecl-library-that-phpstorm-doesnt-curr
