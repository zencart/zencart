<?php

namespace Spatie\Invade\PHPStan;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;

class InvadedPropertyReflection implements PropertyReflection
{
    public function __construct(
        private PropertyReflection $property,
    ) {
    }

    public function getDeclaringClass(): ClassReflection
    {
        return $this->property->getDeclaringClass();
    }

    public function isStatic(): bool
    {
        return $this->property->isStatic();
    }

    public function isPrivate(): bool
    {
        return false;
    }

    public function isPublic(): bool
    {
        return true;
    }

    public function getDocComment(): ?string
    {
        return $this->property->getDocComment();
    }

    public function getReadableType(): Type
    {
        return $this->property->getReadableType();
    }

    public function getWritableType(): Type
    {
        return $this->property->getWritableType();
    }

    public function canChangeTypeAfterAssignment(): bool
    {
        return $this->property->canChangeTypeAfterAssignment();
    }

    public function isReadable(): bool
    {
        return $this->property->isReadable();
    }

    public function isWritable(): bool
    {
        return $this->property->isWritable();
    }

    public function isDeprecated(): TrinaryLogic
    {
        return $this->property->isDeprecated();
    }

    public function getDeprecatedDescription(): ?string
    {
        return $this->property->getDeprecatedDescription();
    }

    public function isInternal(): TrinaryLogic
    {
        return $this->property->isInternal();
    }
}
