<?php

declare(strict_types=1);

namespace Mulagent\Utility;

use JsonException;

class Utility
{
    /**
     * @template T
     *
     * @param  array<T>  $arr
     * @param  int  $from
     * @param  int|null  $to
     * @return array<T>
     */
    public static function arraySplice(array $arr, int $from, ?int $to = null): array
    {
        $arrClone = self::arrayClone($arr);
        return array_splice($arrClone, $from, $to);
    }

    /**
     * @template T
     *
     * @param  array<T>  $arr
     * @return array<T>
     */
    public static function arrayClone(array $arr): array
    {
        /* @phpstan-ignore-next-line */
        return array_map(
            fn(mixed $item) => is_array($item) ? self::arrayClone($item) : (is_object($item) ? clone $item : $item),
            $arr
        );
    }

    /**
     * @param  string  $json
     * @return array<mixed>
     * @throws JsonException
     */
    public static function jsonDecode(string $json): array
    {
        return (array)json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param  array<mixed>  $data
     * @return string
     * @throws JsonException
     */
    public static function jsonEncode(array $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR);
    }
}
