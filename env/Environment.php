<?php

    namespace nyx\base\env;

    /**
     * Environment
     */
    class Environment implements EnvironmentInterface
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
        }
        #endregion

        #region Get, Save and Clear Environment
        /**
         * @param string $name
         * @param mixed  $default
         *
         * @return mixed
         */
        public static function get(string $name, $default = null)
        {
            $name = static::withPrefix($name);

            $value = '';

            if (isset(static::$vars[$name])) {
                $value = static::$vars[$name];
            }

            if (empty($value)) {
                return $default;
            }

            return $value;
        }

        /**
         * @param string $str
         *
         * @return string
         *
         * @noinspection PhpUnused
         */
        public static function parseString(string $str): string
        {
            $sep = preg_quote(static::$prefixSeparator);

            return preg_replace_callback(
                "/{([A-Z\d{$sep}]+)}/",
                fn ($matches)  => static::get($matches[1]),
                $str
            );
        }

        /**
         * @param array  $variables
         * @param string $prefix
         * @param array  $customVariables
         *
         * @return bool
         */
        protected static function saveEnv(array $variables, string $prefix, array $customVariables = []): bool
        {
            $vars = [];

            $hasCustomVariables = !empty($customVariables);

            foreach ($variables as $key) {
                $k = "{$prefix}{$key}";

                if ($hasCustomVariables) {
                    $vars[$k] = $customVariables[$k] ?? null;
                } else {
                    $vars[$k] = getenv($k);
                }
            }

            static::$vars = $vars;

            return true;
        }

        /**
         * @param string $prefix
         * @param array  $variables
         *
         * @return bool
         */
        protected static function clearEnv(string $prefix, array $variables = []): bool
        {
            $prefix    = preg_quote($prefix);

            if (empty($variables)) {
                $variables = getenv();
            }

            foreach ($variables as $key => $value) {
                if (preg_match("/^{$prefix}.*$/", $key)) {
                    putenv($key);

                    if (isset($_SERVER[$key])) {
                        unset($_SERVER[$key]);
                    }

                    if (isset($_ENV[$key])) {
                        unset($_ENV[$key]);
                    }

                    if (isset($GLOBALS, $GLOBALS['_SERVER'], $GLOBALS['_SERVER'][$key])) {
                        unset($GLOBALS['_SERVER'][$key]);
                    }

                    if (isset($GLOBALS, $GLOBALS['_ENV'], $GLOBALS['_ENV'][$key])) {
                        unset($GLOBALS['_ENV'][$key]);
                    }
                }
            }

            return true;
        }
        #endregion

        #region Prefix
        /**
         * @inheritdoc
         */
        public static function withPrefix(string $name): string
        {
            return $name;
        }
        #endregion
    }
