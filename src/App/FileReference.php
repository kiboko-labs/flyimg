<?php

namespace Flyimg\App;

use League\Flysystem\MountManager;

class FileReference implements FileInterface
{
    /**
     * @var MountManager
     */
    private $mountManager;

    /**
     * @var string
     */
    private $path;

    /**
     * @param MountManager $mountManager
     * @param string       $path
     */
    public function __construct(MountManager $mountManager, string $path)
    {
        $this->mountManager = $mountManager;
        $this->path = $path;
    }

    public function toFlysystem(): string
    {
        return $this->path;
    }

    public function mimeType(): string
    {
        return $this->mountManager->getMimetype($this->toFlysystem());
    }

    public function read(): string
    {
        return $this->mountManager->read($this->toFlysystem());
    }

    public function readStream()
    {
        return $this->mountManager->readStream($this->toFlysystem());
    }
}
