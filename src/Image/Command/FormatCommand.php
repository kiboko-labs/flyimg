<?php

namespace Flyimg\Image\Command;

use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;

class FormatCommand implements CommandInterface
{
    /**
     * @var ImagineInterface
     */
    private $imagine;

    /**
     * @var string
     */
    private $format;

    /**
     * @param ImagineInterface $imagine
     * @param string $format
     */
    public function __construct(
        ImagineInterface $imagine,
        string $format
    ) {
        $this->imagine = $imagine;
        $this->format = $format;
    }

    public function execute(ImageInterface $input): ImageInterface
    {
        $path = tempnam(sys_get_temp_dir(), 'flyimg.');
        $input->save($path, [
            'format' => $this->format,
        ]);

        return $this->imagine->open($path);
    }
}
