<?php

    namespace nyx\cnab\parser\models;

    use JsonSerializable;

    /**
     * Lote
     */
    class Lote implements JsonSerializable
    {
        public $sequencial;
        public $header;
        public $trailer;
        public $detalhes;

        protected $layout;

        /**
         * @param array $layout
         * @param int   $sequencial
         */
        public function __construct(array $layout, $sequencial = 1)
        {
            $this->layout = $layout;

            $this->sequencial = $sequencial;
            // inicia com header e trailer = null pois cnab400 pode não conter header e trailer de lotes (CEF SIGCB CNAB400 por exemplo)
            $this->header   = null;
            $this->trailer  = null;
            $this->detalhes = [];

            if (isset($this->layout['header_lote'])) {
                $this->header = new HeaderLote();
                foreach ($this->layout['header_lote'] as $field => $definition) {
                    $this->header->$field = (isset($definition['default'])) ? $definition['default'] : '';
                }
            }

            if (isset($this->layout['trailer_lote'])) {
                $this->trailer = new TrailerLote();
                foreach ($this->layout['trailer_lote'] as $field => $definition) {
                    $this->trailer->$field = (isset($definition['default'])) ? $definition['default'] : '';
                }
            }
        }

        /**
         * @return array
         */
        public function getLayout()
        {
            return $this->layout;
        }

        /**
         * @param array $excetoSegmentos
         *
         * @return object
         */
        public function novoDetalhe(array $excetoSegmentos = [])
        {
            $detalhe = (object)[];

            if (isset($this->layout['detalhes'])) {
                foreach ($this->layout['detalhes'] as $segmento => $segmentoDefinitions) {
                    // pula segmentos informados como "exceto" no parametro da função
                    if (in_array($segmento, $excetoSegmentos)) {
                        continue;
                    }
                    $detalhe->$segmento = new \stdClass;
                    foreach ($segmentoDefinitions as $field => $definition) {
                        $detalhe->$segmento->$field = (isset($definition['default'])) ? $definition['default'] : '';
                    }
                }
            }
            return $detalhe;
        }

        /**
         * @param \stdClass $detalhe
         *
         * @return $this
         */
        public function inserirDetalhe(\stdClass $detalhe)
        {
            $this->detalhes[] = $detalhe;
            return $this;
        }

        /**
         * @return int
         */
        public function countDetalhes()
        {
            return count($this->detalhes);
        }

        /**
         * @return $this
         */
        public function limpaDetalhes()
        {
            $this->detalhes = [];
            return $this;
        }

        /**
         * @inheritdoc
         */
        public function jsonSerialize()
        {
            $headerLote  = $this->header->jsonSerialize();
            $trailerLote = $this->trailer->jsonSerialize();
            $detalhes    = $this->detalhes;

            return ['codigo_lote'  => $this->sequencial, 'header_lote' => $headerLote, 'detalhes' => $detalhes, 'trailer_lote' => $trailerLote];
        }
    }
