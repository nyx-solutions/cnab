<?php

    namespace nyx\cnab\parser\input;

    use nyx\cnab\parser\IntercambioBancarioRetornoFileAbstract;
    use nyx\cnab\parser\models\Linha;
    use nyx\cnab\parser\models\Retorno;

    /**
     *
     */
    class RetornoFile extends IntercambioBancarioRetornoFileAbstract
    {
        /**
         * Para retorno o metodo em questao gera o modelo Retorno conforme layout
         *
         * @param string $path Não necessario
         *
         * @return Retorno
         */
        public function generate($path = null)
        {
            $this->decodeHeaderArquivo();
            $this->decodeTrailerArquivo();
            $this->decodeLotes();
            return $this->model;
        }

        /**
         * Processa header_arquivo
         */
        protected function decodeHeaderArquivo()
        {
            $layout           = $this->layout->getRetornoLayout();
            $headerArquivoDef = $layout['header_arquivo'];
            $linha            = new Linha($this->lines[0], $this->layout, 'retorno');
            foreach ($headerArquivoDef as $campo => $definicao) {
                $valor                                 = $linha->obterValorCampo($definicao);
                $this->model->header_arquivo->{$campo} = $valor;
            }
        }

        /**
         * Processa trailer_arquivo
         */
        protected function decodeTrailerArquivo()
        {
            $layout            = $this->layout->getRetornoLayout();
            $trailerArquivoDef = $layout['trailer_arquivo'];
            $linha             = new Linha($this->lines[count($this->lines) - 1], $this->layout, 'retorno');
            foreach ($trailerArquivoDef as $campo => $definicao) {
                $valor                                  = $linha->obterValorCampo($definicao);
                $this->model->trailer_arquivo->{$campo} = $valor;
            }
        }

        protected function decodeLotes()
        {
            $tipoLayout = $this->layout->getLayout();

            if (strtoupper($tipoLayout) === strtoupper('cnab240')) {
                $this->decodeLotesCnab240();
            } elseif (strtoupper($tipoLayout) === strtoupper('cnab400')) {
                $this->decodeLotesCnab400();
            }
        }

        private function decodeLotesCnab240()
        {
            $defTipoRegistro = [
                'pos'     => [8, 8],
                'picture' => '9(1)',
            ];

            $defCodigoLote = [
                'pos'     => [4, 7],
                'picture' => '9(4)',
            ];

            $defCodigoSegmento = [
                'pos'     => [14, 14],
                'picture' => 'X(1)',
            ];

            $defNumeroRegistro = [
                'pos'     => [9, 13],
                'picture' => '9(5)',
            ];

            $codigoLote                   = null;
            $primeiroCodigoSegmentoLayout = $this->layout->getPrimeiroCodigoSegmentoRetorno();
            $ultimoCodigoSegmentoLayout   = $this->layout->getUltimoCodigoSegmentoRetorno();

            $lote      = null;
            $titulos   = []; // titulos tem titulo
            $segmentos = [];
            foreach ($this->lines as $index => $linhaStr) {
                $linha        = new Linha($linhaStr, $this->layout, 'retorno');
                $tipoRegistro = (int)$linha->obterValorCampo($defTipoRegistro);

                if ($tipoRegistro === IntercambioBancarioRetornoFileAbstract::REGISTRO_HEADER_ARQUIVO) {
                    continue;
                }

                switch ($tipoRegistro) {
                    case IntercambioBancarioRetornoFileAbstract::REGISTRO_HEADER_LOTE:
                        $codigoLote = $linha->obterValorCampo($defCodigoLote);
                        $lote       = [
                            'codigo_lote'  => $codigoLote,
                            'header_lote'  => $this->model->decodeHeaderLote($linha),
                            'trailer_lote' => $this->model->decodeTrailerLote($linha),
                            'titulos'      => [],
                        ];
                        break;
                    case IntercambioBancarioRetornoFileAbstract::REGISTRO_DETALHES:
                        $codigoSegmento             = $linha->obterValorCampo($defCodigoSegmento);
                        $numeroRegistro             = $linha->obterValorCampo($defNumeroRegistro);
                        $dadosSegmento              = $linha->getDadosSegmento('segmento_' . strtolower($codigoSegmento));
                        $segmentos[$codigoSegmento] = $dadosSegmento;
                        $proximaLinha               = new Linha($this->lines[$index + 1], $this->layout, 'retorno');
                        $proximoCodigoSegmento      = $proximaLinha->obterValorCampo($defCodigoSegmento);
                        // se codigoSegmento é ultimo OU proximo codigoSegmento é o primeiro
                        // entao fecha o titulo e adiciona em $detalhes
                        if (strtolower($codigoSegmento) === strtolower($ultimoCodigoSegmentoLayout) ||
                            strtolower($proximoCodigoSegmento) === strtolower($primeiroCodigoSegmentoLayout)) {
                            $lote['titulos'][] = $segmentos;
                            // novo titulo, novos segmentos
                            $segmentos = [];
                        }
                        break;
                    case IntercambioBancarioRetornoFileAbstract::REGISTRO_TRAILER_ARQUIVO:
                        $this->model->lotes[] = $lote;
                        $titulos              = [];
                        $segmentos            = [];
                        break;
                }
            }
        }

        private function decodeLotesCnab400()
        {
            $defTipoRegistro = [
                'pos'     => [1, 1],
                'picture' => '9(1)',
            ];

            // para Cnab400 codigo do segmento na configuracao yaml é o codigo do registro
            $defCodigoSegmento = [
                'pos'     => [1, 1],
                'picture' => '9(1)',
            ];

            $defNumeroRegistro = [
                'pos'     => [395, 400],
                'picture' => '9(6)',
            ];

            $codigoLote                   = null;
            $primeiroCodigoSegmentoLayout = $this->layout->getPrimeiroCodigoSegmentoRetorno();
            $ultimoCodigoSegmentoLayout   = $this->layout->getUltimoCodigoSegmentoRetorno();

            $lote      = null;
            $segmentos = [];
            foreach ($this->lines as $index => $linhaStr) {
                $linha        = new Linha($linhaStr, $this->layout, 'retorno');
                $tipoRegistro = (int)$linha->obterValorCampo($defTipoRegistro);

                if ($tipoRegistro === IntercambioBancarioRetornoFileAbstract::REGISTRO_HEADER_ARQUIVO) {
                    continue;
                }

                if ($tipoRegistro === IntercambioBancarioRetornoFileAbstract::REGISTRO_TRAILER_ARQUIVO) {
                    $lote['titulos'][] = $segmentos;
                    $segmentos         = [];
                    break;
                }

                // estamos tratando detalhes
                $codigoSegmento             = $linha->obterValorCampo($defCodigoSegmento);
                $numeroRegistro             = $linha->obterValorCampo($defNumeroRegistro);
                $dadosSegmento              = $linha->getDadosSegmento('segmento_' . strtolower($codigoSegmento));
                $segmentos[$codigoSegmento] = $dadosSegmento;
                $proximaLinha               = new Linha($this->lines[$index + 1], $this->layout, 'retorno');
                $proximoCodigoSegmento      = $proximaLinha->obterValorCampo($defCodigoSegmento);
                // se (
                // 	proximo codigoSegmento é o primeiro OU
                // 	codigoSegmento é ultimo
                // )
                // entao fecha o titulo e adiciona em $detalhes
                if (
                    strtolower($proximoCodigoSegmento) === strtolower($primeiroCodigoSegmentoLayout) ||
                    strtolower($codigoSegmento) === strtolower($ultimoCodigoSegmentoLayout)
                ) {
                    $lote['titulos'][] = $segmentos;
                    // novo titulo, novos segmentos
                    $segmentos = [];
                }
            }

            $this->model->lotes[] = $lote;
        }
    }
