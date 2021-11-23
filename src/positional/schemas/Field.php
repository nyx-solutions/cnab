<?php

    namespace nyx\positional\schemas;

    /**
     * Field
     */
    class Field
    {
        /**
         * @var string
         */
        protected string $key = '';

        /**
         * @var int
         */
        protected int $length = 0;

        /**
         * @var string
         */
        protected string $padCharacter = ' ';

        /**
         * @var int
         */
        protected int $padPlacement = STR_PAD_RIGHT;

        /**
         * @var callable|null
         */
        protected mixed $callback = null;

        /**
         * @var array
         */
        protected array $validCharacters = [];

        /**
         * @var array
         */
        protected array $allowedPlacements = [STR_PAD_LEFT, STR_PAD_RIGHT, STR_PAD_BOTH];

        /**
         * @return string
         */
        public function getKey()
        {
            return $this->key;
        }

        /**
         * @param string $key
         *
         * @return $this
         */
        public function setKey($key)
        {
            $this->key = $key;
            return $this;
        }

        /**
         * @return int
         */
        public function getLength()
        {
            return $this->length;
        }

        /**
         * @param int $length
         *
         * @return $this
         */
        public function setLength($length)
        {
            $this->length = (int)$length;
            return $this;
        }

        /**
         * @return string
         */
        public function getPadCharacter()
        {
            return $this->padCharacter;
        }

        /**
         * @param string $padCharacter
         *
         * @return $this
         */
        public function setPadCharacter($padCharacter)
        {
            $this->padCharacter = (string)$padCharacter;
            return $this;
        }

        /**
         * @return int
         */
        public function getPadPlacement()
        {
            return $this->padPlacement;
        }

        /**
         * @param int $padPlacement
         *
         * @return static
         */
        public function setPadPlacement(int $padPlacement)
        {
            if (in_array($padPlacement, $this->allowedPlacements, true)) {
                $this->padPlacement = $padPlacement;
            }

            return $this;
        }

        /**
         * @return callable|null
         */
        public function getCallback()
        {
            return $this->callback;
        }

        /**
         * @param callable|null $callback
         *
         * @return $this
         */
        public function setCallback($callback)
        {
            $this->callback = $callback;
            return $this;
        }

        /**
         * @return array
         */
        public function getValidCharacters()
        {
            return $this->validCharacters;
        }

        /**
         * @param array $validCharacters
         *
         * @return $this
         */
        public function setValidCharacters(array $validCharacters)
        {
            $this->validCharacters = $validCharacters;

            return $this;
        }
    }
