<?php

    namespace nyx\positional;

    use JsonException;
    use nyx\positional\schemas\Field;

    /**
     * Schema
     */
    class Schema
    {
        /**
         * @var string
         */
        protected string $delimiter = ' ';

        /**
         * @var array
         */
        protected array $fields = [];

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
         * @return string
         */
        public function getDelimiter()
        {
            return $this->delimiter;
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
            $field = new Field();

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

        /**
         * @param string $key
         *
         * @return Field|null
         */
        public function getField($key): ?Field
        {
            return $this->fields[$key] ?? null;
        }

        /**
         * @return Field[]
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

        /**
         * @param string $json
         *
         * @return Schema
         *
         * @throws JsonException
         */
        public static function createFromJson(string $json)
        {
            $array = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

            return static::createFromArray($array);
        }

        /**
         * @param $array
         *
         * @return static
         */
        public static function createFromArray($array)
        {
            $schema = new static();

            $schemaData = [];

            foreach ($array as $item) {
                foreach ($item as $key => $value) {
                    $existingValueLength = isset($schemaData[$key]) ? $schemaData[$key]['length'] : 0;

                    $properLength = strlen((string)$value);

                    if ($existingValueLength > $properLength) {
                        $properLength = $existingValueLength;
                    }

                    $schemaData[$key] = [
                        'key'          => $key,
                        'length'       => $properLength,
                        'padCharacter' => ' ',
                        'padPlacement' => STR_PAD_RIGHT,
                        'callback'     => null,
                    ];
                }
            }

            $schema->setFields($schemaData);

            return $schema;
        }
    }
