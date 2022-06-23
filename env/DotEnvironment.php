<?php

    namespace nyx\base\env;

    use Dotenv\Dotenv;
    use Exception;

    /**
     * DotEnv Environment
     */
    class DotEnvironment extends Environment
    {
        public const DEFAULT_BASE_PREFIX = 'APP_ENV';
        public const DEFAULT_APP_PREFIX  = 'NYX';
        public const DEFAULT_ENV_PREFIX  = 'PROD';

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
            parent::initialize($path, $variables, $booleans, $basePrefix, $appPrefix, $envPrefix, $clearEnv);

            $currentEnvPrefix = static::$envPrefix = static::generatePrefix($basePrefix, $appPrefix, $envPrefix);
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

        #region Prefix
        /**
         * @param string $basePrefix
         * @param string $appPrefix
         * @param string $envPrefix
        * @param bool    $env
         *
         * @return string
         */
        protected static function generatePrefix(string $basePrefix = '', string $appPrefix = '', string $envPrefix = '', bool $env = true): string
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
