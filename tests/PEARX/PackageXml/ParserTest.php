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

