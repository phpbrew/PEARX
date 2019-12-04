<?php

class ChannelTest extends PHPUnit_Framework_TestCase
{

    public function testPackageReleaseInfoFinderWithLatest()
    {
        $channel = new PEARX\Channel('pecl.php.net');
        ok($channel);

        $xml = $channel->fetchPackageReleaseXml('apc','latest');
        ok($xml);

        $xml = $channel->fetchPackageReleaseXml('xdebug','2.2.1');
        ok($xml);
    }

    public function testPackageFindPearWithFileSystemCache()
    {
        $cache = new CacheKit\FileSystemCache(array(
            'expiry' => 10,
            'cache_dir' => 'tests/tmp',
        ));
        $channel = new PEARX\Channel('pear.php.net', array( 
            'cache' => $cache,
        ));

        $package = $channel->findPackage('Console_Getopt');
        ok( $package );
        ok( $package->name );
        ok( $package->summary );
        ok( $package->license );

        $package = $channel->findPackage('File_Util');
        ok( $package );
        ok( $package->name );
        ok( $package->summary );
        ok( $package->license );
    }

    public function testPackageFindWithFileSystemCache()
    {
        $cache = new CacheKit\FileSystemCache(array(
            'expiry' => 10,
            'cache_dir' => dirname(__FILE__) . '../tmp',
        ));
        $channel = new PEARX\Channel('pear.php.net', array(
            'cache' => $cache,
        ));

        $package = $channel->findPackage('PEAR');
        ok( $package );
        ok( $package->name );
        is( 'PEAR' , $package->name );
        ok( $package->summary );
        ok( $package->license );
    }

    function getChannels()
    {
        return array(
            array('pear.symfony.com'),
        );
    }

    /**
     * @dataProvider getChannels
     */
    function testChannel($host)
    {
        $channel = new PEARX\Channel($host);
        ok( $channel );

        $url = $channel->getRestBaseUrl();
        ok( $url );

        $packages = $channel->getPackages();

        foreach( $packages as $p ) {
            ok( $p );
        }

        $categories = $channel->getCategories();
        ok( $categories );

        foreach( $categories as $category ) {
            ok( $category->name , 'category name' );

            $packages = $category->getPackages();
            foreach( $packages as $packageName => $package ) {
                ok( $package->name );
                ok( $package->summary );
                ok( $package->description );
                ok( $package->license );
                ok( $package->deps );

                foreach( $package->releases as $r ) {
                    ok( $r->version );
                    ok( $r->stability );
                }
            }
        }
    }

}

