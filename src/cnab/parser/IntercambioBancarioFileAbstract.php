<?php

    namespace nyx\cnab\parser;

    /**
     * Intercâmbio Bancário / Arquivo
     */
    abstract class IntercambioBancarioFileAbstract
    {
        /**
         * Model to write
         *
         * @var IntercambioBancarioAbstract
         */
        protected $model;

        /**
         * Write to file $path
         *
         * @param string $path
         *
         * @return bool
         */
        abstract public function generate($path);
    }
