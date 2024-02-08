<?php

namespace App\BaseClass;

use App\Helper\ArrayHelper;

class BaseEntity implements \Stringable
{
    public bool $showMessage = true;

    public function __toString(): string
    {
        return '';
    }

    public function propertiesChanged(array $entityValues): array
    {
        return array_keys(ArrayHelper::diffArray(
            $this->toArray(),
            $entityValues,
            ['showMessage']
        ));
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}