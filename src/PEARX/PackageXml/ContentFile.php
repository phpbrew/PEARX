<?php
/*
 * This file is part of the Onion package.
 *
 * (c) Yo-An Lin <cornelius.howl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace PEARX\PackageXml;

class ContentFile
{
    /**
     * Follows the spec of http://pear.php.net/manual/en/guide.developers.package2.contents.php
     */
    public $file;

    public $installAs;

    public $role;

    public $md5sum;


    /**
     * @param string $file file path
     */
    public function __construct($file)
    {
        $this->file = $file;
    }


    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file)
    {
        $this->file = $file;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setRole($role)
    {
        $this->role = $role;
    }

    public function getInstallAs()
    {
        if( $this->installAs )
            return $this->installAs;
        return $this->file;
    }
}

