// Mock Database for Country, State, City
const DATABASE = {
    countries: [
        { id: 1, name: 'India', code: 'IN' },
        { id: 2, name: 'United States', code: 'US' },
        { id: 3, name: 'United Kingdom', code: 'UK' },
        { id: 4, name: 'Canada', code: 'CA' },
        { id: 5, name: 'Australia', code: 'AU' }
    ],

    states: [
        // India States
        { id: 1, name: 'Gujarat', countryId: 1 },
        { id: 2, name: 'Maharashtra', countryId: 1 },
        { id: 3, name: 'Karnataka', countryId: 1 },
        { id: 4, name: 'Tamil Nadu', countryId: 1 },
        { id: 5, name: 'Rajasthan', countryId: 1 },
        { id: 6, name: 'Punjab', countryId: 1 },
        { id: 7, name: 'West Bengal', countryId: 1 },
        { id: 8, name: 'Uttar Pradesh', countryId: 1 },

        // United States States
        { id: 9, name: 'California', countryId: 2 },
        { id: 10, name: 'New York', countryId: 2 },
        { id: 11, name: 'Texas', countryId: 2 },
        { id: 12, name: 'Florida', countryId: 2 },
        { id: 13, name: 'Illinois', countryId: 2 },

        // United Kingdom States
        { id: 14, name: 'England', countryId: 3 },
        { id: 15, name: 'Scotland', countryId: 3 },
        { id: 16, name: 'Wales', countryId: 3 },
        { id: 17, name: 'Northern Ireland', countryId: 3 },

        // Canada States
        { id: 18, name: 'Ontario', countryId: 4 },
        { id: 19, name: 'Quebec', countryId: 4 },
        { id: 20, name: 'British Columbia', countryId: 4 },
        { id: 21, name: 'Alberta', countryId: 4 },

        // Australia States
        { id: 22, name: 'New South Wales', countryId: 5 },
        { id: 23, name: 'Victoria', countryId: 5 },
        { id: 24, name: 'Queensland', countryId: 5 },
        { id: 25, name: 'Western Australia', countryId: 5 }
    ],

    cities: [
        // Gujarat Cities
        { id: 1, name: 'Ahmedabad', stateId: 1 },
        { id: 2, name: 'Surat', stateId: 1 },
        { id: 3, name: 'Vadodara (Baroda)', stateId: 1 },
        { id: 4, name: 'Rajkot', stateId: 1 },
        { id: 5, name: 'Bhavnagar', stateId: 1 },
        { id: 6, name: 'Jamnagar', stateId: 1 },

        // Maharashtra Cities
        { id: 7, name: 'Mumbai', stateId: 2 },
        { id: 8, name: 'Pune', stateId: 2 },
        { id: 9, name: 'Nagpur', stateId: 2 },
        { id: 10, name: 'Nashik', stateId: 2 },
        { id: 11, name: 'Aurangabad', stateId: 2 },

        // Karnataka Cities
        { id: 12, name: 'Bangalore', stateId: 3 },
        { id: 13, name: 'Mysore', stateId: 3 },
        { id: 14, name: 'Hubli', stateId: 3 },
        { id: 15, name: 'Mangalore', stateId: 3 },

        // Tamil Nadu Cities
        { id: 16, name: 'Chennai', stateId: 4 },
        { id: 17, name: 'Coimbatore', stateId: 4 },
        { id: 18, name: 'Madurai', stateId: 4 },
        { id: 19, name: 'Tiruchirappalli', stateId: 4 },

        // Rajasthan Cities
        { id: 20, name: 'Jaipur', stateId: 5 },
        { id: 21, name: 'Jodhpur', stateId: 5 },
        { id: 22, name: 'Udaipur', stateId: 5 },
        { id: 23, name: 'Kota', stateId: 5 },

        // Punjab Cities
        { id: 24, name: 'Chandigarh', stateId: 6 },
        { id: 25, name: 'Ludhiana', stateId: 6 },
        { id: 26, name: 'Amritsar', stateId: 6 },
        { id: 27, name: 'Jalandhar', stateId: 6 },

        // West Bengal Cities
        { id: 28, name: 'Kolkata', stateId: 7 },
        { id: 29, name: 'Howrah', stateId: 7 },
        { id: 30, name: 'Durgapur', stateId: 7 },
        { id: 31, name: 'Asansol', stateId: 7 },

        // Uttar Pradesh Cities
        { id: 32, name: 'Lucknow', stateId: 8 },
        { id: 33, name: 'Kanpur', stateId: 8 },
        { id: 34, name: 'Agra', stateId: 8 },
        { id: 35, name: 'Varanasi', stateId: 8 },

        // California Cities
        { id: 36, name: 'Los Angeles', stateId: 9 },
        { id: 37, name: 'San Francisco', stateId: 9 },
        { id: 38, name: 'San Diego', stateId: 9 },
        { id: 39, name: 'Sacramento', stateId: 9 },

        // New York Cities
        { id: 40, name: 'New York City', stateId: 10 },
        { id: 41, name: 'Buffalo', stateId: 10 },
        { id: 42, name: 'Rochester', stateId: 10 },
        { id: 43, name: 'Syracuse', stateId: 10 },

        // Texas Cities
        { id: 44, name: 'Houston', stateId: 11 },
        { id: 45, name: 'Dallas', stateId: 11 },
        { id: 46, name: 'Austin', stateId: 11 },
        { id: 47, name: 'San Antonio', stateId: 11 },

        // Florida Cities
        { id: 48, name: 'Miami', stateId: 12 },
        { id: 49, name: 'Orlando', stateId: 12 },
        { id: 50, name: 'Tampa', stateId: 12 },
        { id: 51, name: 'Jacksonville', stateId: 12 },

        // Illinois Cities
        { id: 52, name: 'Chicago', stateId: 13 },
        { id: 53, name: 'Aurora', stateId: 13 },
        { id: 54, name: 'Peoria', stateId: 13 },
        { id: 55, name: 'Rockford', stateId: 13 },

        // England Cities
        { id: 56, name: 'London', stateId: 14 },
        { id: 57, name: 'Manchester', stateId: 14 },
        { id: 58, name: 'Birmingham', stateId: 14 },
        { id: 59, name: 'Liverpool', stateId: 14 },

        // Scotland Cities
        { id: 60, name: 'Edinburgh', stateId: 15 },
        { id: 61, name: 'Glasgow', stateId: 15 },
        { id: 62, name: 'Aberdeen', stateId: 15 },
        { id: 63, name: 'Dundee', stateId: 15 },

        // Wales Cities
        { id: 64, name: 'Cardiff', stateId: 16 },
        { id: 65, name: 'Swansea', stateId: 16 },
        { id: 66, name: 'Newport', stateId: 16 },

        // Northern Ireland Cities
        { id: 67, name: 'Belfast', stateId: 17 },
        { id: 68, name: 'Derry', stateId: 17 },

        // Ontario Cities
        { id: 69, name: 'Toronto', stateId: 18 },
        { id: 70, name: 'Ottawa', stateId: 18 },
        { id: 71, name: 'Hamilton', stateId: 18 },
        { id: 72, name: 'London', stateId: 18 },

        // Quebec Cities
        { id: 73, name: 'Montreal', stateId: 19 },
        { id: 74, name: 'Quebec City', stateId: 19 },
        { id: 75, name: 'Laval', stateId: 19 },

        // British Columbia Cities
        { id: 76, name: 'Vancouver', stateId: 20 },
        { id: 77, name: 'Victoria', stateId: 20 },
        { id: 78, name: 'Surrey', stateId: 20 },

        // Alberta Cities
        { id: 79, name: 'Calgary', stateId: 21 },
        { id: 80, name: 'Edmonton', stateId: 21 },
        { id: 81, name: 'Red Deer', stateId: 21 },

        // New South Wales Cities
        { id: 82, name: 'Sydney', stateId: 22 },
        { id: 83, name: 'Newcastle', stateId: 22 },
        { id: 84, name: 'Wollongong', stateId: 22 },

        // Victoria Cities
        { id: 85, name: 'Melbourne', stateId: 23 },
        { id: 86, name: 'Geelong', stateId: 23 },
        { id: 87, name: 'Ballarat', stateId: 23 },

        // Queensland Cities
        { id: 88, name: 'Brisbane', stateId: 24 },
        { id: 89, name: 'Gold Coast', stateId: 24 },
        { id: 90, name: 'Cairns', stateId: 24 },

        // Western Australia Cities
        { id: 91, name: 'Perth', stateId: 25 },
        { id: 92, name: 'Fremantle', stateId: 25 },
        { id: 93, name: 'Bunbury', stateId: 25 }
    ]
};

