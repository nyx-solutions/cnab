<?php

    namespace nyx\cnab\parser\output;

    use nyx\cnab\parser\exceptions\LayoutException;
    use nyx\cnab\parser\IntercambioBancarioRemessaFileAbstract;
    use nyx\cnab\parser\models\Lote;

    /**
     * Remessa File
     */
    class RemessaFile extends IntercambioBancarioRemessaFileAbstract
    {
        public const CNAB_EOL = "\r\n";

        /**
         * Write to file $path
         *
         * @param string $path
         *
         * @return bool
         */
        public function generate($path)
        {
            // header arquivo
            $headerArquivo = $this->encodeHeaderArquivo();

            // lotes
            $lotes = $this->encodeLotes();

            // trailer arquivo
            $trailerArquivo = $this->encodeTrailerArquivo();


            $data = [
                $headerArquivo,
                $lotes,
                $trailerArquivo,
            ];

            $data = implode(self::CNAB_EOL, $data);
            $data .= self::CNAB_EOL;

            return (bool)file_put_contents($path, $data);
        }

        /**
         * @return string|void
         * @throws LayoutException
         */
        protected function encodeHeaderArquivo()
        {
            if (!isset($this->model->header)) {
                return;
            }

            $layoutRemessa = $this->model->getLayout()->getRemessaLayout();
            return $this->encode($layoutRemessa['header_arquivo'], $this->model->header);
        }

        /**
         * @return string
         */
        protected function encodeLotes()
        {
            $encoded = [];

            foreach ($this->model->lotes as $lote) {
                // header lote
                if (!empty($lote->header)) {
                    $encoded[] = $this->encodeHeaderLote($lote);
                }

                // detalhes
                $encoded[] = $this->encodeDetalhes($lote);

                // trailer lote
                if (!empty($lote->trailer)) {
                    $encoded[] = $this->encodeTrailerLote($lote);
                }
            }

            return implode(self::CNAB_EOL, $encoded);
        }

        /**
         * @param Lote $model
         *
         * @return string|void
         */
        protected function encodeHeaderLote(Lote $model)
        {
            if (!isset($model->header) || empty($model->header)) {
                return;
            }

            $layout = $model->getLayout();
            return $this->encode($layout['header_lote'], $model->header);
        }

        protected function encodeDetalhes(Lote $model)
        {
            if (!isset($model->detalhes)) {
                return;
            }

            $layout = $model->getLayout();

            $encoded = [];

            foreach ($model->detalhes as $detalhe) {
                foreach ($detalhe as $segmento => $obj) {
                    $segmentoEncoded = $this->encode($layout['detalhes'][$segmento], $detalhe->$segmento);
                    $encoded[]       = $segmentoEncoded;
                }
            }

            return implode(self::CNAB_EOL, $encoded);
        }

        /**
         * @param Lote $model
         *
         * @return string|void
         */
        protected function encodeTrailerLote(Lote $model)
        {
            if (!isset($model->trailer) || empty($model->trailer)) {
                return;
            }

            $layout = $model->getLayout();
            return $this->encode($layout['trailer_lote'], $model->trailer);
        }

        /**
         * @return string|void
         * @throws LayoutException
         */
        protected function encodeTrailerArquivo()
        {
            if (!isset($this->model->trailer)) {
                return;
            }

            $layoutRemessa = $this->model->getLayout()->getRemessaLayout();
            return $this->encode($layoutRemessa['trailer_arquivo'], $this->model->trailer);
        }
    }
