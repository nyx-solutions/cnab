<?php

    namespace nyx\cnab\parser\models;

    use nyx\cnab\parser\exceptions\LayoutException;
    use nyx\cnab\parser\Layout;
    use nyx\cnab\parser\formats\Picture;
    use nyx\cnab\parser\exceptions\LinhaException;

    /**
     * Line
     */
    class Linha
    {
        /**
         * @var Layout
         */
        protected $layout;

        /**
         * @var string
         */
        protected $linhaStr;

        /**
         * @var string
         */
        protected $tipo;

        /**
         * @param        $linhaStr
         * @param Layout $layout
         * @param string $tipo
         */
        public function __construct($linhaStr, Layout $layout, $tipo = 'remessa')
        {
            $this->linhaStr = $linhaStr;
            $this->layout   = $layout;
            $this->tipo     = strtolower($tipo);
        }

        /**
         * @param $segmentoKey
         *
         * @return array
         *
         * @throws LinhaException
         * @throws LayoutException
         */
        public function getDadosSegmento($segmentoKey)
        {
            $dados  = [];
            $layout = $this->tipo === 'remessa'
                ? $this->layout->getRemessaLayout()
                : $this->layout->getRetornoLayout();
            $campos = $layout['detalhes'][$segmentoKey];
            foreach ($campos as $nome => $definicao) {
                $dados[$nome] = $this->obterValorCampo($definicao);
            }
            return $dados;
        }

        /**
         * @param array $definicao
         *
         * @return array|float|string|string[]|void
         *
         * @throws LinhaException
         */
        public function obterValorCampo(array $definicao)
        {
            if (1 !== preg_match(Picture::REGEX_VALID_FORMAT, $definicao['picture'], $tipo)) {
                throw new LinhaException('Erro ao obter valor de campo. Definição de campo inválida (picture).');
            }

            $inicio   = $definicao['pos'][0] - 1;
            $tamanho1 = !empty($tipo['tamanho1']) ? (int)$tipo['tamanho1'] : 0;
            $tamanho2 = !empty($tipo['tamanho2']) ? (int)$tipo['tamanho2'] : 0;
            $tamanho  = $tamanho1 + $tamanho2;
            $formato  = $definicao['picture'];
            $opcoes   = [];

            return Picture::decode(substr($this->linhaStr, $inicio, $tamanho), $formato, $opcoes);
        }

        /**
         * @return Layout
         */
        public function getLayout()
        {
            return $this->layout;
        }

        /**
         * @return string
         */
        public function getTipo()
        {
            return $this->tipo;
        }

    }
