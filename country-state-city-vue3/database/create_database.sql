-- Create Database
CREATE DATABASE IF NOT EXISTS country_state_city;
USE country_state_city;

-- Create Countries Table
CREATE TABLE IF NOT EXISTS countries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create States Table
CREATE TABLE IF NOT EXISTS states (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    country_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE CASCADE
);

-- Create Cities Table
CREATE TABLE IF NOT EXISTS cities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    state_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (state_id) REFERENCES states(id) ON DELETE CASCADE
);

-- Insert Countries Data
INSERT INTO countries (name, code) VALUES
('India', 'IN'),
('United States', 'US'),
('United Kingdom', 'UK'),
('Canada', 'CA'),
('Australia', 'AU');

-- Insert States Data
INSERT INTO states (name, country_id) VALUES
-- India States
('Gujarat', 1),
('Maharashtra', 1),
('Karnataka', 1),
('Tamil Nadu', 1),
('Rajasthan', 1),
('Punjab', 1),
('West Bengal', 1),
('Uttar Pradesh', 1),

-- United States States
('California', 2),
('New York', 2),
('Texas', 2),
('Florida', 2),
('Illinois', 2),

-- United Kingdom States
('England', 3),
('Scotland', 3),
('Wales', 3),
('Northern Ireland', 3),

-- Canada States
('Ontario', 4),
('Quebec', 4),
('British Columbia', 4),
('Alberta', 4),

-- Australia States
('New South Wales', 5),
('Victoria', 5),
('Queensland', 5),
('Western Australia', 5);

-- Insert Cities Data
INSERT INTO cities (name, state_id) VALUES
-- Gujarat Cities (state_id = 1)
('Ahmedabad', 1),
('Surat', 1),
('Vadodara (Baroda)', 1),
('Rajkot', 1),
('Bhavnagar', 1),
('Jamnagar', 1),
('Gandhinagar', 1),
('Anand', 1),

-- Maharashtra Cities (state_id = 2)
('Mumbai', 2),
('Pune', 2),
('Nagpur', 2),
('Nashik', 2),
('Aurangabad', 2),
('Solapur', 2),
('Thane', 2),

-- Karnataka Cities (state_id = 3)
('Bangalore', 3),
('Mysore', 3),
('Hubli', 3),
('Mangalore', 3),
('Belgaum', 3),
('Gulbarga', 3),

-- Tamil Nadu Cities (state_id = 4)
('Chennai', 4),
('Coimbatore', 4),
('Madurai', 4),
('Tiruchirappalli', 4),
('Salem', 4),
('Tirunelveli', 4),

-- Rajasthan Cities (state_id = 5)
('Jaipur', 5),
('Jodhpur', 5),
('Udaipur', 5),
('Kota', 5),
('Bikaner', 5),
('Ajmer', 5),

-- Punjab Cities (state_id = 6)
('Chandigarh', 6),
('Ludhiana', 6),
('Amritsar', 6),
('Jalandhar', 6),
('Patiala', 6),
('Bathinda', 6),

-- West Bengal Cities (state_id = 7)
('Kolkata', 7),
('Howrah', 7),
('Durgapur', 7),
('Asansol', 7),
('Siliguri', 7),
('Malda', 7),

-- Uttar Pradesh Cities (state_id = 8)
('Lucknow', 8),
('Kanpur', 8),
('Agra', 8),
('Varanasi', 8),
('Meerut', 8),
('Allahabad', 8),

-- California Cities (state_id = 9)
('Los Angeles', 9),
('San Francisco', 9),
('San Diego', 9),
('Sacramento', 9),
('San Jose', 9),
('Fresno', 9),

-- New York Cities (state_id = 10)
('New York City', 10),
('Buffalo', 10),
('Rochester', 10),
('Syracuse', 10),
('Albany', 10),
('Yonkers', 10),

-- Texas Cities (state_id = 11)
('Houston', 11),
('Dallas', 11),
('Austin', 11),
('San Antonio', 11),
('Fort Worth', 11),
('El Paso', 11),

-- Florida Cities (state_id = 12)
('Miami', 12),
('Orlando', 12),
('Tampa', 12),
('Jacksonville', 12),
('Fort Lauderdale', 12),
('Tallahassee', 12),

-- Illinois Cities (state_id = 13)
('Chicago', 13),
('Aurora', 13),
('Peoria', 13),
('Rockford', 13),
('Elgin', 13),
('Joliet', 13),

-- England Cities (state_id = 14)
('London', 14),
('Manchester', 14),
('Birmingham', 14),
('Liverpool', 14),
('Leeds', 14),
('Sheffield', 14),

-- Scotland Cities (state_id = 15)
('Edinburgh', 15),
('Glasgow', 15),
('Aberdeen', 15),
('Dundee', 15),
('Stirling', 15),
('Perth', 15),

-- Wales Cities (state_id = 16)
('Cardiff', 16),
('Swansea', 16),
('Newport', 16),
('Wrexham', 16),
('Barry', 16),

-- Northern Ireland Cities (state_id = 17)
('Belfast', 17),
('Derry', 17),
('Lisburn', 17),
('Newry', 17),

-- Ontario Cities (state_id = 18)
('Toronto', 18),
('Ottawa', 18),
('Hamilton', 18),
('London', 18),
('Windsor', 18),
('Kitchener', 18),

-- Quebec Cities (state_id = 19)
('Montreal', 19),
('Quebec City', 19),
('Laval', 19),
('Gatineau', 19),
('Longueuil', 19),

-- British Columbia Cities (state_id = 20)
('Vancouver', 20),
('Victoria', 20),
('Surrey', 20),
('Burnaby', 20),
('Richmond', 20),

-- Alberta Cities (state_id = 21)
('Calgary', 21),
('Edmonton', 21),
('Red Deer', 21),
('Lethbridge', 21),
('Medicine Hat', 21),

-- New South Wales Cities (state_id = 22)
('Sydney', 22),
('Newcastle', 22),
('Wollongong', 22),
('Central Coast', 22),
('Maitland', 22),

-- Victoria Cities (state_id = 23)
('Melbourne', 23),
('Geelong', 23),
('Ballarat', 23),
('Bendigo', 23),
('Shepparton', 23),

-- Queensland Cities (state_id = 24)
('Brisbane', 24),
('Gold Coast', 24),
('Cairns', 24),
('Townsville', 24),
('Toowoomba', 24),

-- Western Australia Cities (state_id = 25)
('Perth', 25),
('Fremantle', 25),
('Bunbury', 25),
('Geraldton', 25),
('Kalgoorlie', 25);

-- Create Form Submissions Table (Optional - for storing form data)
CREATE TABLE IF NOT EXISTS form_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    country_id INT NOT NULL,
    state_id INT NOT NULL,
    city_id INT NOT NULL,
    country_name VARCHAR(100),
    state_name VARCHAR(100),
    city_name VARCHAR(100),
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (country_id) REFERENCES countries(id),
    FOREIGN KEY (state_id) REFERENCES states(id),
    FOREIGN KEY (city_id) REFERENCES cities(id)
);