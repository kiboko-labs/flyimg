<?php

namespace Flyimg\Image\Command\Factory;

use Flyimg\Image\Command\CommandFactoryInterface;
use Flyimg\Image\Command\CommandInterface;
use Flyimg\Image\Command\ResizeByHeightCommand;
use Imagine\Image\ImagineInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ResizeByHeightFactory implements CommandFactoryInterface
{
    /**
     * @var ImagineInterface
     */
    private $imagine;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @param ImagineInterface $imagine
     */
    public function __construct(ImagineInterface $imagine)
    {
        $this->imagine = $imagine;
        $this->validator = Validation::createValidator();
    }

    /**
     * @return Constraint[]
     */
    private function constraints(): array
    {
        return [
            new Assert\All([
                new Assert\Type('int'),
                new Assert\GreaterThanOrEqual(1)
            ])
        ];
    }

    public function build(...$options): CommandInterface
    {
        if (!$this->validator->validate($options, $this->constraints())) {
            throw new \RuntimeException(
                'Failed to validate the arguments constraints.'
            );
        }

        return new ResizeByHeightCommand($this->imagine, ...$this->toCommandArguments(...$options));
    }

    private function toCommandArguments(int $height): \Generator
    {
        yield $height;
    }
}
