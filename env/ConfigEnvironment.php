<?php

    namespace nyx\base\env;

    /**
     * Config Environment
     */
    class ConfigEnvironment extends Environment
    {
        /**
         * @var array
         */
        protected static array $vars = [];

        #region Initialization
        /**
         * @inheritdoc
         */
        public static function initialize(string $path, array $variables, array $booleans = [], string $basePrefix = '', string $appPrefix = '', string $envPrefix = '', bool $clearEnv = true): void
        {
            parent::initialize($path, $variables, $booleans, $basePrefix, $appPrefix, $envPrefix, $clearEnv);

            if (!is_file($path)) {
                throw new InvalidConfigException('Configuration file not found.');
            }

            $vars = require($path);

            if (!is_array($vars)) {
                $vars = [];
            }

            $realVariables = [];

            foreach ($variables as $key) {
                if (array_key_exists($key, $vars)) {
                    $realVariables[$key] = $vars[$key];
                } else {
                    throw new InvalidConfigException(sprintf('The variable %s was not found', $key));
                }
            }

            static::saveEnv($variables, '', $realVariables);

            if ($clearEnv) {
                static::clearEnv('', $realVariables);
            }
        }
        #endregion
    }
