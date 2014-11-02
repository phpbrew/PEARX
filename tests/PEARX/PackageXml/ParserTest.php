<?php

class ParserTest extends PHPUnit_Framework_TestCase
{

    public function packageXmlFileProvider()
    {
        $files =  glob('tests/data/package_xml/**/package.xml');
        return array_map(function($file) { 
            return array($file);
        }, $files);
    }


    /**
     * @dataProvider packageXMlFileProvider
     */
    public function testParser($file)
    {
        $parser = new PEARX\PackageXml\Parser;
        ok($parser);

        $package = $parser->parse($file);
        ok($package);
        ok($package->name);
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
            ok(strpos($install->from,'//') === false);
        }
    }

    public function testForExtension()
    {
        $parser = new PEARX\PackageXml\Parser;
        ok($parser);

        $package = $parser->parse('tests/data/package_xml/xdebug/package.xml');
        ok($package);
        ok($package->name);
        ok($package->getChannel());
        ok($package->getDate());
        ok($package->getTime());
        ok($package->getDateTime() );
        is('xdebug',$package->getProvidesExtension());
        ok($package->getZendExtSrcRelease());
    }

    public function testConfigureOptions() 
    {
        $parser = new PEARX\PackageXml\Parser;
        ok($parser);

        $package = $parser->parse('tests/data/package_xml/apcu/package.xml');
        $options = $package->getConfigureOptions();
        $this->assertNotEmpty($options);

        count_ok( 2, $options);
        foreach($options as $option) {
            ok( isset($option->name) );
            ok( isset($option->prompt) );
            ok( isset($option->default) );
        }

    }

    public function testForCompatibility()
    {
        $parser = new PEARX\PackageXml\Parser;
        ok($parser);

        $package = $parser->parse('tests/data/package_xml/Twig/package.xml');
        ok($package);
        ok($package->name);
        ok($package->getChannel());
        ok($package->getDate());
        ok($package->getTime());
        ok($package->getDateTime() );

        /* ContentFile objects */
        // $contents = $package->getContentFiles();
        // ok($contents);
    }

}

