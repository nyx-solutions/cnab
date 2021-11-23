<?php

    namespace nyx\cnab\parser\models;

    use JsonSerializable;

    /**
     * BaseSerializable
     */
    abstract class BaseSerializable implements JsonSerializable
    {
        /**
         * the data
         *
         * @var array
         */
        protected $data;

        /**
         * @return array|mixed
         */
        public function jsonSerialize()
        {
            return $this->data;
        }

        public function __set($name, $value)
        {
            $this->data[$name] = $value;
        }

        public function __get($name)
        {
            return isset($this->data[$name]) ? $this->data[$name] : null;
        }

        public function __isset($name)
        {
            return isset($this->data[$name]);
        }

        public function __unset($name)
        {
            unset($this->data[$name]);
        }
    }
