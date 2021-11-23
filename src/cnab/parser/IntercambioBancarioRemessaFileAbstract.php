<?php

    namespace nyx\cnab\parser;

    use nyx\cnab\parser\formats\Picture;
    use Exception;

    /**
     *
     */
    abstract class IntercambioBancarioRemessaFileAbstract extends IntercambioBancarioFileAbstract
    {
        /**
         * @param IntercambioBancarioAbstract $model
         */
        public function __construct(IntercambioBancarioAbstract $model)
        {
            $this->model = $model;
        }

        /**
         * @param $fieldsDef
         * @param $modelSection
         *
         * @return string
         *
         * @throws Exception
         */
        protected function encode($fieldsDef, $modelSection)
        {
            $encoded = '';

            foreach ($fieldsDef as $field => $definition) {
                if (isset($modelSection->$field)) {
                    $format  = $definition['picture'];
                    $encoded .= Picture::encode($modelSection->$field, $format, ['field_desc' => $field]);
                }
            }

            return $encoded;
        }
    }
