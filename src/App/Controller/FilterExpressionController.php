<?php

namespace Flyimg\App\Controller;

use Flyimg\App\ImageManipulator;
use Imagine\Exception\InvalidArgumentException;
use League\Flysystem\FileExistsException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class FilterExpressionController extends Controller
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
     * @param string $filtersSpec
     * @param string $imageSrc
     *
     * @return Response
     */
    public function uploadAction(string $filtersSpec, string $imageSrc = null): Response
    {
        try {
            $file = $this->imageManipulator->execute($filtersSpec, $imageSrc);
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
     * @param string $filtersSpec
     * @param string $imageSrc
     *
     * @return Response
     */
    public function pathAction(string $filtersSpec, string $imageSrc = null): Response
    {
        return new Response($imageSrc);
    }
}
