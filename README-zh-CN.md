IDE Helper Generator - Beta 
===========================================================

IDE Helper 自动生成.

# 目的
IDE 代码自动完成.

## 支持

* 0.x： php 7.2 - 7.4
* 1.x： php 7.4 - 8.x 开发中

## 使用

```
$ php-extension-stub-generator.phar dump-files {extension name} {dir} 
```

## 使用案例

```
$ php-extension-stub-generator.phar dump-files ast tmp
```

```
$ php -d extension=/home/you/git/nikic_php-ast/modules/ast.so php-extension-stub-generator.phar dump-files ast tmp
```


## TODO: BUILDING phar



## 可能需要看的

  - [IDE Help 使用](http://stackoverflow.com/questions/30328805/phpstorm-how-to-add-method-stubs-from-a-pecl-library-that-phpstorm-doesnt-curr)
