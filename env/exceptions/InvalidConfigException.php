<?php

    namespace nyx\base\env\exceptions;

    use Exception;

    /**
     * Invalid Config Exception
     */
    class InvalidConfigException extends Exception
    {
        /**
         * @return string the user-friendly name of this exception
         */
        public function getName(): string
        {
            return 'Invalid Configuration';
        }
    }
