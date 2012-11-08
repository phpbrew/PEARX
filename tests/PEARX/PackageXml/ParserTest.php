<?php

class ParserTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $parser = new PEARX\PackageXml\Parser;
        ok($parser);

        $package = $parser->parse('tests/data/package_xml/Twig/package.xml');
        ok($package);
    }
}

