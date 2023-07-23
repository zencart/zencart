<?php

namespace Spatie\Invade\PHPStan;

use PHPStan\Reflection\ClassMemberReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;

class InvadedMethodReflection implements MethodReflection
{
    public function __construct(
        private MethodReflection $method,
    ) {
    }

    public function getDeclaringClass(): ClassReflection
    {
        return $this->method->getDeclaringClass();
    }

    public function isStatic(): bool
    {
        return $this->method->isStatic();
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
        return $this->method->getDocComment();
    }

    public function getName(): string
    {
        return $this->method->getName();
    }

    public function getPrototype(): ClassMemberReflection
    {
        return $this->method->getPrototype();
    }

    public function getVariants(): array
    {
        return $this->method->getVariants();
    }

    public function isDeprecated(): TrinaryLogic
    {
        return $this->method->isDeprecated();
    }

    public function getDeprecatedDescription(): ?string
    {
        return $this->method->getDeprecatedDescription();
    }

    public function isFinal(): TrinaryLogic
    {
        return $this->method->isFinal();
    }

    public function isInternal(): TrinaryLogic
    {
        return $this->method->isInternal();
    }

    public function getThrowType(): ?Type
    {
        return $this->method->getThrowType();
    }

    public function hasSideEffects(): TrinaryLogic
    {
        return $this->method->hasSideEffects();
    }
}
