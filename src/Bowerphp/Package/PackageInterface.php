<?php

namespace Bowerphp\Package;

use Bowerphp\Repository\RepositoryInterface;
use Composer\Package\PackageInterface as BasePackageInterface;

/**
 * Defines the essential information a package has that is used during solving/installation
 *
 */
interface PackageInterface extends BasePackageInterface
{
    /**
     * Returns the required version of this package
     *
     * @return string version
     */
    public function getRequiredVersion();

    /**
     * Set the required version of this package
     *
     * @param string version
     */
    public function setRequiredVersion($version);

    /**
     * Returns all package info (e.g. info from package's bower.json)
     *
     * @return array
     */
    public function getInfo();
}
