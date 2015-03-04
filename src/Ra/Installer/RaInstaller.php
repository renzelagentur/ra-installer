<?php

namespace Ra\Installer;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Script\Event;

/**
 * RaInstaller
 *
 * @package   raless
 * @version   GIT: $Id$ PHP5.4 (16.10.2014)
 * @author    Robin Lehrmann <info@renzel-agentur.de>
 * @copyright Copyright (C) 22.10.2014 renzel.agentur GmbH. All rights reserved.
 * @license   http://www.renzel-agentur.de/licenses/raoxid-1.0.txt
 * @link      http://www.renzel-agentur.de/
 * @extend    LibraryInstaller
 *
 */
class RaInstaller extends LibraryInstaller
{

    /**
     * @var array
     */
    protected $_locations = array(
        'ra-module' => 'htdocs/modules/ra',
        'ra-theme'  => 'htdocs/application/views',
        'oxid-base' => 'htdocs'
    );

    /**
     * cleanup deleted modules
     *
     * @param Event $event event
     */
    public static function cleanUpOldModules(Event $event)
    {
        $composer = $event->getComposer();
        $requires = $composer->getPackage()->getRequires();

        $projectPath = str_replace('vendor/vkf/shop', '', $composer->getInstallationManager()->getInstallpath($composer->getPackage()));
        $modulePath = $projectPath . 'htdocs/modules';
        foreach (array('ra', 'vkf') as $renzelVendorDir) {
            foreach (glob($modulePath . '/' . $renzelVendorDir . '/*') as $file) {
                if (is_dir($file)) {
                    $packageId = str_replace($modulePath . '/', '', $file);
                    if (!isset($requires[$packageId])) {
                        self::rmdir($file);
                        $event->getIO()->write('Deleted old package ' . $packageId);
                    }
                }
            }
        }
    }

    /**
     * delete given directory
     *
     * @param string $dir path to delete
     */
    protected static function rmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    if (filetype($dir . '/' . $object) == 'dir') {
                        self::rmdir($dir . '/' . $object);
                    } else {
                        unlink($dir . '/' . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    /**
     * get install path by package
     *
     * @param PackageInterface $package package
     *
     * @return string
     */
    public function getInstallPath(PackageInterface $package)
    {
        if ($package->getType() === 'oxid-base') {
            return $this->_locations[$package->getType()];
        }
        $themeName = "ra";
        $extra = $package->getExtra();
        $installFolder = $this->_locations[$package->getType()] . '/' . str_replace(array($themeName . "/", '-theme'), '', $package->getPrettyName());
        if ($extra) {
            $themeName = $extra["themeName"];
            $installFolder = $this->_locations[$package->getType()] . '/' . $themeName;
        }

        return $installFolder;
    }

    /**
     * is package type supported ?
     *
     * @param string $packageType package name
     *
     * @return bool
     */
    public function supports($packageType)
    {
        return isset($this->_locations[$packageType]);
    }

    /**
     * Installs specific package.
     *
     * @param InstalledRepositoryInterface $repo    repository in which to check
     * @param PackageInterface             $package package instance
     */
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        parent::install($repo, $package);
        $this->generateVendorMetaData();
    }

    /**
     * Generates a vendor metadata file
     */
    protected function generateVendorMetaData()
    {
        touch('htdocs/modules/ra/vendormetadata.php');
    }

    /**
     * Updates specific package.
     *
     * @param InstalledRepositoryInterface $repo    repository in which to check
     * @param PackageInterface             $initial already installed package version
     * @param PackageInterface             $target  updated version
     *
     * @throws InvalidArgumentException if $initial package is not installed
     */
    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
    {
        parent::update($repo, $initial, $target);
        $this->generateVendorMetaData();
    }
}
