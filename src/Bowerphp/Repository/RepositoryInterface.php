<?php

namespace Bowerphp\Repository;

use Composer\Repository\RepositoryInterface as ComposerRepositoryInterface;

/**
 * Repository interface.
 *
 */
interface RepositoryInterface extends ComposerRepositoryInterface
{
    /**
     * Get repo bower.json
     *
     * @param  string  $version
     * @param  boolean $includeHomepage
     * @param  string  $url
     * @return string
     */
    public function getBower($version = 'master', $includeHomepage = false, $url = '');
}
