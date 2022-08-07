<?php

    namespace nyx\positional;

    /**
     * Reader
     */
    class PositionalReader
    {
        /**
         * @var PositionalSchema|null
         */
        protected ?PositionalSchema $schema = null;

        /**
         * @var string
         */
        protected string $string = '';

        #region Setters
        /**
         * @param PositionalSchema $schema
         *
         * @return static
         */
        public function setSchema(PositionalSchema $schema): static
        {
            $this->schema = $schema;

            return $this;
        }
        #endregion

        #region Reader
        /**
         * @param string $string
         *
         * @return array|null
         */
        public function readLine(string $string): ?array
        {
            if ($this->schema->getTotalLineLength() !== strlen($string)) {
                return null;
            }

            $data         = [];
            $lastPosition = 0;

            foreach ($this->schema->getFields() as $field) {
                $strPart = substr($string, $lastPosition, $field->getLength());

                if ($this->schema->getDelimiter() && str_contains($strPart, $this->schema->getDelimiter())) {
                    return null;
                }

                if ($field->getValidCharacters() && !in_array((string)$strPart, $field->getValidCharacters(), true)) {
                    return null;
                }

                $data[$field->getKey()] = $strPart;
                $lastPosition           += $field->getLength();

                if ($this->schema->getDelimiter()) {
                    $isDelimiter = substr($string, $lastPosition, strlen($this->schema->getDelimiter()));

                    if ($isDelimiter !== $this->schema->getDelimiter()) {
                        return null;
                    }

                    $lastPosition += strlen($this->schema->getDelimiter());
                }
            }
            return $data;
        }

        /**
         * @param string $string
         * @param int    $firstLine
         *
         * @return array
         */
        public function readLines(string $string, int $firstLine = 1): array
        {
            $lines = explode(PHP_EOL, $string);

            $data  = [];

            $currentLine = 1;

            foreach ($lines as $line) {
                if ($currentLine < $firstLine) {
                    $currentLine++;

                    continue;
                }

                if (!empty($line)) {
                    $data[] = $this->readLine($line);
                }

                $currentLine++;
            }

            return $data;
        }

        /**
         * @param string           $filePath
         * @param PositionalSchema $schema
         * @param int              $firstLine
         *
         * @return array
         */
        public static function readFile(string $filePath, PositionalSchema $schema, int $firstLine = 1): array
        {
            if (is_file($filePath)) {
                $contents = file_get_contents($filePath);

                if (!empty($contents)) {
                    return (new static())->setSchema($schema)->readLines($contents, $firstLine);
                }
            }

            return [];
        }

        /**
         * @param string           $contents
         * @param PositionalSchema $schema
         * @param int              $firstLine
         *
         * @return array
         */
        public static function readContents(string $contents, PositionalSchema $schema, int $firstLine = 1): array
        {
            if (!empty($contents)) {
                return (new static())->setSchema($schema)->readLines($contents, $firstLine);
            }

            return [];
        }
        #endregion
    }
