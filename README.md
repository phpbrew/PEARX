PEARX
=====

PEARX - A Non-PEAR-Installer-Dependent PEAR Channel Library.

Features:

- Fast
- Non-PEAR dependency
- Support Cache

## Install

    $ git clone git://github.com/c9s/PEARX.git
    $ cd PEARX
    $ onion bundle
    $ sudo pear install -f package.xml

## Synopsis

```php
<?php
    use CacheKit\FileSystemCache;

    $channel = new PEARX\Channel($host);
    $categories = $channel->getCategories();

    foreach( $categories as $category ) {
        // $category->name
        // $category->infoUrl

        $packages = $category->getPackages();
        foreach( $packages as $package ) {
            $package->name;
            $package->summary;
            $package->desc;
            $package->channel;
            $package->license;
            $package->deps;
            $package->releases;

            $package->stable; // version string
            $package->alpha;  // version string
            $package->latest; // version string

            $stability = $package->getRelease('0.0.1');
        }
    }
```


To use PEARX with Cache and CurlDownlaoder

```php
<?php
    $cache = new CacheKit\FileSystemCache(array(
        'expiry' => 60 * 30, // 30 minutes
        'cache_dir' => '/tmp/cache',
    ));

    $d = new CurlDownloader;
    $d->setProgressHandler( new \CurlKit\ProgressBar );

    $channel = new PEARX\Channel($host, array( 
        'cache' => $cache,
        'downloader' => $d,
    ));
```


