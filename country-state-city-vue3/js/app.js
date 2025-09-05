// Vue.js 3 Application with PHP Database Backend
const { createApp, ref, reactive, computed, watch, onMounted } = Vue;

createApp({
    setup() {
        // API Base URL
        const API_BASE = 'api/';
        
        // Reactive data
        const countries = ref([]);
        const states = ref([]);
        const cities = ref([]);
        
        const selectedCountry = ref('');
        const selectedState = ref('');
        const selectedCity = ref('');
        
        const isSubmitting = ref(false);
        const submissionResult = ref(null);
        
        // Form validation errors
        const errors = reactive({
            country: '',
            state: '',
            city: ''
        });

        // Loading states
        const loading = reactive({
            countries: false,
            states: false,
            cities: false
        });

        // Validation rules
        const validationRules = {
            country: {
                required: true,
                message: 'Please select a country'
            },
            state: {
                required: true,
                message: 'Please select a state'
            },
            city: {
                required: true,
                message: 'Please select a city'
            }
        };

        // API Helper Functions
        const apiCall = async (url, options = {}) => {
            try {
                const response = await fetch(url, {
                    headers: {
                        'Content-Type': 'application/json',
                        ...options.headers
                    },
                    ...options
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'API request failed');
                }

                return data;
            } catch (error) {
                console.error('API Error:', error);
                throw error;
            }
        };

        // Clear validation errors
        const clearErrors = () => {
            Object.keys(errors).forEach(key => {
                errors[key] = '';
            });
        };

        // Validate single field
        const validateField = (fieldName, value) => {
            const rule = validationRules[fieldName];
            
            if (rule.required && (!value || value === '')) {
                errors[fieldName] = rule.message;
                return false;
            }
            
            errors[fieldName] = '';
            return true;
        };

        // Validate entire form
        const validateForm = () => {
            let isValid = true;
            
            isValid = validateField('country', selectedCountry.value) && isValid;
            isValid = validateField('state', selectedState.value) && isValid;
            isValid = validateField('city', selectedCity.value) && isValid;
            
            return isValid;
        };

        // Load countries from PHP API
        const loadCountries = async () => {
            try {
                loading.countries = true;
                const response = await apiCall(API_BASE + 'countries.php');
                
                if (response.success) {
                    countries.value = response.data;
                } else {
                    throw new Error(response.message);
                }
            } catch (error) {
                console.error('Error loading countries:', error);
                alert('Error loading countries: ' + error.message);
            } finally {
                loading.countries = false;
            }
        };

        // Handle country selection change
        const onCountryChange = async () => {
            // Clear dependent selections
            selectedState.value = '';
            selectedCity.value = '';
            states.value = [];
            cities.value = [];
            
            // Clear validation errors
            errors.state = '';
            errors.city = '';
            
            // Validate country selection
            validateField('country', selectedCountry.value);
            
            if (selectedCountry.value) {
                try {
                    loading.states = true;
                    const response = await apiCall(API_BASE + `states.php?country_id=${selectedCountry.value}`);
                    
                    if (response.success) {
                        states.value = response.data;
                    } else {
                        throw new Error(response.message);
                    }
                } catch (error) {
                    console.error('Error loading states:', error);
                    alert('Error loading states: ' + error.message);
                } finally {
                    loading.states = false;
                }
            }
        };

        // Handle state selection change
        const onStateChange = async () => {
            // Clear dependent selections
            selectedCity.value = '';
            cities.value = [];
            
            // Clear validation errors
            errors.city = '';
            
            // Validate state selection
            validateField('state', selectedState.value);
            
            if (selectedState.value) {
                try {
                    loading.cities = true;
                    const response = await apiCall(API_BASE + `cities.php?state_id=${selectedState.value}`);
                    
                    if (response.success) {
                        cities.value = response.data;
                    } else {
                        throw new Error(response.message);
                    }
                } catch (error) {
                    console.error('Error loading cities:', error);
                    alert('Error loading cities: ' + error.message);
                } finally {
                    loading.cities = false;
                }
            }
        };

        // Handle city selection change
        const onCityChange = () => {
            validateField('city', selectedCity.value);
        };

        // Submit form to PHP API
        const submitForm = async () => {
            // Clear previous results
            submissionResult.value = null;
            
            // Validate form
            if (!validateForm()) {
                return;
            }

            try {
                isSubmitting.value = true;
                
                const formData = {
                    countryId: selectedCountry.value,
                    stateId: selectedState.value,
                    cityId: selectedCity.value
                };

                // Submit to PHP API
                const response = await apiCall(API_BASE + 'submit.php', {
                    method: 'POST',
                    body: JSON.stringify(formData)
                });
                
                if (response.success) {
                    submissionResult.value = {
                        country: response.data.country,
                        state: response.data.state,
                        city: response.data.city,
                        timestamp: response.data.timestamp,
                        submissionId: response.data.submission_id
                    };
                    
                    // Reset form after successful submission
                    setTimeout(() => {
                        resetForm();
                    }, 5000);
                } else {
                    throw new Error(response.message);
                }
                
            } catch (error) {
                console.error('Submission error:', error);
                alert('Error submitting form: ' + error.message);
            } finally {
                isSubmitting.value = false;
            }
        };

        // Reset form
        const resetForm = () => {
            selectedCountry.value = '';
            selectedState.value = '';
            selectedCity.value = '';
            states.value = [];
            cities.value = [];
            clearErrors();
            submissionResult.value = null;
        };

        // Get selected item names for display
        const getSelectedNames = () => {
            const country = countries.value.find(c => c.id == selectedCountry.value);
            const state = states.value.find(s => s.id == selectedState.value);
            const city = cities.value.find(c => c.id == selectedCity.value);
            
            return {
                country: country ? country.name : '',
                state: state ? state.name : '',
                city: city ? city.name : ''
            };
        };

        // Computed properties
        const isFormValid = computed(() => {
            return selectedCountry.value && 
                   selectedState.value && 
                   selectedCity.value &&
                   !errors.country && 
                   !errors.state && 
                   !errors.city;
        });

        const hasErrors = computed(() => {
            return errors.country || errors.state || errors.city;
        });

        const selectedNames = computed(() => {
            return getSelectedNames();
        });

        // Watch for changes to validate fields
        watch(selectedCountry, (newValue) => {
            if (newValue) {
                validateField('country', newValue);
            }
        });

        watch(selectedState, (newValue) => {
            if (newValue) {
                validateField('state', newValue);
            }
        });

        watch(selectedCity, (newValue) => {
            if (newValue) {
                validateField('city', newValue);
            }
        });

        // Initialize component
        onMounted(() => {
            loadCountries();
        });

        // Return reactive data and methods
        return {
            // Data
            countries,
            states,
            cities,
            selectedCountry,
            selectedState,
            selectedCity,
            errors,
            loading,
            isSubmitting,
            submissionResult,
            
            // Computed
            isFormValid,
            hasErrors,
            selectedNames,
            
            // Methods
            onCountryChange,
            onStateChange,
            onCityChange,
            submitForm,
            resetForm,
            validateField,
            clearErrors
        };
    }
}).mount('#app');