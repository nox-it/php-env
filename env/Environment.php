<?php

    namespace nox\base\env;

    use Dotenv\Dotenv;
    use Exception;

    // NOX_ENV_APP_PROD_NAME

    /**
     * Class Environment
     *
     * @noinspection PhpUnused
     */
    class Environment
    {
        const DEFAULT_BASE_PREFIX = 'NOX_ENV';
        const DEFAULT_APP_PREFIX  = 'APP';
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
         * @param array  $required
         * @param array  $booleans
         * @param string $basePrefix
         * @param string $appPrefix
         * @param string $envPrefix
         *
         * @noinspection PhpUnused
         */
        public static function initialize(string $path, array $required = [], array $booleans = [], string $basePrefix = '', string $appPrefix = '', string $envPrefix = ''): void
        {
            $currentEnvPrefix = static::$envPrefix = static::generatePrefix($basePrefix, $appPrefix, $envPrefix, true);
            $currentDotPrefix = static::$dotPrefix = static::generatePrefix($basePrefix, $appPrefix, $envPrefix, false);

            $inSystemEnv = true;

            foreach ($required as $k) {
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
                    $required
                ));

                static::$_instance = $dotenv;
            }
        }
        #endregion

        #region GetEnv
        /**
         * @param string $name
         * @param mixed  $default
         *
         * @return mixed
         */
        public static function get(string $name, $default = null)
        {
            $name = static::withPrefix($name);

            $var = getenv($name);

            if (empty($var)) {
                return $default;
            }

            return $var;
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
                    return ($text[$index] !== static::$prefixSeparator);
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
            $p = static::$envPrefix;

            if (!empty($p) && substr($name, 0, strlen($p)) !== $p) {
                $name = "{$p}{$name}";
            }

            return $name;
        }
        #endregion
    }
