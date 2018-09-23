<?php

namespace Flyimg\App;

use League\Flysystem\Adapter\Local;
use League\Flysystem\FileExistsException;
use League\Flysystem\MountManager;

class Filesystem
{
    /**
     * @var MountManager
     */
    private $mountManager;

    /**
     * @var string
     */
    private $localPath;

    /**
     * @var string
     */
    private $localPrefix;

    /**
     * @param MountManager $mountManager
     * @param string       $localPath
     * @param string       $localPrefix
     */
    public function __construct(
        MountManager $mountManager,
        string $localPath,
        string $localPrefix = 'local'
    ) {
        $this->mountManager = $mountManager;
        $this->localPath = $localPath;
        $this->localPrefix = $localPrefix;
    }

    /**
     * @param string $source
     * @param string $destination
     *
     * @return bool
     *
     * @throws FileExistsException
     */
    public function copy(string $source, string $destination): bool
    {
        return $this->mountManager->copy($source, $destination);
    }

    /**
     * @param string $source
     *
     * @return string
     *
     * @throws FileExistsException
     */
    public function localCopy(string $source): string
    {
        $destination = uniqid('flyimg.');

        $this->mountManager->copy($source, $this->localPrefix . '://' . $destination);

        return $this->localPath . '/' . $destination;
    }
}
