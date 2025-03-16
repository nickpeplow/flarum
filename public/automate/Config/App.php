<?php
/**
 * Application Configuration
 */

namespace Config;

class App {
    /**
     * Application settings
     */
    public static $settings = [
        // Basic application settings
        'name' => 'Keywords Automation Dashboard',
        'version' => '1.0.0',
        
        // Path settings
        'base_url' => '', // Will be set dynamically in bootstrap
        'asset_url' => '', // Will be set dynamically in bootstrap
        
        // View settings
        'default_layout' => 'layouts/main',
        
        // Database settings - will be loaded from config.php
        'database' => [
            'host' => '',
            'database' => '',
            'username' => '',
            'password' => '',
        ],
        
        // Pagination defaults
        'items_per_page' => 10,
    ];
    
    /**
     * Get a configuration value
     *
     * @param string $key Configuration key (dot notation supported)
     * @param mixed $default Default value if key not found
     * @return mixed
     */
    public static function get($key, $default = null) {
        $parts = explode('.', $key);
        $config = self::$settings;
        
        foreach ($parts as $part) {
            if (!isset($config[$part])) {
                return $default;
            }
            $config = $config[$part];
        }
        
        // If the value is empty and a default is provided, return the default
        if (($config === '' || $config === null) && $default !== null) {
            return $default;
        }
        
        return $config;
    }
    
    /**
     * Set a configuration value
     *
     * @param string $key Configuration key (dot notation supported)
     * @param mixed $value Value to set
     * @return void
     */
    public static function set($key, $value) {
        $parts = explode('.', $key);
        $lastKey = array_pop($parts);
        $config = &self::$settings;
        
        foreach ($parts as $part) {
            if (!isset($config[$part]) || !is_array($config[$part])) {
                $config[$part] = [];
            }
            $config = &$config[$part];
        }
        
        $config[$lastKey] = $value;
    }
} 