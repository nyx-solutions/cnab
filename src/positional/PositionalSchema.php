<?php

    namespace nyx\positional;

    use JsonException;
    use nyx\positional\schemas\PositionalField;
    use Symfony\Component\Yaml\Yaml;

    /**
     * Schema
     */
    class PositionalSchema
    {
        /**
         * @var string
         */
        protected string $delimiter = ' ';

        /**
         * @var array
         */
        protected array $fields = [];

        #region Getters
        /**
         * @return string
         */
        public function getDelimiter()
        {
            return $this->delimiter;
        }

        /**
         * @param string $key
         *
         * @return PositionalField|null
         */
        public function getField($key): ?PositionalField
        {
            return $this->fields[$key] ?? null;
        }

        /**
         * @return PositionalField[]
         */
        public function getFields()
        {
            return $this->fields;
        }

        /**
         * @return int
         */
        public function getTotalLineLength(): int
        {
            $length = strlen($this->getDelimiter()) * count($this->getFields());

            foreach ($this->getFields() as $field) {
                $length += $field->getLength();
            }

            return $length;
        }
        #endregion

        #region Setters
        /**
         * @param string $delimiter
         *
         * @return $this
         */
        public function setDelimiter($delimiter)
        {
            $this->delimiter = $delimiter;
            return $this;
        }

        /**
         * @param string        $key
         * @param int           $length
         * @param string        $padCharacter
         * @param int           $padPlacement
         * @param null|callable $callback
         * @param array         $validCharacters
         *
         * @return static
         */
        public function setField(
            $key,
            $length,
            $padCharacter = ' ',
            $padPlacement = STR_PAD_RIGHT,
            $callback = null,
            $validCharacters = []
        )
        {
            $field = new PositionalField();

            $field->setKey($key);
            $field->setLength($length);
            $field->setPadCharacter($padCharacter);
            $field->setPadPlacement($padPlacement);
            $field->setCallback($callback);
            $field->setValidCharacters($validCharacters);

            $this->fields[$key] = $field;

            return $this;
        }

        /**
         * @param array $fields
         *
         * @return static
         */
        public function setFields(array $fields)
        {
            foreach ($fields as $field) {
                if (isset($field['type']) && $field['type'] === 'numeric') {
                    $field['padCharacter'] = '0';
                    $field['padPlacement'] = STR_PAD_LEFT;
                }

                $this->setField(
                    $field['key'],
                    $field['length'],
                    $field['padCharacter'] ?? ' ',
                    $field['padPlacement'] ?? STR_PAD_RIGHT,
                    $field['callback'] ?? null,
                    $field['validCharacters'] ?? []
                );
            }

            return $this;
        }
        #endregion

        #region Creation
        #region JSON
        /**
         * @param string $filePath
         *
         * @return PositionalSchema
         *
         * @throws JsonException
         */
        public static function createFromJsonFile(string $filePath): PositionalSchema
        {
            if (is_file($filePath)) {
                $json = file_get_contents($filePath);

                if (!empty($json)) {
                    return static::createFromJson($json);
                }
            }

            return new static();
        }

        /**
         * @param string $json
         *
         * @return PositionalSchema
         *
         * @throws JsonException
         */
        public static function createFromJson(string $json)
        {
            $array = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

            return static::createFromArray($array);
        }
        #endregion

        #region YAML
        /**
         * @param string $filePath
         *
         * @return PositionalSchema
         */
        public static function createFromYamlFile(string $filePath): PositionalSchema
        {
            if (is_file($filePath)) {
                $yaml = file_get_contents($filePath);

                if (!empty($yaml)) {
                    return static::createFromYaml($yaml);
                }
            }

            return new static();
        }

        /**
         * @param string $yaml
         *
         * @return PositionalSchema
         */
        public static function createFromYaml(string $yaml): PositionalSchema
        {
            $fields = Yaml::parse($yaml);

            if (!is_array($fields)) {
                $fields = [];
            }

            return static::createFromArray($fields);
        }
        #endregion

        #region Array
        /**
         * @param array $fields
         *
         * @return static
         */
        public static function createFromArray(array $fields)
        {
            $schema = new static();

            $schemaFields = [];

            foreach ($fields as $field) {
                $key             = $field['key'] ?? null;
                $length          = $field['length'] ?? null;
                $type            = $field['type'] ?? '';
                $padCharacter    = $field['padCharacter'] ?? ' ';
                $padPlacement    = $field['padPlacement'] ?? STR_PAD_RIGHT;
                $validCharacters = $field['validCharacters'] ?? [];

                if (!empty($key)) {
                    $length       = (int)$length;
                    $padCharacter = (string)$padCharacter;

                    if (is_string($padPlacement)) {
                        if (strtoupper($padPlacement) === 'STR_PAD_LEFT') {
                            $padPlacement = STR_PAD_LEFT;
                        } elseif (strtoupper($padPlacement) === 'STR_PAD_BOTH') {
                            $padPlacement = STR_PAD_BOTH;
                        } else {
                            $padPlacement = STR_PAD_RIGHT;
                        }
                    }

                    $padPlacement = (int)$padPlacement;

                    if (!in_array($padPlacement, [STR_PAD_LEFT, STR_PAD_RIGHT, STR_PAD_BOTH], true)) {
                        $padPlacement = STR_PAD_RIGHT;
                    }

                    if (!is_array($validCharacters)) {
                        $validCharacters = [];
                    }

                    $schemaFields[$key] = [
                        'key'             => $key,
                        'length'          => $length,
                        'type'            => $type,
                        'padCharacter'    => $padCharacter,
                        'padPlacement'    => $padPlacement,
                        'validCharacters' => $validCharacters,
                        'callback'        => null,
                    ];
                }
            }

            $schema->setFields($schemaFields);

            return $schema;
        }
        #endregion
        #endregion
    }
