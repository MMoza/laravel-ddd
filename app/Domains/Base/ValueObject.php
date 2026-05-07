<?php

namespace App\Domains\Base;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

abstract class ValueObject implements Arrayable, JsonSerializable
{
    abstract public function getValue(): mixed;

    abstract public function isSame(ValueObject $valueObject): bool;

    public function equals(?ValueObject $valueObject): bool
    {
        if (is_null($valueObject)) {
            return false;
        }

        return $this->isSame($valueObject);
    }

    abstract public function __toString(): string;

    public function toArray(): array
    {
        return ['value' => $this->getValue()];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}