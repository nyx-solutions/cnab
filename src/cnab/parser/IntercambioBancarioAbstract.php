<?php

    namespace nyx\cnab\parser;

    use nyx\cnab\parser\exceptions\LayoutException;
    use nyx\cnab\parser\models\HeaderArquivo;
    use nyx\cnab\parser\models\Lote;
    use nyx\cnab\parser\models\TrailerArquivo;
    use JsonSerializable;

    /**
     *
     */
    abstract class IntercambioBancarioAbstract implements JsonSerializable
    {
        /**
         * Header Arquivo
         *
         * @var HeaderArquivo|null
         */
        public ?HeaderArquivo $header = null;

        /**
         * Trailer Arquivo
         *
         * @var TrailerArquivo|null
         */
        public TrailerArquivo|null $trailer = null;

        /**
         * @var Lote[]
         */
        public array $lotes = [];

        /**
         * @var Layout|null
         */
        protected ?Layout $layout = null;

        #region Initialization
        /**
         * @param Layout $layout
         */
        public function __construct(Layout $layout)
        {
            $this->layout  = $layout;
            $this->header  = new HeaderArquivo();
            $this->trailer = new TrailerArquivo();
            $this->lotes   = [];
        }
        #endregion

        /**
         * @return Layout
         *
         * @throws LayoutException
         */
        public function getLayout()
        {
            if ($this->layout === null) {
                throw new LayoutException('Layout not found.');
            }
            return $this->layout;
        }

        /**
         * @param Lote $lote
         *
         * @return static
         */
        public function inserirLote(Lote $lote)
        {
            $this->lotes[] = $lote;

            return $this;
        }

        /**
         * @param $sequencial
         *
         * @return static
         */
        public function removerLote($sequencial)
        {
            $found = -1;

            foreach ($this->lotes as $indice => $lote) {
                if ($lote->sequencial === $sequencial) {

                    $found = $indice;

                    break;
                }
            }

            if ($found > -1) {
                unset($this->lotes[$found]);
            }

            return $this;
        }

        /**
         * @return static
         */
        public function limparLotes(): static
        {
            $this->lotes = [];

            return $this;
        }

        /**
         * @inheritdoc
         */
        public function jsonSerialize()
        {
            $headerArquivo  = $this->header->jsonSerialize();
            $trailerArquivo = $this->trailer->jsonSerialize();
            $lotes          = $this->lotes;

            return ['header_arquivo' => $headerArquivo, 'lotes' => $lotes, 'trailer_arquivo' => $trailerArquivo];
        }
    }
