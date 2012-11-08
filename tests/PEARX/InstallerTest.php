<?php

class InstallerTest extends PHPUnit_Framework_TestCase
{
    public function testPearPackageInstaller()
    {
        $tmpDir = sys_get_temp_dir();
        echo "\nUsing temp dir: $tmpDir\n";
        chdir($tmpDir);

        $url = 'http://pear.corneltek.com/get/AssetKit-1.4.1.tgz';
        $info = parse_url($url);
        $packageFile = basename($info['path']);
        if( ! file_exists($packageFile) ) {
            system("wget $url");
        }

        $installer = new \PEARX\Installer;
        $installedCnt = $installer->install($packageFile,'vendor/pear');
        ok($installedCnt > 0);
    }
}

