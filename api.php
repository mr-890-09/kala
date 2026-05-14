<?php
header('Content-Type: application/json');
require_once 'config.php';

$action = $_GET['action'] ?? '';
$db = getDatabase();

if (!$db) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Get or create session
if (empty($_SESSION['id'])) {
    $_SESSION['id'] = session_id();
}

switch ($action) {
    case 'save_weather':
        saveWeatherData($_POST, $db);
        break;
    
    case 'get_weather':
        getWeatherData($_POST['location'] ?? '', $db);
        break;
    
    case 'save_preference':
        saveUserPreference($_POST, $db);
        break;
    
    case 'get_preferences':
        getUserPreferences($db);
        break;
    
    case 'add_to_history':
        addToSearchHistory($_POST['location'] ?? '', $db);
        break;
    
    case 'get_history':
        getSearchHistory($db);
        break;
    
    case 'get_favorites':
        getFavoriteLocations($db);
        break;
    
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}

function saveWeatherData($data, $db) {
    try {
        $stmt = $db->prepare("
            INSERT INTO weather_cache (location, temperature, description, wind_speed, wind_direction, humidity, pressure, visibility, cloud_cover, raw_data)
            VALUES (:location, :temperature, :description, :wind_speed, :wind_direction, :humidity, :pressure, :visibility, :cloud_cover, :raw_data)
        ");
        
        $stmt->execute([
            ':location' => $data['location'] ?? '',
            ':temperature' => $data['temperature'] ?? null,
            ':description' => $data['description'] ?? '',
            ':wind_speed' => $data['wind_speed'] ?? null,
            ':wind_direction' => $data['wind_direction'] ?? '',
            ':humidity' => $data['humidity'] ?? null,
            ':pressure' => $data['pressure'] ?? null,
            ':visibility' => $data['visibility'] ?? null,
            ':cloud_cover' => $data['cloud_cover'] ?? null,
            ':raw_data' => $data['raw_data'] ?? ''
        ]);
        
        echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function getWeatherData($location, $db) {
    try {
        // Get cached weather data (within last 30 minutes)
        $stmt = $db->prepare("
            SELECT * FROM weather_cache 
            WHERE LOWER(location) = LOWER(:location)
            AND cached_at > datetime('now', '-30 minutes')
            ORDER BY cached_at DESC
            LIMIT 1
        ");
        
        $stmt->execute([':location' => $location]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $result['raw_data'] = json_decode($result['raw_data'], true);
            echo json_encode($result);
        } else {
            echo json_encode(['error' => 'No cached data found', 'location' => $location]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function saveUserPreference($data, $db) {
    try {
        $sessionId = session_id();
        
        $stmt = $db->prepare("
            SELECT id FROM user_preferences WHERE session_id = :session_id
        ");
        $stmt->execute([':session_id' => $sessionId]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            $stmt = $db->prepare("
                UPDATE user_preferences 
                SET favorite_locations = :favorites, temperature_unit = :unit, updated_at = CURRENT_TIMESTAMP
                WHERE session_id = :session_id
            ");
        } else {
            $stmt = $db->prepare("
                INSERT INTO user_preferences (session_id, favorite_locations, temperature_unit)
                VALUES (:session_id, :favorites, :unit)
            ");
        }
        
        $stmt->execute([
            ':session_id' => $sessionId,
            ':favorites' => $data['favorites'] ?? '',
            ':unit' => $data['unit'] ?? 'C'
        ]);
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function getUserPreferences($db) {
    try {
        $sessionId = session_id();
        
        $stmt = $db->prepare("
            SELECT * FROM user_preferences WHERE session_id = :session_id
        ");
        $stmt->execute([':session_id' => $sessionId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $result['favorite_locations'] = json_decode($result['favorite_locations'], true) ?? [];
            echo json_encode($result);
        } else {
            echo json_encode(['favorite_locations' => [], 'temperature_unit' => 'C']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function addToSearchHistory($location, $db) {
    try {
        $stmt = $db->prepare("
            INSERT INTO search_history (session_id, location)
            VALUES (:session_id, :location)
        ");
        
        $stmt->execute([
            ':session_id' => session_id(),
            ':location' => $location
        ]);
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function getSearchHistory($db) {
    try {
        $stmt = $db->prepare("
            SELECT DISTINCT location FROM search_history 
            WHERE session_id = :session_id
            ORDER BY searched_at DESC
            LIMIT 10
        ");
        
        $stmt->execute([':session_id' => session_id()]);
        $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo json_encode(['history' => $results]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function getFavoriteLocations($db) {
    try {
        $stmt = $db->prepare("
            SELECT favorite_locations FROM user_preferences 
            WHERE session_id = :session_id
        ");
        
        $stmt->execute([':session_id' => session_id()]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $favorites = [];
        if ($result && $result['favorite_locations']) {
            $favorites = json_decode($result['favorite_locations'], true) ?? [];
        }
        
        echo json_encode(['favorites' => $favorites]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
