<?php

    namespace nyx\positional;

    /**
     * Reader
     */
    class Reader
    {
        /**
         * @var Schema|null
         */
        protected ?Schema $schema = null;

        /**
         * @var string
         */
        protected string $string = '';

        #region Setters
        /**
         * @param Schema $schema
         *
         * @return static
         */
        public function setSchema(Schema $schema): static
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
         *
         * @return array
         */
        public function readLines($string)
        {
            $lines = explode(PHP_EOL, $string);
            $data  = [];
            foreach ($lines as $line) {
                if (!empty($line)) {
                    $data[] = $this->readLine($line);
                }
            }
            return $data;
        }
        #endregion
    }
