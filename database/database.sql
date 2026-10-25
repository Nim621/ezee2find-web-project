CREATE DATABASE IF NOT EXISTS ezee2find;
USE ezee2find;



-- 1. Admin Users Table 
DROP TABLE IF EXISTS AdminUser;
CREATE TABLE AdminUser(
     adminid INT AUTO_INCREMENT PRIMARY KEY,
     username VARCHAR(50) NOT NULL UNIQUE,
    passwordhash VARCHAR(255) NOT NULL,
    fullname VARCHAR(100) NOT NULL,
     status VARCHAR(20) NOT NULL DEFAULT 'active',
    createdat DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 2. Category Table 
DROP TABLE IF EXISTS Category ;
CREATE TABLE Category(
    categoryid INT AUTO_INCREMENT PRIMARY KEY,
    categoryname VARCHAR(80) NOT NULL UNIQUE,
    icon VARCHAR(50) DEFAULT 'bi-geo-alt',
    description TEXT
); 

-- 3. Place / Attraction Table 
DROP TABLE IF EXISTS Place;
CREATE TABLE Place (
    placeid INT AUTO-INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    categoryid INT NOT NULL,
    latitude DECIMAL(10, 7) NOT NULL,
    longitude DECIMAL(10, 7) NOT NULL,
    distancekm DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
    openinginfo VARCHAR(255) NOT NULL DEFAULT 'Open daily',
    open_hour TIME DEFAULT '08:00:00',
    close_hour TIME DEFAULT '18:00:00',
    entrancefee DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    fee_foreign DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    fee_notes VARCHAR(255) DEFAULT 'Free admission',
    visitduration INT NOT NULL DEFAULT 60 COMMENT 'duration in minutes',
    besttime VARCHAR(120) DEFAULT 'Morning or late afternoon',
    restrictions TEXT COMMENT 'Dress code, photography, footwear rules',
    accessibility VARCHAR(255) DEFAULT 'Partially wheelchair accessible',
    traveltips TEXT COMMENT 'Visitor advice, footwear, hydration',
    imageurl VARCHAR(255) DEFAULT 'assets/images/default.jpg',
    isdeleted TINYINT NOT NULL DEFAULT 0,
    updatedat DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_place_category FOREIGN KEY (categoryid) REFERENCES Category (categoryid) ON UPDATE CASCADE
);


-- 4. Place Image Table 
DROP TABLE IF EXISTS PlaceImage;
CREATE TABLE PlaceImage (
    imageid INT AUTO_INCREMENT PRIMARY KEY,
    placeid INT NOT NULL,
    path VARCHAR(255) NOT NULL,
    alttext VARCHAR(150) DEFAULT '',
    caption VARCHAR(255) DEFAULT '',
    CONSTRAINT fk_image_place FOREIGN KEY (placeid) REFERENCES Place (placeid) ON DELETE CASCADE
);

-- 5. Nearby Facilities Table 
DROP TABLE IF EXISTS Facility;
CREATE TABLE Facility (
    facilityid INT  PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    type VARCHAR(60) NOT NULL COMMENT 'Parking, Restrooms, Restaurant, Public Transport, ATM, Tourist Police',
    latitude DECIMAL(10, 7) NOT NULL,
    longitude DECIMAL(10, 7) NOT NULL,
    notes TEXT
);


-- 6. Place-Facility Relationship Table 
DROP TABLE IF EXISTS PlaceFacility;
CREATE TABLE PlaceFacility (
    id INT AUTO_INCREMENT PRIMARY KEY,
    placeid INT NOT NULL,
    facilityid INT NOT NULL,
    distance_meters INT DEFAULT 100,
    notes VARCHAR(255) DEFAULT '',
    CONSTRAINT fk_pf_place FOREIGN KEY (placeid) REFERENCES Place (placeid) ON DELETE CASCADE,
    CONSTRAINT fk_pf_facility FOREIGN KEY (facilityid) REFERENCES Facility (facilityid) ON DELETE CASCADE
); 

-- 7. Itinerary Table 
DROP TABLE IF EXISTS Itinerary;
CREATE TABLE Itinerary (
    itineraryid INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL DEFAULT 'Galle One-Day Trip',
    date DATE NOT NULL,
    starttime TIME NOT NULL DEFAULT '08:30:00',
    endtime TIME NOT NULL DEFAULT '18:30:00',
    travelmode VARCHAR(50) NOT NULL DEFAULT 'tuk-tuk' COMMENT'walking, tuk-tuk, scooter, private_car',
     pace VARCHAR(50) NOT NULL DEFAULT 'moderate' COMMENT 'relaxed, moderate, fast-paced',
    estimatedbudget DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    createdate DATETIME DEFAULT CURRENT_TIMESTAMP 
); 


-- 8. Itinerary Item Table 
DROP TABLE IF EXISTS ItineraryItem;
CREATE TABLE ItineraryItem (
    itemid INT AUTO_INCREMENT PRIMARY KEY,
    itineraryid INT NOT NULL,
    placeid INT NOT NULL,
    sequenceno INT NOT NULL,
    starttime TIME NOT NULL,
    endtime TIME NOT NULL,
    travelminutes INT NOT NULL DEFAULT 0,
    visitminutes INT NOT NULL DEFAULT 60,
    bufferminutes INT NOT NULL DEFAULT 15,
    CONSTRAINT fk_item_itinerary FOREIGN KEY (itineraryid) REFERENCES Itinerary (itineraryid) ON DELETE CASCADE,
    CONSTRAINT fk_item_place FOREIGN KEY (placeid) REFERENCES Place (placeid) ON UPDATE CASCADE
); 



INSERT INTO Category ( categoryname, icon, description) VALUES
('Heritage & Architecture', 'bi-building', 'UNESCO Fort fortifications, colonial monuments, and historical structures.'),
('Museums & Culture', 'bi-bank', 'Art, maritime treasures, archaeological collections, and folklore.'),
('Religious & Sacred', 'bi-flower1', 'Ancient Buddhist temples, historic colonial churches, and peaceful shrines.'),
('Beach & Coastal', 'bi-water', 'Golden sandy shores, coral bays, surfing spots, and ocean vistas.'),
('Nature & Wildlife', 'bi-tree', 'Green nature reserves, serene lagoons, coastal biodiversity, and bird sanctuaries.'),
('Culture & Agriculture', 'bi-cup-hot', 'Famous Ceylon tea plantations, artisan workshops, and heritage crafts.');

-- ====================================================================
-- SEED DATA: Insert Default Admin User
-- Username: admin
-- Password: Admin@123 (bcrypt hash: $2y$10$w81o9yN1zFsh4c1c9cWv0OU5l1W5z63oB1zKzQ3f2i5k7o0m9qW5K)
-- We also generate standard hash for 'admin123'
-- ====================================================================
INSERT INTO AdminUser ( username, passwordhash, fullname, status) VALUES
('admin', '$2y$10$2l31Rj9e1zD7U.x0c6w0bO08b88M1X.yDsqk3rY1a8m910.87tL7m', 'System Administrator', 'active');


INSERT INTO Place (placeid,name, description, categoryid, latitude, longitude, distancekm, openinginfo, open_hour, close_hour, entrancefee, fee_foreign, fee_notes, visitduration, besttime, restrictions, accessibility, traveltips, imageurl, isdeleted) VALUES
(1, 'Galle Fort',
 'UNESCO World Heritage-listed fortified old town founded by the Portuguese in the 16th century and extensively fortified by the Dutch. Known for charming cobblestone streets, ramparts, colonial villas, and sweeping ocean views.',
 1, 6.0270000, 80.2170000, 0.50, 
 'Open 24 hours daily (ramparts freely accessible)', '06:00:00', '21:00:00', 
 0.00, 0.00, 'Free entrance to the Fort grounds and ramparts', 
 120, 'Early morning (07:00-09:30) or Sunset (16:30-18:30)', 
 'No drones without aviation permit. Respect residential quiet zones.', 
 'Rampart paths are paved but have steps and cobblestones; main streets are wheelchair accessible.', 
 'Carry sunscreen, an umbrella, and a water bottle. Walking along Flag Rock at sunset is a highlight.', 
 'https://images.unsplash.com/photo-1588258524675-c61919d3d3a7?auto=format&fit=crop&w=800&q=80', 0),

(2, 'Galle Lighthouse', 
 'Historic 26.5-metre tall coastal lighthouse situated on Utrecht Point at the southern end of Galle Fort, originally established in 1848 and rebuilt in 1939. One of Galle''s most iconic photographic landmarks.',
 1, 6.0245000, 80.2185000, 0.90, 
 'Open daily from sunrise to sunset (Exterior viewing only)', '06:00:00', '19:00:00', 
 0.00, 0.00, 'Free exterior access; interior tower climbing is restricted to Sri Lanka Ports Authority', 
 30, 'Early morning or golden hour (16:30 - 18:00)', 
 'Interior tower access restricted. No climbing the barricades.', 
 'Accessible via the paved Fort rampart promenade with shaded palms.', 
 'Combine with a walk around the adjacent bastion. Great shady palm trees for resting.', 
 'https://images.unsplash.com/photo-1546708973-b339540b5162?auto=format&fit=crop&w=800&q=80', 0),

(3, 'Dutch Reformed Church (Groote Kerk)', 
 'Consecrated in 1755, this is one of the oldest functioning Protestant churches in Sri Lanka. It features authentic Doric gables, historic floor tombstones carved with heraldry, a magnificent organ loft, and peaceful gardens.',
 3, 6.0315000, 80.2173000, 0.40, 
 'Open Monday to Saturday 09:00 - 17:00, Sunday 10:00 - 16:00', '09:00:00', '17:00:00', 
 0.00, 0.00, 'Free admission; donations appreciated for heritage maintenance', 
 40, 'Morning (09:30 - 11:30)', 
 'Modest clothing recommended. Silence during prayer services.No flash photography inside.', 
 'Step-free entrance ramp available into the main nave.', 
 'Examine the detailed carved tombstones on the stone floor which record colonial VOC history.', 
 'https://images.unsplash.com/photo-1586861635167-e5223aadc9fe?auto=format&fit=crop&w=800&q=80', 0),

(4, 'Galle National Museum', 
 'Housed within the Fort''s oldest surviving Dutch colonial building (constructed in 1656), this museum showcases southern archaeological relics, traditional mask carvings, Dutch VOC weapons, and traditional lace-making artifacts.',
 2, 6.0322000, 80.2168000, 0.50, 
 'Open Tuesday to Saturday 09:00 - 17:00 (Closed Sundays, Mondays & Public Holidays)', '09:00:00', '17:00:00', 
 100.00, 1200.00, 'Local: 100 LKR, Foreign Adult: 1200 LKR (Dept of National Museums)', 
 60, '10:00 - 13:00 (Great during midday heat)', 
 'Photography requires a small additional fee ticket. No large backpacks.', 
 'Ground floor exhibits accessible; upper level requires staircase.', 
 'Excellent indoor educational stop to learn about Galle''s pre-colonial and colonial maritime trade.', 
 'https://images.unsplash.com/photo-1566127444979-b3d2b654e3d7?auto=format&fit=crop&w=800&q=80', 0),

(5, 'Galle National Maritime Museum', 
 'Situated inside the imposing Dutch warehouse (Kopman Stores) above the Old Gate of Galle Fort. Displays underwater archaeology artifacts, recovered shipwrecks, traditional fishing craft, and coral reef ecosystems.',
 2, 6.0335000, 80.2180000, 0.70, 
 'Open Tuesday to Saturday 09:00 - 16:30', '09:00:00', '16:30:00', 
 150.00, 1500.00, 'Local: 150 LKR, Foreign Adult: 1500 LKR', 
 60, '11:00 - 14:00 (Air-conditioned indoor respite)', 
 'Flash photography not permitted in special exhibit galleries.', 
 'Ground floor fully wheelchair accessible with gentle ramps.', 
 'Look for the reconstructed 17th-century Avondster ship model and maritime navigation tools.', 
 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?auto=format&fit=crop&w=800&q=80', 0),

(6, 'Unawatuna Beach', 
 'A world-famous picturesque crescent bay lined with golden sands and calm turquoise waters protected by a shallow reef. Renowned for swimming, coastal dining, stand-up paddleboarding, and a lively seaside atmosphere.',
 4, 6.0108000, 80.2488000, 5.00, 
 'Open 24 hours daily (beach access)', '06:00:00', '22:00:00', 
 0.00, 0.00, 'Public beach access is free. Sunbeds/umbrellas available to rent.', 
 90, 'Morning (08:00 - 11:00) or Sunset dining (16:30 - 19:00)', 
 'Observe beach lifeguard flag safety warnings. No glass bottles on the sand.', 
 'Main road access points have gentle paved walkways leading to the sand.', 
 'Calmer waters for swimming compared to other southern surf breaks. Great fresh fruit juices.', 
 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80', 0),

(7, 'Rumassala / Jungle Beach', 
 'A secluded twin-cove paradise framed by the lush jungle headland of Rumassala Sanctuary. Featured in the Ramayana legend as a fallen piece of the Himalayas, this tranquil cove offers clear waters ideal for snorkeling.',
 5, 6.0182000, 80.2395000, 6.50, 
 'Open daily from sunrise to sunset (06:00 - 18:00)', '06:00:00', '18:00:00', 
 0.00, 0.00, 'Free access (small tip/fee for vehicle parking at upper point)', 
 90, 'Early morning (07:30 - 10:30) for tranquil snorkeling', 
 'Take all trash back with you (eco-protected wildlife zone). Beware of slippery rocks.', 
 'Requires descending a winding dirt jungle trail; not suitable for standard wheelchairs.', 
 'Wear sturdy footwear for the 10-minute downhill walk. Bring snorkel gear to see reef fish.', 
 'https://images.unsplash.com/photo-1510414842594-a61782153f58?auto=format&fit=crop&w=800&q=80', 0),

(8, 'Japanese Peace Pagoda, Rumassala', 
 'An eye-catching snow-white Buddhist stupa gifted by Japanese Buddhist monk Nichidatsu Fujii in 2004. Perched high on Rumassala hill, it offers 360-degree panoramic views of Galle Fort, the Indian Ocean, and lush coastal jungle.',
 3, 6.0165000, 80.2405000, 5.80, 
 'Open daily 06:00 - 18:30 (Quiet meditation hours respected)', '06:00:00', '18:30:00', 
 0.00, 0.00, 'Free admission; donations welcome', 
 45, 'Late afternoon (16:30 - 18:00) for breathtaking sunset views over Galle Fort', 
 'Strict dress code: Shoulders and knees must be covered. Footwear and hats must be removed before stepping on the upper terrace.', 
 'Paved road directly to upper car park; moderate stone steps to stupa circumnavigation walkway.', 
 'The stone terrace can get very hot during midday sun, so wear socks or visit early/late.', 
 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=800&q=80', 0),

(9, 'Yatagala Raja Maha Viharaya', 
 'An extraordinary 2,300-year-old ancient rock cave temple built during the reign of King Devanampiyatissa. Huge granite boulders protect an impressive 9-metre reclining Buddha statue and vibrant ancient wall murals.',
 3, 6.0195000, 80.2780000, 9.00, 
 'Open daily 06:30 - 18:00', '06:30:00', '18:00:00', 
 0.00, 0.00, 'Free entry; voluntary donations for temple upkeep', 
 60, 'Morning (08:00 - 10:30)', 
 'Modest Buddhist temple attire mandatory (white or light modest clothing, knees and shoulders covered). Remove shoes and headwear.', 
 'Paved flight of stone stairs leads to the rock shrine chamber.', 
 'Marvel at the ancient Bo tree believed to be a sapling of Sri Maha Bodhi in Anuradhapura.', 
 'https://images.unsplash.com/photo-1563245372-f21724e3856d?auto=format&fit=crop&w=800&q=80', 0),

(10, 'Koggala Lake', 
 'A picturesque coastal lagoon spanning over 7 square kilometers dotted with eight scenic mangrove islands. Famous for cinnamon peeling demonstrations, the secluded Temple Island, birdwatching, and scenic boat safaris.',
 5, 5.9985000, 80.3320000, 15.00, 
 'Boat excursions run daily 07:00 - 17:30', '07:00:00', '17:30:00', 
 0.00, 0.00, 'Free lake viewing. 1.5-hr private motorboat safaris cost approx 3,500 - 5,000 LKR per boat.', 
 90, 'Early morning (07:30 - 09:30) for active wildlife and cool breeze', 
 'Life jackets mandatory during boat trips. Do not disturb nesting water birds.', 
 'Main boat jetties are accessible by vehicle with assistance onto boats.', 
 'Visit Cinnamon Island to witness traditional artisans harvest and shave fresh cinnamon bark.', 
 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=800&q=80', 0),

(11, 'Martin Wickramasinghe Folk Museum Complex', 
 'The ancestral home and 7-acre museum estate of celebrated Sri Lankan literary pioneer Martin Wickramasinghe. Features rural agricultural machinery, bullock carts, folk arts, vintage vehicles, and lush botanical gardens.',
 2, 5.9930000, 80.3440000, 17.50, 
 'Open daily 09:00 - 17:00 (Last entry 16:30)', '09:00:00', '17:00:00', 
 200.00, 1000.00, 'Local Adult: 200 LKR, Child: 50 LKR; Foreign Adult: 1000 LKR', 
 75, 'Morning or early afternoon (10:00 - 14:00)', 
 'Do not touch historical artifacts or folk instruments on display.', 
 'Level garden paths and ground-floor exhibition halls have good accessibility.', 
 'A must-visit for cultural enthusiasts seeking deep insight into southern Sri Lanka''s rural heritage.', 
 'https://images.unsplash.com/photo-1582560475093-ba66accbc424?auto=format&fit=crop&w=800&q=80', 0),

(12, 'Koggala Beach & Stilt Fishermen', 
 'An expansive golden coastline famed worldwide for the time-honored southern Sri Lankan tradition of stilt fishing (Ritipanna), where fishermen balance on slender poles driven into the sea bed to catch spotted herrings.',
 4, 5.9910000, 80.3290000, 16.50, 
 'Accessible 24 hours daily', '06:00:00', '19:00:00', 
 0.00, 0.00, 'Free beach access. Stilt fishermen usually request a 500-1,000 LKR tip for staged photography.', 
 45, 'Morning (06:30 - 08:30) or late afternoon sunset (16:30 - 18:00)', 
 'Strong ocean undercurrents in certain spots; swimming should only be done in designated areas.', 
 'Flat beach access points along the Matara-Galle coastal highway.', 
 'Bring camera with a telephoto zoom. Sunset silhouettes of the fishermen on stilts are magical.', 
 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80', 0),

(13, 'Hikkaduwa Beach & Coral Reef Sanctuary', 
 'Renowned marine sanctuary situated 18 km north of Galle. Features shallow vibrant coral gardens, friendly wild green sea turtles that swim up to the shore, excellent reef surf breaks, and beachside cafes.',
 4, 6.1395000, 80.1030000, 17.50, 
 'Open 24 hours daily (National Park office 08:00 - 17:00)', '06:00:00', '21:00:00', 
 0.00, 0.00, 'Free shore access. Glass bottom boat tours to coral reef ~2,500 LKR per boat.', 
 120, 'Morning (08:30 - 11:30) when waters are calm and clear', 
 'Strictly prohibited to touch, feed, or stand on live corals or chase wild sea turtles.', 
 'Beachfront resorts and walkways offer accessible entry to the sandy shore.', 
 'Go to the reef shallows in front of the coral garden to spot friendly sea turtles in waist-deep water.', 
 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?auto=format&fit=crop&w=800&q=80', 0),

(14, 'Kataluwa Purvarama Maha Vihara', 
 'Founded in the 13th century and renovated in the 19th century, this historic temple contains world-celebrated Kandyan-style wall paintings and rare murals depicting British Queen Victoria in homage to colonial history.',
 3, 5.9860000, 80.3620000, 19.00, 
 'Open daily 08:00 - 17:00', '08:00:00', '17:00:00', 
 0.00, 500.00, 'Suggested donation/entry: 500 LKR for temple preservation', 
 45, 'Morning (09:00 - 12:00)', 
 'Temple dress code applies (cover shoulders/knees, remove shoes and hat). No flash on delicate 1880s wall paintings.', 
 'Ground level pathways with minor threshold steps into the image shrine.', 
 'Ask the resident monk or temple caretaker to point out the Queen Victoria and vintage steam train murals.', 
 'https://images.unsplash.com/photo-1563245372-f21724e3856d?auto=format&fit=crop&w=800&q=80', 0),

(15, 'Handunugoda Tea Estate (Virgin White Tea)', 
 'A legendary 200-acre coastal estate that produces the world-renowned "Virgin White Tea," harvested without being touched by human hands following ancient Chinese imperial rituals. Offers guided factory tours and 20+ tea tastings.',
 6, 6.0110000, 80.3540000, 21.50, 
 'Open daily 08:00 - 16:30', '08:00:00', '16:30:00', 
 0.00, 0.00, 'Free guided plantation and factory tour with complimentary tea tasting salon', 
 75, 'Morning (09:00 - 11:30) or early afternoon', 
 'Respect working agricultural machinery and factory hygienic zones.', 
 'Tea salon and main processing pavilion are fully accessible; hillside fields have plantation footpaths.', 
 'Sample the famous Virgin White Tea, Sapphire Oolong, and specialty cinnamon tea at the salon.', 
 'https://images.unsplash.com/photo-1576092768241-dec231879fc3?auto=format&fit=crop&w=800&q=80', 0);


INSERT INTO Facility (facilityid, name, type, latitude, longitude, notes) VALUES
(1, 'Galle Central Railway & Bus Terminal', 'Public Transport', 6.0355000, 80.2140000, 'Central transport hub connecting Colombo, Matara, and local tuk-tuk stands.'),
(2, 'Galle Fort Rampart Car Park', 'Parking', 6.0330000, 80.2190000, 'Spacious paid vehicle parking directly outside the Old Gate of Galle Fort.'),
(3, 'Fort Public Restrooms & Info Point', 'Restrooms', 6.0310000, 80.2160000, 'Clean public municipal restrooms and tourist information counter.'),
(4, 'Galle Tourist Police Station', 'Tourist Police', 6.0320000, 80.2155000, 'Dedicated 24/7 tourist safety assistance, emergency lost item reporting.'),
(5, 'Pedlar Street Heritage Cafes & Dining', 'Restaurant', 6.0280000, 80.2175000, 'Concentration of bakeries, Sri Lankan rice & curry cafes, gelato shops.'),
(6, 'Unawatuna Beach Central Car Park', 'Parking', 6.0120000, 80.2470000, 'Main vehicle and three-wheeler parking with beach path access.'),
(7, 'Rumassala Nature Lookout Rest Stop', 'Restrooms', 6.0170000, 80.2400000, 'Rest stop with ocean viewpoint, drinking coconut stalls, and restrooms.'),
(8, 'Koggala Lake Boat Safari Jetty', 'Public Transport', 5.9990000, 80.3310000, 'Authorized boat safari departure station with registered safety life jackets.');

INSERT INTO PlaceFacility (placeid, facilityid, distance_meters, notes) VALUES
(1, 2, 200, 'Parking right at the Dutch gate entrance to Galle Fort'),
(1, 3, 150, 'Municipal public restrooms on Church Street'),
(1, 4, 100, 'Tourist police station next to main entrance gate'),
(1, 5, 50, 'Famous restaurants and artisan cafes line Pedlar Street'),
(2, 5, 250, 'Short 3-minute stroll to closest cafes on Lighthouse Street'),
(3, 3, 100, 'Restrooms located within walking distance'),
(4, 3, 120, 'Directly accessible via pedestrian street'),
(5, 2, 80, 'Opposite the old gate parking pavilion'),
(6, 6, 150, 'Central public parking area near the main beach road'),
(7, 7, 200, 'Rest area with scenic lookout near trail entrance'),
(8, 7, 300, 'Parking and rest area at the base of the Peace Pagoda hill'),
(10, 8, 50, 'Safari boats depart from the lake jetty station');
