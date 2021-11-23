<?php

    namespace nyx\cnab\parser;

    use nyx\cnab\parser\exceptions\LayoutException;
    use Symfony\Component\Yaml\Yaml;

    /**
     * Layout
     */
    class Layout
    {
        /**
         * @var mixed|null
         */
        protected mixed $config = null;

        /**
         * @var string|null
         */
        protected ?string $arquivo = null;

        #region Initalization
        /**
         * @param string $filePath
         */
        public function __construct($filePath)
        {
            $this->arquivo = $filePath;
            $this->config  = Yaml::parse(file_get_contents($filePath));
        }
        #endregion

        /**
         * @return mixed
         * @throws LayoutException
         */
        public function getRemessaLayout()
        {
            if (!isset($this->config['remessa'])) {
                throw new LayoutException('Falta seção "remessa" no arquivo de layout "' . $this->arquivo . '".');
            }

            return $this->config['remessa'];
        }

        /**
         * @return mixed
         * @throws LayoutException
         */
        public function getRetornoLayout()
        {
            if (!isset($this->config['retorno'])) {
                throw new LayoutException('Falta seção "retorno" no arquivo de layout "' . $this->arquivo . '".');
            }

            return $this->config['retorno'];
        }

        /**
         * @return mixed|null
         */
        public function getVersao()
        {
            return $this->config['retorno'] ?? null;
        }

        /**
         * @return mixed|null
         */
        public function getServico()
        {
            return $this->config['servico'] ?? null;
        }

        /**
         * @return mixed|null
         */
        public function getLayout()
        {
            return $this->config['layout'] ?? null;
        }

        /**
         * @return string
         *
         * @throws LayoutException
         */
        public function getPrimeiroCodigoSegmentoRetorno(): string
        {
            $layout       = $this->getRetornoLayout();
            $segments     = array_keys($layout['detalhes']);
            $firstSegment = $segments[0];
            $parts        = explode('_', $firstSegment);

            return strtolower($parts[count($parts) - 1]);
        }

        /**
         * @return string
         * @throws LayoutException
         */
        public function getUltimoCodigoSegmentoRetorno(): string
        {
            $layout      = $this->getRetornoLayout();
            $segments    = array_keys($layout['detalhes']);
            $lastSegment = $segments[count($segments) - 1];
            $parts       = explode('_', $lastSegment);

            return strtolower($parts[count($parts) - 1]);
        }
    }
