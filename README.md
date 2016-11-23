# Dravencms gallery module

This is a simple gallery module for dravencms

## Instalation

The best way to install dravencms/gallery is using  [Composer](http://getcomposer.org/):


```sh
$ composer require dravencms/gallery:@dev
```

Then you have to register extension in `config.neon`.

```yaml
extensions:
	gallery: Dravencms\Gallery\DI\GalleryExtension
```
