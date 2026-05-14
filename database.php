<?php
/**
 * Database Helper Functions
 * Utility functions for SQLite database operations
 */

require_once 'config.php';

class WeatherDatabase {
    private $db;
    
    public function __construct() {
        $this->db = getDatabase();
        if (!$this->db) {
            throw new Exception("Failed to connect to database");
        }
    }
    
    /**
     * Cache weather data
     */
    public function cacheWeather($location, $weatherData) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO weather_cache (location, temperature, description, wind_speed, wind_direction, humidity, pressure, visibility, cloud_cover, raw_data)
                VALUES (:location, :temperature, :description, :wind_speed, :wind_direction, :humidity, :pressure, :visibility, :cloud_cover, :raw_data)
            ");
            
            return $stmt->execute([
                ':location' => $location,
                ':temperature' => $weatherData['temperature'] ?? null,
                ':description' => $weatherData['description'] ?? '',
                ':wind_speed' => $weatherData['windSpeed'] ?? null,
                ':wind_direction' => $weatherData['windDirection'] ?? '',
                ':humidity' => $weatherData['humidity'] ?? null,
                ':pressure' => $weatherData['pressure'] ?? null,
                ':visibility' => $weatherData['visibility'] ?? null,
                ':cloud_cover' => $weatherData['cloudCover'] ?? null,
                ':raw_data' => json_encode($weatherData)
            ]);
        } catch (Exception $e) {
            error_log("Cache weather error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get cached weather (within 30 minutes)
     */
    public function getCachedWeather($location) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM weather_cache 
                WHERE LOWER(location) = LOWER(:location)
                AND cached_at > datetime('now', '-30 minutes')
                ORDER BY cached_at DESC
                LIMIT 1
            ");
            
            $stmt->execute([':location' => $location]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get cached weather error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Add favorite location
     */
    public function addFavorite($sessionId, $location) {
        try {
            $prefs = $this->getUserPreferences($sessionId);
            $favorites = $prefs ? json_decode($prefs['favorite_locations'], true) ?? [] : [];
            
            if (!in_array($location, $favorites)) {
                $favorites[] = $location;
            }
            
            $stmt = $this->db->prepare("
                INSERT OR REPLACE INTO user_preferences (session_id, favorite_locations, updated_at)
                VALUES (:session_id, :favorites, CURRENT_TIMESTAMP)
            ");
            
            return $stmt->execute([
                ':session_id' => $sessionId,
                ':favorites' => json_encode($favorites)
            ]);
        } catch (Exception $e) {
            error_log("Add favorite error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user preferences
     */
    public function getUserPreferences($sessionId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM user_preferences WHERE session_id = :session_id
            ");
            
            $stmt->execute([':session_id' => $sessionId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get preferences error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get weather statistics
     */
    public function getWeatherStatistics() {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(DISTINCT location) as unique_locations,
                    COUNT(*) as total_queries,
                    AVG(temperature) as avg_temperature,
                    MIN(temperature) as min_temperature,
                    MAX(temperature) as max_temperature
                FROM weather_cache
            ");
            
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get statistics error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Clean old cache entries (older than 7 days)
     */
    public function cleanOldCache() {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM weather_cache 
                WHERE cached_at < datetime('now', '-7 days')
            ");
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Clean cache error: " . $e->getMessage());
            return false;
        }
    }
}

// Create database instance if needed
try {
    $weatherDb = new WeatherDatabase();
} catch (Exception $e) {
    error_log("Database initialization failed: " . $e->getMessage());
}
?>
