<?php

    namespace nyx\cnab\parser;

    use nyx\cnab\parser\exceptions\LinhaException;
    use nyx\cnab\parser\exceptions\RetornoException;
    use nyx\cnab\parser\models\Linha;
    use nyx\cnab\parser\models\Retorno;

    /**
     *
     */
    abstract class IntercambioBancarioRetornoFileAbstract extends IntercambioBancarioFileAbstract
    {
        #region Constants
        #region CNAB240
        public const REGISTRO_HEADER_ARQUIVO  = 0;
        public const REGISTRO_HEADER_LOTE     = 1;
        public const REGISTRO_DETALHES        = 3;
        public const REGISTRO_TRAILER_LOTE    = 5;
        public const REGISTRO_TRAILER_ARQUIVO = 9;
        #endregion

        #region CNAB400
        #endregion
        #endregion

        /**
         * @var Layout|null
         */
        protected ?Layout $layout = null;

        /**
         * @var string|null
         */
        protected ?string $path = null;

        /**
         * @var array|false
         */
        protected array|false $lines = false;

        /**
         * Total de lotes em um arquivo
         *
         * @var int
         */
        protected int $totalLotes = 0;

        #region Initialization
        /**
         * @param Layout $layout Layout do retorno
         * @param string $path   Caminho do arquivo de retorno a ser processado
         *
         * @throws RetornoException
         * @throws LinhaException
         */
        public function __construct(Layout $layout, string $path)
        {
            $this->layout = $layout;
            $this->path   = $path;
            $this->lines = file($this->path, FILE_IGNORE_NEW_LINES);

            if ($this->lines === false) {
                throw new RetornoException('Falha ao ler linhas do arquivo de retorno "' . $this->path . '".');
            }

            $this->calculaTotalLotes();

            $this->model = new Retorno();
        }
        #endregion

        /**
         * @return int
         */
        public function getTotalLotes()
        {
            return $this->totalLotes;
        }

        /**
         * @return int
         *
         * @throws LinhaException
         */
        protected function calculaTotalLotes()
        {
            $this->totalLotes = 1;

            $layout = $this->layout->getLayout();

            $linhaTrailerArquivoStr = $this->lines[count($this->lines) - 1];

            $linha = new Linha($linhaTrailerArquivoStr, $this->layout, 'retorno');

            if (strtoupper($layout) === strtoupper('cnab240')) {
                // conforme cnab240 febraban
                $definicao        = ['pos' => [18, 23], 'picture' => '9(6)',];
                $this->totalLotes = (int)$linha->obterValorCampo($definicao);
            } elseif (strtoupper($layout) === strtoupper('cnab400')) {
                $this->totalLotes = 1; // cnab400 apenas 1 lote
            }

            return $this->totalLotes;
        }
    }
