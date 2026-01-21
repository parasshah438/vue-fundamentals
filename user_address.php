<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Location Dropdown</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        /* Copy all the CSS from the previous artifact here */
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        
        .location-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .location-dropdown {
            position: relative;
        }
        
        .dropdown-panel {
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 1000;
            display: none;
            min-width: 320px;
        }
        
        .dropdown-panel.show {
            display: block;
        }
        
        .left-panel {
            width: 320px;
            max-height: 400px;
            overflow-y: auto;
            background-color: white;
            position: relative;
        }
        
        .back-header {
            padding: 12px 16px;
            background-color: #e7f3ff;
            border-bottom: 1px solid #dee2e6;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .form-check-item {
            padding: 11px 16px;
            margin: 0;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            cursor: pointer;
            position: relative;
        }
        
        .form-check-item:hover {
            background-color: #f8f9fa;
        }
        
        .form-check-input[type="radio"] {
            width: 18px;
            height: 18px;
            margin: 0;
            cursor: pointer;
            flex-shrink: 0;
        }
        
        .form-check-label {
            cursor: pointer;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-left: 10px;
            margin-bottom: 0;
        }
        
        .chevron-icon {
            color: #6c757d;
            font-size: 14px;
            margin-left: auto;
        }
        
        .right-panel, .city-panel {
            position: fixed;
            width: 280px;
            background-color: white;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            box-shadow: 2px 2px 8px rgba(0,0,0,0.15);
            max-height: 400px;
            overflow-y: auto;
            display: none;
            z-index: 1002;
        }
        
        .right-panel.show, .city-panel.show {
            display: block;
        }
        
        .right-panel-header {
            padding: 12px 16px;
            background-color: #f8f9fa;
            color: #333;
            border-bottom: 1px solid #dee2e6;
            font-weight: 600;
            font-size: 14px;
        }
        
        .option-item {
            padding: 11px 16px;
            display: flex;
            align-items: center;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            background-color: white;
        }
        
        .option-item:hover {
            background-color: #f8f9fa;
        }
        
        .loading {
            padding: 20px;
            text-align: center;
            color: #6c757d;
        }
        
        .selected-info {
            margin-top: 15px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 8px;
        }
        
        .info-label {
            font-weight: 600;
            width: 100px;
            color: #495057;
        }
        
        .info-value {
            color: #0d6efd;
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="location-container">
        <h5 class="mb-4">Select Your Location</h5>
        
        <div class="mb-3 location-dropdown">
            <label class="form-label">Country, State & City</label>
            <input type="text" 
                   class="form-control" 
                   id="locationInput" 
                   placeholder="Click to select location"
                   readonly>
            
            <div class="dropdown-panel" id="dropdownPanel">
                <div class="left-panel" id="leftPanel">
                    <div class="back-header">
                        <i class="bi bi-globe"></i>
                        <span>Countries</span>
                    </div>
                    <div class="loading">Loading countries...</div>
                </div>
            </div>
            
            <!-- Dynamic state panel -->
            <div class="right-panel" id="statePanel">
                <div class="right-panel-header" id="statePanelHeader">States</div>
                <div id="stateList"></div>
            </div>
            
            <!-- Dynamic city panel -->
            <div class="city-panel" id="cityPanel">
                <div class="right-panel-header" id="cityPanelHeader">Cities</div>
                <div id="cityList"></div>
            </div>
            
            <div class="selected-info" id="selectedInfo" style="display: none;">
                <div class="info-row">
                    <span class="info-label">Country:</span>
                    <span class="info-value" id="displayCountry">-</span>
                </div>
                <div class="info-row">
                    <span class="info-label">State:</span>
                    <span class="info-value" id="displayState">-</span>
                </div>
                <div class="info-row">
                    <span class="info-label">City:</span>
                    <span class="info-value" id="displayCity">-</span>
                </div>
            </div>
        </div>
        
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary flex-fill" onclick="clearSelection()">Clear</button>
            <button type="button" class="btn btn-primary flex-fill" onclick="saveToDatabase()">Save Address</button>
        </div>
    </div>

    <script>
        let selectedCountry = { id: null, name: null };
        let selectedState = { id: null, name: null };
        let selectedCity = { id: null, name: null };
        
        const input = document.getElementById('locationInput');
        const panel = document.getElementById('dropdownPanel');
        const statePanel = document.getElementById('statePanel');
        const cityPanel = document.getElementById('cityPanel');
        
        // Load countries on page load
        window.addEventListener('DOMContentLoaded', function() {
            loadCountries();
        });
        
        input.addEventListener('click', function(e) {
            e.stopPropagation();
            panel.classList.toggle('show');
        });
        
        document.addEventListener('click', function(e) {
            if (!panel.contains(e.target) && e.target !== input && 
                !statePanel.contains(e.target) && !cityPanel.contains(e.target)) {
                panel.classList.remove('show');
                statePanel.classList.remove('show');
                cityPanel.classList.remove('show');
            }
        });
        
        // Load countries from database
        function loadCountries() {
            fetch('get_countries.php')
                .then(response => response.json())
                .then(countries => {
                    const leftPanel = document.getElementById('leftPanel');
                    let html = `
                        <div class="back-header">
                            <i class="bi bi-globe"></i>
                            <span>Countries</span>
                        </div>
                    `;
                    
                    countries.forEach(country => {
                        html += `
                            <div class="form-check-item has-submenu" data-country-id="${country.id}" data-country-name="${country.name}">
                                <input class="form-check-input" type="radio" name="country" id="country_${country.id}" 
                                       onchange="handleCountrySelection(${country.id}, '${country.name}')" 
                                       onclick="event.stopPropagation()">
                                <label class="form-check-label" for="country_${country.id}">
                                    <span>${country.name}</span>
                                    <i class="bi bi-chevron-right chevron-icon"></i>
                                </label>
                            </div>
                        `;
                    });
                    
                    leftPanel.innerHTML = html;
                    attachCountryHoverEvents();
                })
                .catch(error => {
                    console.error('Error loading countries:', error);
                    document.getElementById('leftPanel').innerHTML = '<div class="loading">Error loading countries</div>';
                });
        }
        
        // Load states by country
        function loadStates(countryId, countryName) {
            const stateList = document.getElementById('stateList');
            const statePanelHeader = document.getElementById('statePanelHeader');
            
            statePanelHeader.textContent = countryName + ' - States';
            stateList.innerHTML = '<div class="loading">Loading states...</div>';
            
            fetch(`get_states.php?country_id=${countryId}`)
                .then(response => response.json())
                .then(states => {
                    let html = '';
                    
                    if (states.length === 0) {
                        html = '<div class="loading">No states found</div>';
                    } else {
                        states.forEach(state => {
                            html += `
                                <div class="option-item has-cities" data-state-id="${state.id}" data-state-name="${state.name}">
                                    <input type="radio" name="state" id="state_${state.id}" 
                                           onchange="handleStateSelection(${state.id}, '${state.name}')" 
                                           onclick="event.stopPropagation()">
                                    <label for="state_${state.id}">${state.name}</label>
                                    <i class="bi bi-chevron-right chevron-icon"></i>
                                </div>
                            `;
                        });
                    }
                    
                    stateList.innerHTML = html;
                    attachStateHoverEvents();
                })
                .catch(error => {
                    console.error('Error loading states:', error);
                    stateList.innerHTML = '<div class="loading">Error loading states</div>';
                });
        }
        
        // Load cities by state
        function loadCities(stateId, stateName) {
            const cityList = document.getElementById('cityList');
            const cityPanelHeader = document.getElementById('cityPanelHeader');
            
            cityPanelHeader.textContent = stateName + ' - Cities';
            cityList.innerHTML = '<div class="loading">Loading cities...</div>';
            
            fetch(`get_cities.php?state_id=${stateId}`)
                .then(response => response.json())
                .then(cities => {
                    let html = '';
                    
                    if (cities.length === 0) {
                        html = '<div class="loading">No cities found</div>';
                    } else {
                        cities.forEach(city => {
                            html += `
                                <div class="option-item">
                                    <input type="radio" name="city" id="city_${city.id}" 
                                           onchange="handleCitySelection(${city.id}, '${city.name}')" 
                                           onclick="event.stopPropagation()">
                                    <label for="city_${city.id}">${city.name}</label>
                                </div>
                            `;
                        });
                    }
                    
                    cityList.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error loading cities:', error);
                    cityList.innerHTML = '<div class="loading">Error loading cities</div>';
                });
        }
        
        // Attach hover events to countries
        function attachCountryHoverEvents() {
            document.querySelectorAll('.left-panel .has-submenu').forEach(item => {
                item.addEventListener('mouseenter', function() {
                    const countryId = this.getAttribute('data-country-id');
                    const countryName = this.getAttribute('data-country-name');
                    
                    const itemRect = this.getBoundingClientRect();
                    const panelRect = panel.getBoundingClientRect();
                    
                    statePanel.style.left = (panelRect.right + 5) + 'px';
                    statePanel.style.top = itemRect.top + 'px';
                    
                    cityPanel.classList.remove('show');
                    statePanel.classList.add('show');
                    
                    loadStates(countryId, countryName);
                });
            });
        }
        
        // Attach hover events to states
        function attachStateHoverEvents() {
            document.querySelectorAll('.option-item.has-cities').forEach(item => {
                item.addEventListener('mouseenter', function() {
                    const stateId = this.getAttribute('data-state-id');
                    const stateName = this.getAttribute('data-state-name');
                    
                    const itemRect = this.getBoundingClientRect();
                    const statePanelRect = statePanel.getBoundingClientRect();
                    
                    cityPanel.style.left = (statePanelRect.right + 5) + 'px';
                    cityPanel.style.top = itemRect.top + 'px';
                    cityPanel.classList.add('show');
                    
                    loadCities(stateId, stateName);
                });
            });
        }
        
        function handleCountrySelection(countryId, countryName) {
            selectedCountry = { id: countryId, name: countryName };
            selectedState = { id: null, name: null };
            selectedCity = { id: null, name: null };
            updateDisplay();
        }
        
        function handleStateSelection(stateId, stateName) {
            if (!selectedCountry.id) {
                alert('Please select a country first!');
                return;
            }
            selectedState = { id: stateId, name: stateName };
            selectedCity = { id: null, name: null };
            updateDisplay();
        }
        
        function handleCitySelection(cityId, cityName) {
            if (!selectedCountry.id || !selectedState.id) {
                alert('Please select Country and State first!');
                return;
            }
            selectedCity = { id: cityId, name: cityName };
            updateDisplay();
            
            setTimeout(() => {
                panel.classList.remove('show');
                statePanel.classList.remove('show');
                cityPanel.classList.remove('show');
            }, 200);
        }
        
        function updateDisplay() {
            const selectedInfo = document.getElementById('selectedInfo');
            
            if (selectedCountry.id) {
                selectedInfo.style.display = 'block';
                document.getElementById('displayCountry').textContent = selectedCountry.name || '-';
                document.getElementById('displayState').textContent = selectedState.name || '-';
                document.getElementById('displayCity').textContent = selectedCity.name || '-';
                
                let displayText = selectedCountry.name;
                if (selectedState.name) displayText += ', ' + selectedState.name;
                if (selectedCity.name) displayText += ', ' + selectedCity.name;
                input.value = displayText;
            } else {
                selectedInfo.style.display = 'none';
                input.value = '';
            }
        }
        
        function clearSelection() {
            selectedCountry = { id: null, name: null };
            selectedState = { id: null, name: null };
            selectedCity = { id: null, name: null };
            document.querySelectorAll('input[type="radio"]').forEach(r => r.checked = false);
            updateDisplay();
        }
        
        function saveToDatabase() {
            if (!selectedCountry.id || !selectedState.id || !selectedCity.id) {
                alert('Please select Country, State and City before saving!');
                return;
            }
            
            const data = {
                country_id: selectedCountry.id,
                state_id: selectedState.id,
                city_id: selectedCity.id,
                user_id: 1 // Replace with actual user ID from session
            };
            
            fetch('save_address.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('Address saved successfully!');
                } else {
                    console.log(result.error);
                    alert(result.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to save address');
            });
        }
    </script>
</body>
</html>