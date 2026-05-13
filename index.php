<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FisherWeather - Ilm kalastajatele</title>
    <link rel="stylesheet" href="style.css">

    <script>
        window.onload = function() {
         let algne = document.getElementById("clicks");
        algne.innerHTML = localStorage.getItem("test");   
        }
        alert("Tere tulemast FisherWeather'i! See rakendus on loodud kalastajatele, et pakkuda täpset ja usaldusväärset ilmateavet, mis aitab teil valida parimad kalastamistingimused. Sisestage linn või kasutage oma praegust asukohta, et saada kohandatud ilmateavet ja kalastamise nõuandeid. Loodame, et see rakendus aitab teil nautida edukat kalapüüki igal ajal!");
        console.log("tere, kasutaja");
    </script>
       
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <h1>FisherWeather</h1>
                <p>Täpne ilmateade kalastajatele</p>
            </div>
        </header>

        <!-- Search Bar -->
        <div class="search-section">
            <div class="search-box">
                <input 
                    type="text" 
                    id="searchInput" 
                    placeholder="Sisestage linna nimi..."
                    autocomplete="off"
                >
                <button id="searchBtn" onclick="searchWeather()">Otsi</button>
                <button id="locationBtn" onclick="getCurrentLocation()" class="location-btn">Minu asukoht</button>
            </div>
            <div id="suggestions" class="suggestions"></div>
        </div>

        <!-- Error Message -->
        <div id="errorMessage" class="error-message"></div>

        <!-- Loading Section -->
        <div id="loading" class="loading">
            <p>Laadin ilmateabe...</p>
        </div>

        <!-- Main Weather Display -->
        <div id="weatherContent" class="weather-content">
            <!-- Current Weather -->
            <section class="current-weather">
                <div class="weather-header">
                    <div>
                        <h2 id="locationName">-</h2>
                        <p id="dateTime" class="date-time">-</p>
                    </div>
                </div>

                <div class="temperature-section">
                    <div class="temp-display">
                        <span id="temperature" class="temperature">-</span>
                        <span class="temp-unit">°C</span>
                    </div>
                    <p id="weatherDescription" class="weather-description">-</p>
                </div>

                <div class="fishing-conditions">
                    <h3>Kalastamistingimused</h3>
                    <div class="conditions-grid">
                        <div class="condition-card">
                            <span class="condition-icon">T</span>
                            <h4>Tuul</h4>
                            <p id="windSpeed" class="condition-value">-</p>
                            <small id="windDir">-</small>
                        </div>
                        <div class="condition-card">
                            <span class="condition-icon">N</span>
                            <h4>Niiskus</h4>
                            <p id="humidity" class="condition-value">-</p>
                        </div>
                        <div class="condition-card">
                            <span class="condition-icon">R</span>
                            <h4>Rõhk</h4>
                            <p id="pressure" class="condition-value">-</p>
                            <small>hPa</small>
                        </div>
                        <div class="condition-card">
                            <span class="condition-icon">N</span>
                            <h4>Nähtavus</h4>
                            <p id="visibility" class="condition-value">-</p>
                            <small>km</small>
                        </div>
                        <div class="condition-card">
                            <span class="condition-icon">P</span>
                            <h4>Pilvisus</h4>
                            <p id="cloudCover" class="condition-value">-</p>
                        </div>
                        <div class="condition-card">
                            <span class="condition-icon">U</span>
                            <h4>UV indeks</h4>
                            <p id="uvIndex" class="condition-value">-</p>
                        </div>
                    </div>
                </div>

                <!-- Fishing Rating -->
                <div class="fishing-rating">
                    <h3>Kalastamise hindamine</h3>
                    <div id="fishingRating" class="rating-bar">
                        <div class="rating-fill" style="width: 0%"></div>
                    </div>
                    <p id="ratingText" class="rating-text">-</p>
                </div>
            </section>

            <!-- Forecast Section -->
            <section class="forecast-section">
                <h3>5-päevane prognoos</h3>
                <div id="forecastContainer" class="forecast-grid">
                    <!-- Forecast cards will be populated here -->
                </div>
            </section>

            <!-- Sunrise/Sunset Section -->
            <section class="sun-times">
                <div class="sun-card sunrise-card">
                    <h4>Päikesetõus</h4>
                    <p id="sunrise" class="sun-time">-</p>
                </div>
                <div class="sun-card sunset-card">
                    <h4>Päikeseloojang</h4>
                    <p id="sunset" class="sun-time">-</p>
                </div>
            </section>

            <!-- Fishing Tips -->
            <section class="fishing-tips">
                <h3>Kalastamise nõuanded täna</h3>
                <ul id="tipsList">
                    <li>Nõuanded laadivad...</li>
                </ul>
            </section>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js"></script>
    <script>
        const BASE_URL = 'https://api.open-meteo.com/v1/forecast';
        const GEOCODING_URL = 'https://geocoding-api.open-meteo.com/v1/search';

        let currentData = null;

        // Get current location on page load
        window.addEventListener('load', () => {
            getCurrentLocation();
        });

        // Search functionality
        function searchWeather() {
            const city = document.getElementById('searchInput').value.trim();
            if (city) {
                searchCity(city);
            }
        }

        // Enter key to search
        document.getElementById('searchInput').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                searchWeather();
            }
        });

        // Search for city
        async function searchCity(query) {
            try {
                const response = await axios.get(GEOCODING_URL, {
                    params: {
                        name: query,
                        count: 1,
                        language: 'et',
                        format: 'json'
                    }
                });

                if (response.data.results && response.data.results.length > 0) {
                    const location = response.data.results[0];
                    showSuggestions([`${location.name}${location.admin1 ? ', ' + location.admin1 : ''}, ${location.country}`]);
                    document.getElementById('searchInput').dataset.lat = location.latitude;
                    document.getElementById('searchInput').dataset.lon = location.longitude;
                } else {
                    showError('Linn ei leitud. Proovige teist.');
                }
            } catch (error) {
                showError('Viga linna otsimisega.');
            }
        }

        // Autocomplete suggestions
        document.getElementById('searchInput').addEventListener('input', async (e) => {
            const query = e.target.value.trim();
            if (query.length > 2) {
                try {
                    const response = await axios.get(GEOCODING_URL, {
                        params: {
                            name: query,
                            count: 5,
                            language: 'et',
                            format: 'json'
                        }
                    });

                    if (response.data.results) {
                        const suggestions = response.data.results.map(r => 
                            `${r.name}${r.admin1 ? ', ' + r.admin1 : ''}, ${r.country}`
                        );
                        showSuggestions(suggestions, response.data.results);
                    }
                } catch (error) {
                    // Silent fail for autocomplete
                }
            } else {
                document.getElementById('suggestions').innerHTML = '';
            }
        });

        function showSuggestions(suggestions, locations = []) {
            const container = document.getElementById('suggestions');
            container.innerHTML = '';
            suggestions.forEach((suggestion, index) => {
                const div = document.createElement('div');
                div.textContent = suggestion;
                div.className = 'suggestion-item';
                div.onclick = () => {
                    document.getElementById('searchInput').value = suggestion;
                    if (locations[index]) {
                        fetchWeather(locations[index].latitude, locations[index].longitude, suggestion);
                    }
                    container.innerHTML = '';
                };
                container.appendChild(div);
            });
        }

        // Get current location
        async function getCurrentLocation() {
            showLoading(true);
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    async (position) => {
                        const { latitude, longitude } = position.coords;
                        fetchWeather(latitude, longitude, 'Teie asukoht');
                    },
                    () => {
                        showError('Ei saa juurde pääseda teie asukohale. Otsi linna.');
                        showLoading(false);
                    }
                );
            } else {
                showError('Geolokatsioon ei ole toetatud. Otsi linna.');
                showLoading(false);
            }
        }

        // Fetch weather data from Open-Meteo
        async function fetchWeather(latitude, longitude, locationName) {
            showLoading(true);
            clearError();
            try {
                const response = await axios.get(BASE_URL, {
                    params: {
                        latitude: latitude,
                        longitude: longitude,
                        current: 'temperature_2m,relative_humidity_2m,apparent_temperature,precipitation,weather_code,wind_speed_10m,wind_direction_10m,pressure_msl,cloud_cover,uv_index',
                        hourly: 'temperature_2m,weather_code',
                        daily: 'weather_code,temperature_2m_max,temperature_2m_min,precipitation_sum,wind_speed_10m_max,sunrise,sunset',
                        timezone: 'auto'
                    }
                });
                displayWeather(response.data, locationName, latitude, longitude);
                document.getElementById('searchInput').value = '';
                document.getElementById('suggestions').innerHTML = '';
            } catch (error) {
                showError('Viga ilmateabe hankimisel.');
                showLoading(false);
            }
        }

        // Get weather description from WMO code
        function getWeatherDescription(code) {
            const weatherCodes = {
                0: 'Selge taevas',
                1: 'Peamiselt selge',
                2: 'Osaliselt pilves',
                3: 'Pilves',
                45: 'Uduline',
                48: 'Uduline',
                51: 'Kerge märg',
                53: 'Mõõdukas märg',
                55: 'Tihe märg',
                61: 'Kerge vihm',
                63: 'Mõõdukas vihm',
                65: 'Tugev vihm',
                71: 'Kerge lumi',
                73: 'Mõõdukas lumi',
                75: 'Tugev lumi',
                77: 'Lumejäätmed',
                80: 'Kerged vihmapilved',
                81: 'Mõõdukad vihmapilved',
                82: 'Äärmisel vihmapilved',
                85: 'Kerged lumepilved',
                86: 'Tugevad lumepilved',
                95: 'Äikesemyrsky',
                96: 'Äikesemyrsky rajamuodoin',
                99: 'Äikesemyrsky rajamuodoin'
            };
            return weatherCodes[code] || 'Teadmata';
        }

        // Get weather icon from WMO code
        function getWeatherIcon(code) {
            if (code === 0) return '☀️';
            if (code === 1 || code === 2) return '🌤️';
            if (code === 3) return '☁️';
            if (code === 45 || code === 48) return '🌫️';
            if (code >= 51 && code <= 67) return '🌧️';
            if (code >= 71 && code <= 86) return '❄️';
            if (code >= 80 && code <= 82) return '🌦️';
            if (code >= 85 && code <= 86) return '🌨️';
            if (code >= 95 && code <= 99) return '⛈️';
            return '🌬️';
        }

        // Display weather data
        function displayWeather(data, locationName, latitude, longitude) {
            currentData = data;
            const current = data.current;
            const daily = data.daily;

            // Update location and time
            document.getElementById('locationName').textContent = locationName;
            document.getElementById('dateTime').textContent = new Date().toLocaleDateString('et-EE', {
                weekday: 'long',
                month: 'long',
                day: 'numeric'
            });

            // Update temperature section
            document.getElementById('temperature').textContent = Math.round(current.temperature_2m);
            document.getElementById('weatherDescription').textContent = getWeatherDescription(current.weather_code);

            // Update fishing conditions
            document.getElementById('windSpeed').textContent = Math.round(current.wind_speed_10m) + ' km/h';
            document.getElementById('windDir').textContent = getWindDirection(current.wind_direction_10m);
            document.getElementById('humidity').textContent = current.relative_humidity_2m + '%';
            document.getElementById('pressure').textContent = Math.round(current.pressure_msl);
            document.getElementById('visibility').textContent = '10'; // Open-Meteo doesn't provide visibility
            document.getElementById('cloudCover').textContent = current.cloud_cover + '%';
            document.getElementById('uvIndex').textContent = current.uv_index.toFixed(1);

            // Sunrise and Sunset
            document.getElementById('sunrise').textContent = formatTime(daily.sunrise[0]);
            document.getElementById('sunset').textContent = formatTime(daily.sunset[0]);

            // Calculate and display fishing rating
            calculateFishingRating(current, daily);

            // Display forecast
            displayForecast(daily);

            // Generate fishing tips
            generateFishingTips(current, daily);

            showLoading(false);
        }

        // Get wind direction from degrees
        function getWindDirection(degrees) {
            const directions = ['N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW'];
            const index = Math.round(degrees / 22.5) % 16;
            return directions[index];
        }

        // Format time HH:MM
        function formatTime(dateString) {
            const date = new Date(dateString);
            return date.toLocaleTimeString('et-EE', { hour: '2-digit', minute: '2-digit', hour12: false });
        }

        // Calculate fishing rating based on conditions
        function calculateFishingRating(current, daily) {
            let rating = 50; // Base rating

            // Cloud cover (better when overcast)
            if (current.cloud_cover > 30 && current.cloud_cover < 90) rating += 20;

            // Wind (moderate is good)
            const wind = current.wind_speed_10m;
            if (wind > 10 && wind < 30) rating += 15;
            if (wind > 30) rating -= 10;

            // Humidity (higher is better)
            if (current.relative_humidity_2m > 60) rating += 10;

            // Pressure (higher is stable)
            if (current.pressure_msl > 1010) rating += 5;

            // Weather code (rain reduces fishing)
            if (current.weather_code >= 51 && current.weather_code <= 67) rating -= 10;

            rating = Math.min(100, Math.max(0, rating));

            document.querySelector('.rating-fill').style.width = rating + '%';

            let ratingText = '';
            if (rating >= 80) ratingText = 'Suurepärane - Täiuslikud tingimused kalastamiseks!';
            else if (rating >= 60) ratingText = 'Hea - Ilus päev kalastamiseks';
            else if (rating >= 40) ratingText = 'Rahuldav - Korralikud tingimused, tasub proovida';
            else ratingText = 'Halb - Keeruline ilm täna';

            document.getElementById('ratingText').textContent = ratingText;
        }

        // Display forecast
        function displayForecast(daily) {
            const container = document.getElementById('forecastContainer');
            container.innerHTML = '';

            for (let i = 0; i < Math.min(5, daily.time.length); i++) {
                const date = new Date(daily.time[i]);
                const dayName = date.toLocaleDateString('et-EE', { weekday: 'short' });
                const dayNum = date.toLocaleDateString('et-EE', { month: 'short', day: 'numeric' });
                const code = daily.weather_code[i];

                const card = document.createElement('div');
                card.className = 'forecast-card';
                card.innerHTML = `
                    <h4>${dayName}</h4>
                    <p class="forecast-date">${dayNum}</p>
                    <p class="forecast-icon" style="font-size: 2rem; margin: 5px 0;">${getWeatherIcon(code)}</p>
                    <p class="forecast-temp">${Math.round(daily.temperature_2m_max[i])}°</p>
                    <p class="forecast-min">${Math.round(daily.temperature_2m_min[i])}°</p>
                    <p class="forecast-condition">${getWeatherDescription(code)}</p>
                    <p class="forecast-wind">Tuul: ${Math.round(daily.wind_speed_10m_max[i])} km/h</p>
                `;
                container.appendChild(card);
            }
        }

        // Generate fishing tips
        function generateFishingTips(current, daily) {
            const tips = [];

            // Cloud cover tips
            if (current.cloud_cover > 70) {
                tips.push('Raske pilvisus on suurepärane kaladele - kalad on aktiivsemad');
            } else if (current.cloud_cover > 30) {
                tips.push('Osalised pilved loovad ideaalsed tingimused kala tegevusele');
            } else {
                tips.push('Selge taevas muudab kalastamise raskemaks - proovige vara hommikul või hilisõhtul');
            }

            // Wind tips
            if (current.wind_speed_10m < 5) {
                tips.push('Väga rahulik ilm - kalad on pinnale tulnud');
            } else if (current.wind_speed_10m < 20) {
                tips.push('Mõõdukas tuul loob laineid - täiuslik kalastamiseks');
            } else if (current.wind_speed_10m < 30) {
                tips.push('Tugevtuul segab vett - kalad toituvad rohkem');
            } else {
                tips.push('Väga tuuline - olge ettevaatlik ja valige varjustatud kohad');
            }

            // Temperature tips
            if (current.temperature_2m < 10) {
                tips.push('Külm vesi - aeglasem ainevahetis, kalad söövad vähem');
            } else if (current.temperature_2m > 25) {
                tips.push('Soe vesi - kaladus hommikul või õhtul on parim');
            } else {
                tips.push('Ideaalne veehulga aktiivsete kalasude jaoks');
            }

            // Pressure tips
            if (current.pressure_msl > 1020) {
                tips.push('Kõrge rõhk - stabiilsed tingimused, järjepidev kaladus');
            } else if (current.pressure_msl < 1000) {
                tips.push('Madal rõhk - oodake aktiivsemaid kalasid');
            }

            // Humidity tips
            if (current.relative_humidity_2m > 70) {
                tips.push('Kõrge niiskus ja märg olek soodustab kalastamist');
            }

            // Weather tips
            if (daily.precipitation_sum[0] > 5) {
                tips.push('Täna on oodata vihma - võib põhjustada ajutist tegevutuks erisolekut enne tormi');
            }

            const tipsList = document.getElementById('tipsList');
            tipsList.innerHTML = '';
            tips.forEach(tip => {
                const li = document.createElement('li');
                li.textContent = tip;
                tipsList.appendChild(li);
            });
        }

        // Utility functions
        function showLoading(show) {
            document.getElementById('loading').style.display = show ? 'block' : 'none';
            document.getElementById('weatherContent').style.display = show ? 'none' : 'block';
        }

        function showError(message) {
            document.getElementById('errorMessage').textContent = message;
            document.getElementById('errorMessage').style.display = 'block';
        }

        function clearError() {
            document.getElementById('errorMessage').style.display = 'none';
        }
    </script>
</body>
</html>