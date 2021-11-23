<?php

    namespace nyx\cnab\parser\models;

    use nyx\cnab\parser\exceptions\LayoutException;
    use nyx\cnab\parser\IntercambioBancarioAbstract;
    use nyx\cnab\parser\Layout;

    /**
     * Remessa
     */
    class Remessa extends IntercambioBancarioAbstract
    {
        public function __construct(Layout $layout)
        {
            parent::__construct($layout);

            $remessaLayout = $this->layout->getRemessaLayout();

            if (isset($remessaLayout['header_arquivo'])) {
                foreach ($remessaLayout['header_arquivo'] as $field => $definition) {
                    $this->header->$field = (isset($definition['default'])) ? $definition['default'] : '';
                }
            }

            if (isset($remessaLayout['trailer_arquivo'])) {
                foreach ($remessaLayout['trailer_arquivo'] as $field => $definition) {
                    $this->trailer->$field = (isset($definition['default'])) ? $definition['default'] : '';
                }
            }
        }

        /**
         * @param int $sequencial
         *
         * @return Lote
         *
         * @throws LayoutException
         */
        public function novoLote($sequencial = 1)
        {
            return new Lote($this->layout->getRemessaLayout(), $sequencial);
        }
    }
