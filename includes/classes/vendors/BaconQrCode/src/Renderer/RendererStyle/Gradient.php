<?php
declare(strict_types = 1);

namespace BaconQrCode\Renderer\RendererStyle;

use BaconQrCode\Renderer\Color\ColorInterface;

final class Gradient
{
    public function __construct(
        private ColorInterface $startColor,
        private ColorInterface $endColor,
        private GradientType   $type
    ) {
    }

    public function getStartColor() : ColorInterface
    {
        return $this->startColor;
    }

    public function getEndColor() : ColorInterface
    {
        return $this->endColor;
    }

    public function getType() : GradientType
    {
        return $this->type;
    }
}
