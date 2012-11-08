<?php

class InstallerTest extends PHPUnit_Framework_TestCase
{

    public function distUrlProvider()
    {
        return array(
            array('http://pear.corneltek.com/get/AssetKit-1.4.1.tgz'),
            array('http://pear.corneltek.com/get/CLIFramework-1.5.7.tgz'),
        );
    }


    /**
     * @dataProvider distUrlProvider
     */
    public function testPearPackageInstaller($distUrl)
    {
        $tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pearx_tests';
        if( ! file_exists($tmpDir) )
            mkdir($tmpDir,0755,true);

        echo "\nUsing temp dir: $tmpDir\n";
        chdir($tmpDir);

        $info = parse_url($distUrl);
        $packageFile = basename($info['path']);
        if( ! file_exists($packageFile) ) {
            system("wget $distUrl");
        }

        $installer = new \PEARX\Installer;
        $installedCnt = $installer->install($packageFile,'vendor/pear');
        ok($installedCnt > 0);
    }
}

