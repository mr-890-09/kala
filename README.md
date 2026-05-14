# FisherWeather - PHP & SQLite Setup

This folder now utilizes **PHP** and **SQLite** for advanced weather tracking and user data management.

## Files Overview

### Core Files
- **index.php** - Main application file with PHP session management and SQLite integration
- **config.php** - Database configuration and initialization
- **api.php** - RESTful API endpoints for database operations
- **database.php** - Database helper class for easy data access
- **style.css** - UI styling (unchanged)

## Features

### PHP Features
- ✅ Session management (`$_SESSION`)
- ✅ Database connection pooling
- ✅ Error handling and logging
- ✅ JSON API responses
- ✅ CORS-ready endpoints

### SQLite Database

#### Tables Created

**1. weather_cache**
- Stores weather data for locations
- Auto-caches API responses for 30 minutes
- Fields: location, temperature, description, wind_speed, wind_direction, humidity, pressure, visibility, cloud_cover, raw_data, cached_at

**2. user_preferences**
- Stores user-specific settings per session
- Fields: session_id, favorite_locations, temperature_unit, last_search, created_at, updated_at

**3. search_history**
- Tracks search history per session
- Fields: session_id, location, searched_at

## API Endpoints

All endpoints are accessed via `api.php?action=ACTION`

### Weather Operations
- `save_weather` - Cache weather data to SQLite
- `get_weather` - Retrieve cached weather
- `get_history` - Get search history

### User Preferences
- `save_preference` - Save user settings
- `get_preferences` - Get user settings
- `get_favorites` - Get favorite locations

## Database Location
- **File**: `weather_app.db` (created in the application root)
- **Type**: SQLite3
- **No setup required** - Database initializes automatically on first load

## Usage Examples

### Saving Weather Data
```php
require_once 'database.php';

$weatherDb = new WeatherDatabase();
$weatherDb->cacheWeather('Tallinn', [
    'temperature' => 15.5,
    'description' => 'Cloudy',
    'windSpeed' => 8.3,
    'humidity' => 72,
    'pressure' => 1013,
    'visibility' => 10,
    'cloudCover' => 80
]);
```

### Getting Cached Weather
```php
$cached = $weatherDb->getCachedWeather('Tallinn');
if ($cached) {
    echo "Temperature: " . $cached['temperature'] . "°C";
}
```

### Managing Favorites
```php
$weatherDb->addFavorite(session_id(), 'Tallinn');
```

## Requirements
- PHP 7.2+ (with PDO SQLite extension)
- XAMPP or similar PHP environment
- SQLite3 support (built into PHP by default)

## Database Maintenance

### Clean Old Cache
```php
$weatherDb->cleanOldCache(); // Removes entries older than 7 days
```

### Get Statistics
```php
$stats = $weatherDb->getWeatherStatistics();
// Returns: unique locations, total queries, temperature statistics
```

## Performance Notes
- Weather data cached for 30 minutes to reduce API calls
- Old cache entries automatically cleaned after 7 days
- Session-based user tracking prevents data mix-up
- Indexed searches for faster queries

## Security Features
- SQL prepared statements prevent injection
- Session-based data isolation
- Error logging to prevent information disclosure
- PDO exception handling

---

The application is now fully functional with persistent data storage using SQLite!
