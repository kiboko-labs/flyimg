<?php

namespace Flyimg\App;

interface FileInterface
{
    public function toFlysystem(): string;

    public function mimeType(): string;

    /**
     * @return string
     */
    public function read(): string;

    /**
     * @return resource
     */
    public function readStream();
}
