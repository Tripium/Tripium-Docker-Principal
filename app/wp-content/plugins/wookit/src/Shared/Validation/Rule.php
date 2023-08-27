<?php

namespace WooKit\Shared\Validation;

use Webmozart\Assert\Assert;

class Rule
{
    public static array $aArray = [];

    public static function inArray(array $aArray): array
    {
        return [
            'callback' => ['\Webmozart\Assert\Assert', 'inArray'],
            'compare'  => $aArray
        ];
    }

    /**
     * Check and validate array value. ['timeline' => ['from' => 1, 'to' => 2]]
     * timeline
     *
     * @param array $aValueTypes
     *
     * @return array
     */
    public static function validArrayValue(array $aValueTypes): array
    {
        return [
            'callback' => ['\WooKit\Shared\Validation\Validation', 'validArrayValue'],
            'compare'  => $aValueTypes
        ];
    }

    /**
     * Ensures that the key is existed in an array
     *
     * @param array $aKeys
     *
     * @return array
     */
    public static function allKeyExistsInArray(array $aKeys): array
    {
        return [
            'callback' => ['\WooKit\Shared\Validation\Validation', 'allKeyExistsInArray'],
            'compare'  => $aKeys
        ];
    }

    public static function eq($value): array
    {
        return [
            'callback' => ['\Webmozart\Assert\Assert', 'eq'],
            'compare'  => $value
        ];
    }

    public static function same($value): array
    {
        return [
            'callback' => ['\Webmozart\Assert\Assert', 'same'],
            'compare'  => $value
        ];
    }

    /**
     * //kiểm tra giá trị từng phần tử trong mảng số
     * @param array $value
     * @return array
     */
    public static function validArrayChild(array $value): array
    {
        return [
            'callback' => ['WooKit\Shared\Validation\Validation', 'checkValuesArrayIndex'],
            'compare'  => $value,
        ];
    }

    public static function notEq($value): array
    {
        return [
            'callback' => ['\Webmozart\Assert\Assert', 'notEq'],
            'compare'  => $value
        ];
    }

    public static function greaterThan($value): array
    {
        return [
            'callback' => ['\Webmozart\Assert\Assert', 'greaterThan'],
            'compare'  => $value
        ];
    }

    public static function greaterThanEq($value): array
    {
        return [
            'callback' => ['\Webmozart\Assert\Assert', 'greaterThanEq'],
            'compare'  => $value
        ];
    }

    public static function lessThan($value): array
    {
        return [
            'callback' => ['\Webmozart\Assert\Assert', 'lessThan'],
            'compare'  => $value
        ];
    }

    public static function lessThanEq($value): array
    {
        return [
            'callback' => ['\Webmozart\Assert\Assert', 'lessThanEq'],
            'compare'  => $value
        ];
    }

    public static function count($value): array
    {
        return [
            'callback' => ['\Webmozart\Assert\Assert', 'count'],
            'compare'  => $value
        ];
    }
}