// Database API Functions
const DatabaseAPI = {
    // Get all countries
    getCountries() {
        return new Promise((resolve) => {
            setTimeout(() => {
                resolve([...DATABASE.countries]);
            }, 300); // Simulate API delay
        });
    },

    // Get states by country ID
    getStatesByCountry(countryId) {
        return new Promise((resolve) => {
            setTimeout(() => {
                const states = DATABASE.states.filter(state => state.countryId == countryId);
                resolve([...states]);
            }, 200);
        });
    },

    // Get cities by state ID
    getCitiesByState(stateId) {
        return new Promise((resolve) => {
            setTimeout(() => {
                const cities = DATABASE.cities.filter(city => city.stateId == stateId);
                resolve([...cities]);
            }, 200);
        });
    },

    // Get country by ID
    getCountryById(countryId) {
        return DATABASE.countries.find(country => country.id == countryId);
    },

    // Get state by ID
    getStateById(stateId) {
        return DATABASE.states.find(state => state.id == stateId);
    },

    // Get city by ID
    getCityById(cityId) {
        return DATABASE.cities.find(city => city.id == cityId);
    },

    // Submit form data (simulate API call)
    submitSelection(data) {
        return new Promise((resolve, reject) => {
            setTimeout(() => {
                if (data.country && data.state && data.city) {
                    resolve({
                        success: true,
                        message: 'Selection submitted successfully!',
                        data: data,
                        timestamp: new Date().toLocaleString()
                    });
                } else {
                    reject({
                        success: false,
                        message: 'Invalid data provided'
                    });
                }
            }, 1000);
        });
    }
};