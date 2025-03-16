<?php
/**
 * DotEnv Utility
 * 
 * A utility class for loading environment variables from .env files
 */

namespace Utils;

class DotEnv {
    protected $path;
    protected $variables = [];

    /**
     * Constructor - loads environment variables from .env file
     * 
     * @param string $path Path to the .env file
     */
    public function __construct($path = null) {
        $this->path = $path ?: __DIR__ . '/../.env';
        $this->load();
    }

    /**
     * Load environment variables from .env file
     * 
     * @return void
     */
    public function load() {
        if (!file_exists($this->path)) {
            throw new \RuntimeException(sprintf('Environment file %s does not exist', $this->path));
        }

        if (!is_readable($this->path)) {
            throw new \RuntimeException(sprintf('Environment file %s is not readable', $this->path));
        }

        $lines = file($this->path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parse variable
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);

                // Remove quotes if present
                if (strpos($value, '"') === 0 && substr($value, -1) === '"') {
                    $value = substr($value, 1, -1);
                } elseif (strpos($value, "'") === 0 && substr($value, -1) === "'") {
                    $value = substr($value, 1, -1);
                }

                $this->variables[$name] = $value;
            }
        }
    }

    /**
     * Get an environment variable
     * 
     * @param string $key Variable name
     * @param mixed $default Default value if variable not found
     * @return mixed
     */
    public function get($key, $default = null) {
        return $this->variables[$key] ?? $default;
    }

    /**
     * Check if an environment variable exists
     * 
     * @param string $key Variable name
     * @return bool
     */
    public function has($key) {
        return array_key_exists($key, $this->variables);
    }

    /**
     * Get all environment variables
     * 
     * @return array
     */
    public function all() {
        return $this->variables;
    }

    /**
     * Set an environment variable (for the current request only)
     * 
     * @param string $key Variable name
     * @param mixed $value Variable value
     * @return void
     */
    public function set($key, $value) {
        $this->variables[$key] = $value;
    }
} 