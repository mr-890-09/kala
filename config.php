<?php
// Database configuration
define('DB_PATH', __DIR__ . '/weather_app.db');

// Initialize SQLite database
function initializeDatabase() {
    try {
        $db = new PDO('sqlite:' . DB_PATH);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create tables if they don't exist
        $db->exec("
            CREATE TABLE IF NOT EXISTS weather_cache (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                location TEXT NOT NULL,
                temperature REAL,
                description TEXT,
                wind_speed REAL,
                wind_direction TEXT,
                humidity INTEGER,
                pressure INTEGER,
                visibility REAL,
                cloud_cover INTEGER,
                raw_data TEXT,
                cached_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        $db->exec("
            CREATE TABLE IF NOT EXISTS user_preferences (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                session_id TEXT NOT NULL UNIQUE,
                favorite_locations TEXT,
                temperature_unit TEXT DEFAULT 'C',
                last_search TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        $db->exec("
            CREATE TABLE IF NOT EXISTS search_history (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                session_id TEXT NOT NULL,
                location TEXT NOT NULL,
                searched_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        return $db;
    } catch (Exception $e) {
        error_log("Database initialization error: " . $e->getMessage());
        return null;
    }
}

// Get database connection
function getDatabase() {
    try {
        $db = new PDO('sqlite:' . DB_PATH);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    } catch (Exception $e) {
        error_log("Database connection error: " . $e->getMessage());
        return null;
    }
}

// Initialize database on first load
if (!file_exists(DB_PATH)) {
    initializeDatabase();
}
?>
