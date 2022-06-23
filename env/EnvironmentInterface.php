<?php

    namespace nyx\base\env;

    /**
     * Interface: Environment
     */
    interface EnvironmentInterface
    {
        #region Initialization
        /**
         * @param string $path
         * @param array  $variables
         * @param array  $booleans
         * @param string $basePrefix
         * @param string $appPrefix
         * @param string $envPrefix
         * @param bool   $clearEnv
         */
        public static function initialize(string $path, array $variables, array $booleans = [], string $basePrefix = '', string $appPrefix = '', string $envPrefix = '', bool $clearEnv = true): void;
        #endregion

        #region Get, Save and Clear Environment
        /**
         * @param string $name
         * @param mixed  $default
         *
         * @return mixed
         */
        public static function get(string $name, $default = null);

        /**
         * @param string $str
         *
         * @return string
         *
         * @noinspection PhpUnused
         */
        public static function parseString(string $str): string;
        #endregion
    }
