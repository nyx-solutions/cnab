<?php

    namespace nyx\cnab\parser\formats;

    use DateTime;
    use Exception;
    use InvalidArgumentException;
    use function preg_match;
    use function str_pad;
    use function substr;

    /**
     * Picture
     */
    class Picture
    {
        public const REGEX_VALID_FORMAT = '/(?P<tipo1>X|9)\((?P<tamanho1>[0-9]+)\)(?P<tipo2>(V9)?)\(?(?P<tamanho2>([0-9]+)?)\)?/';

        /**
         * @param string $format
         *
         * @return bool
         *
         * @noinspection NotOptimalRegularExpressionsInspection
         */
        public static function validarFormato(string $format): bool
        {
            if (preg_match(self::REGEX_VALID_FORMAT, $format)) {
                return true;
            }

            return false;
        }

        /**
         * @param string $format
         *
         * @return int
         *
         * @noinspection NotOptimalRegularExpressionsInspection
         */
        public static function getLength(string $format): int
        {
            $m = [];

            if (preg_match(self::REGEX_VALID_FORMAT, $format, $m)) {
                return ((int)$m['tamanho1'] + (int)$m['tamanho2']);
            }

            throw new InvalidArgumentException("'$format' is not a valid format");
        }

        /**
         * @param string $value
         *
         * @return array|string
         */
        public static function parseNumber(string $value): array|string
        {
            $value = preg_replace(['/[^0-9.]/', '/^0+/'], '', $value);

            if ($value) {
                return $value;
            }

            return '0';
        }

        /**
         * @param $value
         * @param $format
         * @param $options
         *
         * @return string|void
         *
         * @throws Exception
         *
         * @noinspection NotOptimalRegularExpressionsInspection
         * @noinspection PowerOperatorCanBeUsedInspection
         */
        public static function encode($value, $format, $options)
        {
            $m = [];
            
            if (preg_match(self::REGEX_VALID_FORMAT, $format, $m)) {
                if ((string)$m['tipo1'] === 'X' && !$m['tipo2']) {
                    $value = substr($value, 0, $m['tamanho1']);

                    return str_pad($value, (int)$m['tamanho1'], ' ', STR_PAD_RIGHT);
                }

                if ((string)$m['tipo1'] === '9') {
                    if ($value instanceof DateTime) {
                        if ($options['date_format']) {
                            $value = strftime($options['date_format'], $value->getTimestamp());
                        } else {
                            if ((int)$m['tamanho1'] === 8) {
                                $value = $value->format('dmY');
                            }

                            if ((int)$m['tamanho1'] === 6) {
                                $value = $value->format('dmy');
                            }
                        }
                    }

                    if (!is_numeric($value)) {
                        $msg = "%svalor '$value' não é número, formato requerido $format.";

                        if (!empty($options['register_desc'])) {
                            $msg = sprintf($msg, "{$options['register_desc']} > %s");
                        }

                        if (!empty($options['field_desc'])) {
                            $msg = sprintf($msg, "{$options['field_desc']}: ");
                        }
                        throw new Exception($msg);
                    }

                    $value = self::parseNumber($value);
                    $exp   = explode('.', $value);

                    if (!isset($exp[1])) {
                        $exp[1] = 0;
                    }

                    if ($m['tipo2'] === 'V9') {
                        $tamanho_left  = (int)$m['tamanho1'];
                        $tamanho_right = (int)$m['tamanho2'];
                        $valor_left    = str_pad($exp[0], $tamanho_left, '0', STR_PAD_LEFT);
                        if (strlen($exp[1]) > $tamanho_right) {
                            $extra    = strlen($exp[1]) - $tamanho_right;
                            $extraPow = pow(10, $extra);
                            $exp[1]   = round($exp[1] / $extraPow);
                        }
                        $valor_right = str_pad($exp[1], $tamanho_right, '0', STR_PAD_RIGHT);

                        return $valor_left . $valor_right;
                    }

                    if ($m['tipo2']) {
                        $msg = "%s$format' is not a valid format";

                        if (!empty($options['register_desc'])) {
                            $msg = sprintf($msg, "{$options['register_desc']} > %s");
                        }

                        if (!empty($options['field_desc'])) {
                            $msg = sprintf($msg, "{$options['field_desc']}: ");
                        }
                        throw new InvalidArgumentException($msg);
                    } else {
                        $value = self::parseNumber($value);

                        return str_pad($value, (int)$m['tamanho1'], '0', STR_PAD_LEFT);
                    }
                }
            } else {
                throw new InvalidArgumentException("'$format' is not a valid format");
            }
        }

        /**
         * @param $value
         * @param $format
         * @param $options
         *
         * @return array|float|string|void
         *
         * @noinspection NotOptimalRegularExpressionsInspection
         */
        public static function decode($value, $format, $options)
        {
            $m = [];

            if (preg_match(self::REGEX_VALID_FORMAT, $format, $m)) {
                if ((string)$m['tipo1'] === 'X' && !$m['tipo2']) {
                    return rtrim($value);
                }

                if ((string)$m['tipo1'] === '9') {
                    if ((string)$m['tipo2'] === 'V9') {
                        $tamanho_left  = (int)$m['tamanho1'];
                        $tamanho_right = (int)$m['tamanho2'];
                        $valor_left    = self::parseNumber(substr($value, 0, $tamanho_left));
                        $valor_right   = '0.' . substr($value, $tamanho_left, $tamanho_right);
                        if ((double)$valor_right > 0) {
                            return $valor_left + (double)$valor_right;
                        }

                        return self::parseNumber($valor_left);
                    }

                    if ($m['tipo2']) {
                        $msg = "%s$format' is not a valid format";

                        if (!empty($options['field_desc'])) {
                            $msg = sprintf($msg, "{$options['field_desc']}: ");
                        }
                        throw new InvalidArgumentException($msg);
                    } else {
                        return self::parseNumber($value);
                    }
                }
            } else {
                $msg = "%s$format' is not a valid format";

                if (!empty($options['field_desc'])) {
                    $msg = sprintf($msg, "{$options['field_desc']}: ");
                }

                throw new InvalidArgumentException($msg);
            }
        }
    }
