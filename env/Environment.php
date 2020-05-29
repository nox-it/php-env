<?php

    namespace nox\base\env;

    use Dotenv\Dotenv;
    use Exception;

    /**
     * Class Environment
     *
     * @noinspection PhpUnused
     */
    class Environment
    {
        const DEFAULT_BASE_PREFIX = 'APP_ENV';
        const DEFAULT_APP_PREFIX  = 'NOX';
        const DEFAULT_ENV_PREFIX  = 'PROD';

        /**
         * @var Dotenv|null
         */
        private static ?Dotenv $_instance = null;

        /**
         * @var string
         */
        public static string $envPrefix = '';

        /**
         * @var string
         */
        public static string $dotPrefix = '';

        /**
         * @var string
         */
        public static string $prefixSeparator = '_';

        /**
         * @var bool
         */
        protected static bool $useSystemEnv = false;

        /**
         * @var array
         */
        protected static array $vars = [];

        #region Instance
        /**
         * @return Dotenv
         *
         * @throws Exception
         */
        protected static function instance(): Dotenv
        {
            if (static::$_instance instanceof static) {
                return static::$_instance;
            }

            throw new Exception('DotEnv Instance not found.');
        }
        #endregion

        #region Initialization
        /**
         * @param string $path
         * @param array  $variables
         * @param array  $booleans
         * @param string $basePrefix
         * @param string $appPrefix
         * @param string $envPrefix
         * @param bool   $clearEnv
         *
         * @noinspection PhpUnused
         */
        public static function initialize(string $path, array $variables, array $booleans = [], string $basePrefix = '', string $appPrefix = '', string $envPrefix = '', bool $clearEnv = true): void
        {
            $currentEnvPrefix = static::$envPrefix = static::generatePrefix($basePrefix, $appPrefix, $envPrefix, true);
            $currentDotPrefix = static::$dotPrefix = static::generatePrefix($basePrefix, $appPrefix, $envPrefix, false);

            $inSystemEnv = true;

            foreach ($variables as $k) {
                $key = "{$currentEnvPrefix}{$k}";

                if (in_array($k, $booleans)) {
                    continue;
                }

                if (getenv($key) === false) {
                    $inSystemEnv = false;

                    break;
                }
            }

            if (!$inSystemEnv) {
                $dotenv = Dotenv::createImmutable($path);

                $dotenv->load();

                $dotenv->required(array_map(
                    fn ($item) => "{$currentDotPrefix}{$item}",
                    $variables
                ));

                static::$_instance = $dotenv;
            } else {
                static::$useSystemEnv = true;
            }

            static::saveEnv(
                $variables,
                (($inSystemEnv) ? $currentEnvPrefix : $currentDotPrefix)
            );

            if ($clearEnv) {
                static::clearEnv($basePrefix);
            }
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
                "/{([A-Z0-9{$sep}]+)}/",
                fn ($matches)  => static::get($matches[1]),
                $str
            );
        }

        /**
         * @param array  $variables
         * @param string $prefix
         *
         * @return bool
         */
        protected static function saveEnv(array $variables, string $prefix): bool
        {
            $vars = [];

            foreach ($variables as $key) {
                $k = "{$prefix}{$key}";

                $vars[$k] = getenv($k);
            }

            static::$vars = $vars;

            return true;
        }

        /**
         * @param string $prefix
         *
         * @return bool
         */
        protected static function clearEnv(string $prefix): bool
        {
            $prefix    = preg_quote($prefix);
            $variables = getenv();

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
         * @param string $basePrefix
         * @param string $appPrefix
         * @param string $envPrefix
        * @param bool   $env
         *
         * @return string
         */
        protected static function generatePrefix(string $basePrefix = '', string $appPrefix = '', string $envPrefix = '', $env = true): string
        {
            if (empty($basePrefix)) {
                $basePrefix = self::DEFAULT_BASE_PREFIX;
            }

            if (empty($appPrefix)) {
                $appPrefix = self::DEFAULT_APP_PREFIX;
            }

            if (empty($envPrefix)) {
                $envPrefix = self::DEFAULT_ENV_PREFIX;
            }

            $prefix = $basePrefix;
            $prefix .= ((!empty($prefix) && !static::hasEndingSeparator($prefix)) ? static::$prefixSeparator : '').$appPrefix;

            if ($env) {
                $prefix .= ((!empty($prefix) && !static::hasEndingSeparator($prefix)) ? static::$prefixSeparator : '').$envPrefix;
            }

            if (!static::hasEndingSeparator($prefix)) {
                $prefix .= static::$prefixSeparator;
            }

            return mb_convert_case($prefix, MB_CASE_UPPER, 'UTF-8');
        }

        /**
         * @param string $text
         *
         * @return bool
         */
        protected static function hasEndingSeparator(string $text): bool
        {
            if (!empty($text)) {
                $index = (strlen($text) - 1);

                if ($index > -1) {
                    return ($text[$index] === static::$prefixSeparator);
                }
            }

            return false;
        }

        /**
         * @param string $name
         *
         * @return string
         */
        public static function withPrefix(string $name): string
        {
            $p = ((static::$useSystemEnv) ? static::$envPrefix : static::$dotPrefix);

            if (!empty($p) && substr($name, 0, strlen($p)) !== $p) {
                $name = "{$p}{$name}";
            }

            return $name;
        }
        #endregion
    }
