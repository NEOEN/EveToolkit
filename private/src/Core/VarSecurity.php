<?php

namespace Core;

/**
 * Class Security
 * @desc      Validation, Option, Filter Konstanten werden gesetzt
 * @author    Nicolas Andreas <andreas.nicolas@gmx.net>
 * @since     2011-07-18
 * @copyright Nicolas Andreas
 * @package   Core
 */


class VarSecurity {

    /**
     * validation from value
     *
     * @param      $value
     * @param null $typeName
     * @param null $flag
     * @param null $defaultReturn
     * @param null $option
     * @param bool $filter
     *
     * @return mixed|null
     */
    public function validationVar($value, $typeName = null, $flag = null, $defaultReturn = null, $option = null, $filter = false) {
        $typeId = $this->getType($typeName, $filter);
        $options = $this->getOption($option, $flag);
        $filterVar = filter_var($value, $typeId, $options);
        if ($typeName !== 'boolean' && $typeName !== 'bool' && $filterVar === false) {
            return $defaultReturn;
        }

        return $filterVar;
    }

    /**
     * get the type
     *
     * @param string $typeName int|float|url|email|boolean|ip|encoded|special_chars|magic_quotes|string
     * @param bool   $filter
     *
     * @return int
     */
    public function getType($typeName, $filter = false) {
        if (!$typeName) {
            return $filterId = FILTER_UNSAFE_RAW;
        }

        if ($filter) {
            return $this->getFilterKonstante($typeName);
        } else {
            return $this->getValidationKonstante($typeName);
        }
    }

    /**
     * get the option
     *
     * @param string $option min|max|decimal
     * @param string $flag   nullonfailure|ipv4|ipv6|noprivrange|noresrange|array
     *
     * @return array
     */
    public function getOption($option = null, $flag = null) {
        $array['options'] = [];
        $array['flags'] = 0;

        if ($option) {
            $options = explode(';', $option);
            foreach ($options as $option) {
                list($name, $wert) = explode('=', $option);
                $array['options'] = array_merge($array['options'], $this->getOptionArray(\trim($name), trim($wert)));
            }
        }

        if ($flag) {
            $flags = explode(';', $flag);
            foreach ($flags as $flag) {
                $array['flags'] += $this->getFlags($flag);
            }
        }

        return $array;
    }

    /**
     * get option as array
     *
     * @param string $name min|max|decimal
     * @param mixed  $value
     *
     * @return array
     */
    protected function getOptionArray($name, $value) {
        switch ($name) {
            case 'min':
                $array['min_range'] = $value;
                break;
            case 'max':
                $array['max_range'] = $value;
                break;
            case 'decimal':
                $array['decimal'] = $value;
                break;
            default;
                if ($name) {
                    $array = [$value, $name];
                } else {
                    $array = [];
                }
        }

        return $array;
    }

    /**
     * get flag konstante
     *
     * @param string $flag nullonfailure|ipv4|ipv6|noprivrange|noresrange|array
     *
     * @return int
     */
    protected function getFlags($flag) {
        switch ($flag) {
            case 'nullonfailure':
                $flagKonstante = FILTER_NULL_ON_FAILURE;
                break;
            case 'ipv4':
                $flagKonstante = FILTER_FLAG_IPV4;
                break;
            case 'ipv6':
                $flagKonstante = FILTER_FLAG_IPV6;
                break;
            case 'noprivrange':
                $flagKonstante = FILTER_FLAG_NO_PRIV_RANGE;
                break;
            case 'noresrange':
                $flagKonstante = FILTER_FLAG_NO_RES_RANGE;
                break;
            case 'array':
            default:
                $flagKonstante = FILTER_REQUIRE_ARRAY;
                break;
        }

        return $flagKonstante;
    }

    /**
     * get the validation konstante
     *
     * @param string $filter int|float|url|email|boolean|ip|encoded|special_chars|magic_quotes|string|
     *
     * @return int
     */
    protected function getValidationKonstante($filter) {
        switch ($filter) {
            case 'int':
            case 'integer':
                $validationKonstante = FILTER_VALIDATE_INT;
                break;
            case 'float':
                $validationKonstante = FILTER_VALIDATE_FLOAT;
                break;
            case 'url':
                $validationKonstante = FILTER_VALIDATE_URL;
                break;
            case 'email':
                $validationKonstante = FILTER_VALIDATE_EMAIL;
                break;
            case 'boolean':
            case 'bool':
                $validationKonstante = FILTER_VALIDATE_BOOLEAN;
                break;
            case 'ip':
                $validationKonstante = FILTER_VALIDATE_IP;
                break;
            case 'encoded':
            case 'special_chars':
            case 'magic_quotes':
            case 'string':
                $validationKonstante = $this->getFilterKonstante($filter);
                break;
            default:
                if ($filter) {
                    $validationKonstante = FILTER_CALLBACK;
                } else {
                    $validationKonstante = FILTER_UNSAFE_RAW;
                }
        }

        return $validationKonstante;
    }

    /**
     * get the filter konstante
     *
     * @param string $filter int|float|url|email|string|boolean|ip|encoded|special_chars|magic_quotes
     *
     * @return int
     */
    protected function getFilterKonstante($filter) {
        switch ($filter) {
            case 'int':
            case 'integer':
                $filterKonstante = FILTER_SANITIZE_NUMBER_INT;
                break;
            case 'float':
                $filterKonstante = FILTER_SANITIZE_NUMBER_FLOAT;
                break;
            case 'url':
                $filterKonstante = FILTER_SANITIZE_URL;
                break;
            case 'email':
                $filterKonstante = FILTER_SANITIZE_EMAIL;
                break;
            case 'string':
                $filterKonstante = FILTER_SANITIZE_STRING;
                break;
            case 'boolean':
            case 'bool':
            case 'ip':
                $filterKonstante = $this->getValidationKonstante($filter);
                break;
            case 'encoded':
                $filterKonstante = FILTER_SANITIZE_ENCODED;
                break;
            case 'special_chars':
                $filterKonstante = FILTER_SANITIZE_SPECIAL_CHARS;
                break;
            case 'magic_quotes':
                $filterKonstante = FILTER_SANITIZE_MAGIC_QUOTES;
                break;
            default:
                $filterKonstante = FILTER_UNSAFE_RAW;
        }

        return $filterKonstante;
    }
}