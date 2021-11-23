<?php

    namespace nyx\cnab\parser\models;

    use StdClass as DataContainer;

    /**
     * Retorno
     */
    class Retorno
    {
        /**
         * @var DataContainer
         */
        public $header_arquivo;

        /**
         * @var DataContainer
         */
        public $trailer_arquivo;

        /**
         * @var array of DataContainer (header_lote(1),detalhes(1)(n),trailer_lote(1) ... header_lote(m),detalhes(1)(n),trailer_lote(m))
         */
        public $lotes;

        public function __construct()
        {
            $this->header_arquivo  = new DataContainer();
            $this->trailer_arquivo = new DataContainer();
            $this->lotes           = [];
        }

        /**
         * @param \nyx\cnab\parser\models\Linha $linha
         *
         * @return array
         * @throws \nyx\cnab\parser\exceptions\LayoutException
         * @throws \nyx\cnab\parser\exceptions\LinhaException
         */
        public function decodeHeaderLote(Linha $linha)
        {
            $dados = [];

            $layout = $linha->getTipo() === 'remessa'
                ? $linha->getLayout()->getRemessaLayout()
                : $linha->getLayout()->getRetornoLayout();

            foreach ($layout['header_lote'] as $nome => $definicao) {
                $dados[$nome] = $linha->obterValorCampo($definicao);
            }

            return $dados;
        }

        /**
         * @param \nyx\cnab\parser\models\Linha $linha
         *
         * @return array
         * @throws \nyx\cnab\parser\exceptions\LayoutException
         * @throws \nyx\cnab\parser\exceptions\LinhaException
         */
        public function decodeTrailerLote(Linha $linha)
        {
            $dados = [];

            $layout = $linha->getTipo() === 'remessa'
                ? $linha->getLayout()->getRemessaLayout()
                : $linha->getLayout()->getRetornoLayout();

            foreach ($layout['trailer_lote'] as $nome => $definicao) {
                $dados[$nome] = $linha->obterValorCampo($definicao);
            }

            return $dados;
        }

        /**
         * @return int
         */
        public function getTotalLotes()
        {
            return count($this->lotes);
        }

        /**
         * @return int
         */
        public function getTotalTitulos()
        {
            $total = 0;

            foreach ($this->lotes as $lote) {
                $total += count($lote['titulos']);
            }

            return $total;
        }
    }
