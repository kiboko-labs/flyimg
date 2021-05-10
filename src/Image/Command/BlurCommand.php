<?php

namespace Flyimg\Image\Command;

use Imagine\Image\BoxInterface;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\PointInterface;

class BlurCommand implements CommandInterface
{
    /**
     * @var ImagineInterface
     */
    private $imagine;

    /**
     * @var PointInterface
     */
    private $start;

    /**
     * @var BoxInterface
     */
    private $box;

    /**
     * @param BoxInterface     $box
     * @param PointInterface   $start
     * @param ImagineInterface $imagine
     */
    public function __construct(
        ImagineInterface $imagine,
        PointInterface $start,
        BoxInterface $box
    ) {
        $this->imagine = $imagine;
        $this->box = $box;
        $this->start = $start;
    }

    public function execute(ImageInterface $input): ImageInterface
    {
        $temporary = $input->copy();
        $temporary
            ->crop($this->start, $this->box)
            ->resize($this->box->scale(.1))
            ->resize($this->box);

        return $input->paste($temporary, $this->start);
    }
}
