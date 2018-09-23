<?php

namespace Flyimg\App\Controller;

use Flyimg\App\ImageManipulator;
use Imagine\Exception\InvalidArgumentException;
use League\Flysystem\FileExistsException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class PathExpressionController extends Controller
{
    /**
     * @var ImageManipulator
     */
    private $imageManipulator;

    /**
     * @param ImageManipulator $imageManipulator
     */
    public function __construct(ImageManipulator $imageManipulator)
    {
        $this->imageManipulator = $imageManipulator;
    }

    /**
     * @param string $options
     * @param string $imageSrc
     *
     * @return Response
     */
    public function uploadAction(string $options, string $imageSrc = null): Response
    {
        try {
            $file = $this->imageManipulator->execute($options, $imageSrc);
        } catch (FileExistsException $e) {
            return new Response($e->getMessage(), 500);
        } catch (InvalidArgumentException $e) {
            return new Response('', 404);
        }

        return new Response($file->read(), 200, [
            'Content-Type' => $file->mimeType(),
        ]);
    }

    /**
     * @param string $options
     * @param string $imageSrc
     *
     * @return Response
     */
    public function pathAction(string $options, string $imageSrc = null): Response
    {
        return new Response($imageSrc);
    }
}
