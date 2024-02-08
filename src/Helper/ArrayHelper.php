<?php

namespace App\Helper;

use Doctrine\ORM\PersistentCollection;

abstract class ArrayHelper
{
    public static function diffArray(array $a1, array $a2, $ignoreProperties = []): array
    {
        $r = array();
        foreach ($a1 as $k => $v) {
            if (!in_array($k, $ignoreProperties)) {
                if (array_key_exists($k, $a2)) {
                    if ($v instanceof PersistentCollection && $a2[$k] instanceof PersistentCollection) {
                        $v = $v->toArray();
                        $a2[$k] = $a2[$k]->toArray();
                    }

                    if (is_array($v)) {
                        $rad = ArrayHelper::diffArray($v, $a2[$k]);
                        if (count($rad)) {
                            $r[$k] = $rad;
                        }
                    } else {
                        if ($v != $a2[$k]) {
                            $r[$k] = $v;
                        }
                    }
                } else {
                    $r[$k] = $v;
                }
            }
        }
        return $r;
    }
}
