PEARX
=====

PEARX - A Non-PEAR-Dependent PEAR library for PHP 5.3 (PSR-0 compliance)

Features:

- Package XML parser.
- Package XML builder.
- Package XML installer.
- Faster than the original PEAR code.
- Can run without PEAR dependency.
- Support Cache.
- PSR-0 compliance.

## Install

    $ git clone https://github.com/phpbrew/PEARX.git
    $ cd PEARX
    $ onion bundle
    $ sudo pear install -f package.xml

## Synopsis

Channel operations:

```php
use CacheKit\FileSystemCache;

$channel = new PEARX\Channel($host);


// find package from the remote pear host
$package = $channel->findPackage('PEAR');



// traverse pear channel categories
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

Parsing Package XML:

```php
    $parser = new PEARX\PackageXml\Parser;

    $package = $parser->parse($file);
    ok($package->getName());
    ok($package->getChannel());
    ok($package->getDate());
    ok($package->getTime());
    ok($package->getDateTime() );

    /* ContentFile objects */
    $contents = $package->getContents();
    ok($contents);

    foreach( $contents as $content ) {
        ok($content->file);
        ok($content->role);
    }

    $filelist = $package->getInstallFileList();
    foreach( $filelist as $install ) {
        ok($install->from);
        ok($install->to);
    }
```


