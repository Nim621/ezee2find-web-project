<?php
/**
 * EZEE2FIND - Database Connection Module
 */

// Database Configuration Settings
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3307');
define('DB_NAME', 'ezee2find');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

class Database {
    private static ?PDO $instance = null;
    private static string $driverUsed = 'mysql';

    public static function getConnection(): PDO {
        if (self::$instance !== null) {
            return self::$instance;
        }

        // 1. Try MySQL Connection First
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_TIMEOUT            => 2, // Quick 2-second timeout so it falls back promptly if MySQL isn't running
            ];
            self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            self::$driverUsed = 'mysql';
            return self::$instance;
        } catch (PDOException $e) {
            // MySQL unavailable 
            self::$driverUsed = 'sqlite';
            return self::initSqliteFallback();
        }
    }

    public static function getDriver(): string {
        return self::$driverUsed;
    }

    private static function initSqliteFallback(): PDO {
        $dbDir = __DIR__ . '/../database';
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0777, true);
        }
        $sqliteFile = $dbDir . '/ezee2find.db';
        $needsSeeding = !file_exists($sqliteFile) || filesize($sqliteFile) < 1000;

        $pdo = new PDO('sqlite:' . $sqliteFile);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        if ($needsSeeding) {
            self::seedSqlite($pdo);
        }

        self::$instance = $pdo;
        return $pdo;
    }

    private static function seedSqlite(PDO $pdo): void {
        $schema = "
        CREATE TABLE IF NOT EXISTS AdminUser (
            adminid INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            passwordhash TEXT NOT NULL,
            fullname TEXT NOT NULL,
            status TEXT DEFAULT 'active',
            createdat DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS Category (
            categoryid INTEGER PRIMARY KEY AUTOINCREMENT,
            categoryname TEXT UNIQUE NOT NULL,
            icon TEXT DEFAULT 'bi-geo-alt',
            description TEXT
        );

        CREATE TABLE IF NOT EXISTS Place (
            placeid INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            description TEXT NOT NULL,
            categoryid INTEGER NOT NULL,
            latitude REAL NOT NULL,
            longitude REAL NOT NULL,
            distancekm REAL DEFAULT 0.0,
            openinginfo TEXT DEFAULT 'Open daily',
            open_hour TEXT DEFAULT '08:00',
            close_hour TEXT DEFAULT '18:00',
            entrancefee REAL DEFAULT 0.0,
            fee_foreign REAL DEFAULT 0.0,
            fee_notes TEXT DEFAULT 'Free admission',
            visitduration INTEGER DEFAULT 60,
            besttime TEXT DEFAULT 'Morning or late afternoon',
            restrictions TEXT,
            accessibility TEXT DEFAULT 'Partially accessible',
            traveltips TEXT,
            imageurl TEXT,
            isdeleted INTEGER DEFAULT 0,
            updatedat DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS Facility (
            facilityid INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            type TEXT NOT NULL,
            latitude REAL NOT NULL,
            longitude REAL NOT NULL,
            notes TEXT
        );

        CREATE TABLE IF NOT EXISTS PlaceFacility (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            placeid INTEGER NOT NULL,
            facilityid INTEGER NOT NULL,
            distance_meters INTEGER DEFAULT 100,
            notes TEXT
        );

        CREATE TABLE IF NOT EXISTS Itinerary (
            itineraryid INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT DEFAULT 'Galle One-Day Trip',
            date TEXT NOT NULL,
            starttime TEXT DEFAULT '08:30',
            endtime TEXT DEFAULT '18:30',
            travelmode TEXT DEFAULT 'tuk-tuk',
            pace TEXT DEFAULT 'moderate',
            estimatedbudget REAL DEFAULT 0.0,
            createdat DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS ItineraryItem (
            itemid INTEGER PRIMARY KEY AUTOINCREMENT,
            itineraryid INTEGER NOT NULL,
            placeid INTEGER NOT NULL,
            sequenceno INTEGER NOT NULL,
            starttime TEXT NOT NULL,
            endtime TEXT NOT NULL,
            travelminutes INTEGER DEFAULT 0,
            visitminutes INTEGER DEFAULT 60,
            bufferminutes INTEGER DEFAULT 15
        );
        ";
        $pdo->exec($schema);

        // Seed Admin (password: admin123)
        $hash = password_hash('admin123', PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO AdminUser (adminid, username, passwordhash, fullname, status) VALUES (1, 'admin', ?, 'System Administrator', 'active')");
        $stmt->execute([$hash]);

        // Seed Categories
        $categories = [
            [1, 'Heritage & Architecture', 'bi-building', 'UNESCO Fort fortifications, colonial monuments, and historical structures.'],
            [2, 'Museums & Culture', 'bi-bank', 'Art, maritime treasures, archaeological collections, and folklore.'],
            [3, 'Religious & Sacred', 'bi-flower1', 'Ancient Buddhist temples, historic colonial churches, and peaceful shrines.'],
            [4, 'Beach & Coastal', 'bi-water', 'Golden sandy shores, coral bays, surfing spots, and ocean vistas.'],
            [5, 'Nature & Wildlife', 'bi-tree', 'Green nature reserves, serene lagoons, coastal biodiversity, and bird sanctuaries.'],
            [6, 'Culture & Agriculture', 'bi-cup-hot', 'Famous Ceylon tea plantations, artisan workshops, and heritage crafts.']
        ];
        $catStmt = $pdo->prepare("INSERT OR IGNORE INTO Category (categoryid, categoryname, icon, description) VALUES (?, ?, ?, ?)");
        foreach ($categories as $cat) {
            $catStmt->execute($cat);
        }

        // Seed Places (15 attractions)
        $places = [
            [1, 'Galle Fort', 'UNESCO World Heritage-listed fortified old town founded by the Portuguese in the 16th century and extensively fortified by the Dutch. Known for charming cobblestone streets, ramparts, colonial villas, and sweeping ocean views.', 1, 6.0270, 80.2170, 0.50, 'Open 24 hours daily (ramparts freely accessible)', '06:00', '21:00', 0.0, 0.0, 'Free entrance to Fort grounds and ramparts', 120, 'Early morning (07:00-09:30) or Sunset (16:30-18:30)', 'No drones without aviation permit. Respect residential quiet zones.', 'Ramparts paved with stone paths; Fort streets are wheelchair accessible.', 'Carry sunscreen, an umbrella, and water. Walking along Flag Rock at sunset is a highlight.', 'https://images.unsplash.com/photo-1588258524675-c61919d3d3a7?auto=format&fit=crop&w=800&q=80'],
            [2, 'Galle Lighthouse', 'Historic 26.5-metre tall coastal lighthouse situated on Utrecht Point at the southern end of Galle Fort, originally established in 1848 and rebuilt in 1939. One of Galle\'s most iconic photographic landmarks.', 1, 6.0245, 80.2185, 0.90, 'Open daily sunrise to sunset (exterior viewing)', '06:00', '19:00', 0.0, 0.0, 'Free exterior access; interior tower managed by Ports Authority', 30, 'Early morning or golden hour (16:30 - 18:00)', 'Interior tower access restricted. Do not climb barricades.', 'Accessible via the paved Fort rampart promenade with shaded palms.', 'Combine with a walk around the adjacent bastion. Great shady palm trees for resting.', 'https://images.unsplash.com/photo-1546708973-b339540b5162?auto=format&fit=crop&w=800&q=80'],
            [3, 'Dutch Reformed Church (Groote Kerk)', 'Consecrated in 1755, this is one of the oldest functioning Protestant churches in Sri Lanka. It features authentic Doric gables, historic floor tombstones carved with heraldry, a magnificent organ loft, and peaceful gardens.', 3, 6.0315, 80.2173, 0.40, 'Open Mon-Sat 09:00 - 17:00, Sun 10:00 - 16:00', '09:00', '17:00', 0.0, 0.0, 'Free admission; donations appreciated for maintenance', 40, 'Morning (09:30 - 11:30)', 'Modest clothing recommended. Silence during prayer services. No flash photography inside.', 'Step-free entrance ramp available into the main nave.', 'Examine the detailed carved tombstones on the stone floor which record colonial VOC history.', 'https://images.unsplash.com/photo-1586861635167-e5223aadc9fe?auto=format&fit=crop&w=800&q=80'],
            [4, 'Galle National Museum', 'Housed within the Fort\'s oldest surviving Dutch colonial building (constructed in 1656), this museum showcases southern archaeological relics, traditional mask carvings, Dutch VOC weapons, and traditional lace-making artifacts.', 2, 6.0322, 80.2168, 0.50, 'Open Tue-Sat 09:00 - 17:00 (Closed Sun & Mon)', '09:00', '17:00', 100.0, 1200.0, 'Local: 100 LKR, Foreign Adult: 1200 LKR (Dept of National Museums)', 60, '10:00 - 13:00 (Great during midday heat)', 'Photography requires a small additional fee ticket. No large backpacks.', 'Ground floor exhibits accessible; upper level requires staircase.', 'Excellent indoor educational stop to learn about Galle\'s pre-colonial and colonial maritime trade.', 'https://images.unsplash.com/photo-1566127444979-b3d2b654e3d7?auto=format&fit=crop&w=800&q=80'],
            [5, 'Galle National Maritime Museum', 'Situated inside the imposing Dutch warehouse (Kopman Stores) above the Old Gate of Galle Fort. Displays underwater archaeology artifacts, recovered shipwrecks, traditional fishing craft, and coral reef ecosystems.', 2, 6.0335, 80.2180, 0.70, 'Open Tue-Sat 09:00 - 16:30', '09:00', '16:30', 150.0, 1500.0, 'Local: 150 LKR, Foreign Adult: 1500 LKR', 60, '11:00 - 14:00 (Air-conditioned indoor respite)', 'Flash photography not permitted in special exhibit galleries.', 'Ground floor fully wheelchair accessible with gentle ramps.', 'Look for the reconstructed 17th-century Avondster ship model and maritime navigation tools.', 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?auto=format&fit=crop&w=800&q=80'],
            [6, 'Unawatuna Beach', 'A world-famous picturesque crescent bay lined with golden sands and calm turquoise waters protected by a shallow reef. Renowned for swimming, coastal dining, stand-up paddleboarding, and a lively seaside atmosphere.', 4, 6.0108, 80.2488, 5.00, 'Open 24 hours daily (beach access)', '06:00', '22:00', 0.0, 0.0, 'Public beach access is free. Sunbeds/umbrellas available to rent.', 90, 'Morning (08:00 - 11:00) or Sunset dining (16:30 - 19:00)', 'Observe beach lifeguard flag safety warnings. No glass bottles on sand.', 'Main road access points have gentle paved walkways leading to the sand.', 'Calmer waters for swimming compared to other southern surf breaks. Great fresh fruit juices.', 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80'],
            [7, 'Rumassala / Jungle Beach', 'A secluded twin-cove paradise framed by the lush jungle headland of Rumassala Sanctuary. Featured in the Ramayana legend as a fallen piece of the Himalayas, this tranquil cove offers clear waters ideal for snorkeling.', 5, 6.0182, 80.2395, 6.50, 'Open daily sunrise to sunset (06:00 - 18:00)', '06:00', '18:00', 0.0, 0.0, 'Free access (small vehicle parking fee at upper point)', 90, 'Early morning (07:30 - 10:30) for tranquil snorkeling', 'Take all trash back with you (eco-protected wildlife zone). Beware of slippery rocks.', 'Requires descending a winding dirt jungle trail; not suitable for standard wheelchairs.', 'Wear sturdy footwear for the 10-minute downhill walk. Bring snorkel gear to see reef fish.', 'https://images.unsplash.com/photo-1510414842594-a61782153f58?auto=format&fit=crop&w=800&q=80'],
            [8, 'Japanese Peace Pagoda, Rumassala', 'An eye-catching snow-white Buddhist stupa gifted by Japanese Buddhist monk Nichidatsu Fujii in 2004. Perched high on Rumassala hill, it offers 360-degree panoramic views of Galle Fort, the Indian Ocean, and lush coastal jungle.', 3, 6.0165, 80.2405, 5.80, 'Open daily 06:00 - 18:30', '06:00', '18:30', 0.0, 0.0, 'Free admission; donations welcome', 45, 'Late afternoon (16:30 - 18:00) for breathtaking sunset views over Galle Fort', 'Strict dress code: Shoulders and knees must be covered. Footwear and hats removed before stepping on upper terrace.', 'Paved road directly to upper car park; moderate stone steps to stupa walkway.', 'The stone terrace can get very hot during midday sun, so wear socks or visit early/late.', 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=800&q=80'],
            [9, 'Yatagala Raja Maha Viharaya', 'An extraordinary 2,300-year-old ancient rock cave temple built during the reign of King Devanampiyatissa. Huge granite boulders protect an impressive 9-metre reclining Buddha statue and vibrant ancient wall murals.', 3, 6.0195, 80.2780, 9.00, 'Open daily 06:30 - 18:00', '06:30', '18:00', 0.0, 0.0, 'Free entry; voluntary donations for temple upkeep', 60, 'Morning (08:00 - 10:30)', 'Modest Buddhist temple attire mandatory (white or light modest clothing, knees and shoulders covered). Remove shoes and headwear.', 'Paved flight of stone stairs leads to the rock shrine chamber.', 'Marvel at the ancient Bo tree believed to be a sapling of Sri Maha Bodhi in Anuradhapura.', 'https://images.unsplash.com/photo-1563245372-f21724e3856d?auto=format&fit=crop&w=800&q=80'],
            [10, 'Koggala Lake', 'A picturesque coastal lagoon spanning over 7 square kilometers dotted with eight scenic mangrove islands. Famous for cinnamon peeling demonstrations, the secluded Temple Island, birdwatching, and scenic boat safaris.', 5, 5.9985, 80.3320, 15.00, 'Boat excursions run daily 07:00 - 17:30', '07:00', '17:30', 0.0, 0.0, 'Free lake viewing. 1.5-hr private motorboat safaris cost approx 3,500 - 5,000 LKR per boat.', 90, 'Early morning (07:30 - 09:30) for active wildlife and cool breeze', 'Life jackets mandatory during boat trips. Do not disturb nesting water birds.', 'Main boat jetties are accessible by vehicle with assistance onto boats.', 'Visit Cinnamon Island to witness traditional artisans harvest and shave fresh cinnamon bark.', 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=800&q=80'],
            [11, 'Martin Wickramasinghe Folk Museum Complex', 'The ancestral home and 7-acre museum estate of celebrated Sri Lankan literary pioneer Martin Wickramasinghe. Features rural agricultural machinery, bullock carts, folk arts, vintage vehicles, and lush botanical gardens.', 2, 5.9930, 80.3440, 17.50, 'Open daily 09:00 - 17:00 (Last entry 16:30)', '09:00', '17:00', 200.0, 1000.0, 'Local Adult: 200 LKR, Child: 50 LKR; Foreign Adult: 1000 LKR', 75, 'Morning or early afternoon (10:00 - 14:00)', 'Do not touch historical artifacts or folk instruments on display.', 'Level garden paths and ground-floor exhibition halls have good accessibility.', 'A must-visit for cultural enthusiasts seeking deep insight into southern Sri Lanka\'s rural heritage.', 'https://images.unsplash.com/photo-1582560475093-ba66accbc424?auto=format&fit=crop&w=800&q=80'],
            [12, 'Koggala Beach & Stilt Fishermen', 'An expansive golden coastline famed worldwide for the time-honored southern Sri Lankan tradition of stilt fishing (Ritipanna), where fishermen balance on slender poles driven into the sea bed to catch spotted herrings.', 4, 5.9910, 80.3290, 16.50, 'Accessible 24 hours daily', '06:00', '19:00', 0.0, 0.0, 'Free beach access. Stilt fishermen usually request a 500-1,000 LKR tip for staged photography.', 45, 'Morning (06:30 - 08:30) or late afternoon sunset (16:30 - 18:00)', 'Strong ocean undercurrents in certain spots; swimming only in designated zones.', 'Flat beach access points along the Matara-Galle coastal highway.', 'Bring camera with a telephoto zoom. Sunset silhouettes of fishermen on stilts are magical.', 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80'],
            [13, 'Hikkaduwa Beach & Coral Reef Sanctuary', 'Renowned marine sanctuary situated 18 km north of Galle. Features shallow vibrant coral gardens, friendly wild green sea turtles that swim up to the shore, excellent reef surf breaks, and beachside cafes.', 4, 6.1395, 80.1030, 17.50, 'Open 24 hours daily (National Park office 08:00 - 17:00)', '06:00', '21:00', 0.0, 0.0, 'Free shore access. Glass bottom boat tours to coral reef ~2,500 LKR per boat.', 120, 'Morning (08:30 - 11:30) when waters are calm and clear', 'Strictly prohibited to touch, feed, or stand on live corals or chase wild sea turtles.', 'Beachfront resorts and walkways offer accessible entry to the sandy shore.', 'Go to the reef shallows in front of the coral garden to spot friendly sea turtles in waist-deep water.', 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?auto=format&fit=crop&w=800&q=80'],
            [14, 'Kataluwa Purvarama Maha Vihara', 'Founded in the 13th century and renovated in the 19th century, this historic temple contains world-celebrated Kandyan-style wall paintings and rare murals depicting British Queen Victoria in homage to colonial history.', 3, 5.9860, 80.3620, 19.00, 'Open daily 08:00 - 17:00', '08:00', '17:00', 0.0, 500.0, 'Suggested donation/entry: 500 LKR for temple preservation', 45, 'Morning (09:00 - 12:00)', 'Temple dress code applies (cover shoulders/knees, remove shoes and hat). No flash on delicate 1880s wall paintings.', 'Ground level pathways with minor threshold steps into the image shrine.', 'Ask the resident monk or temple caretaker to point out the Queen Victoria and vintage steam train murals.', 'https://images.unsplash.com/photo-1563245372-f21724e3856d?auto=format&fit=crop&w=800&q=80'],
            [15, 'Handunugoda Tea Estate (Virgin White Tea)', 'A legendary 200-acre coastal estate that produces the world-renowned "Virgin White Tea," harvested without being touched by human hands following ancient Chinese imperial rituals. Offers guided factory tours and 20+ tea tastings.', 6, 6.0110, 80.3540, 21.50, 'Open daily 08:00 - 16:30', '08:00', '16:30', 0.0, 0.0, 'Free guided plantation and factory tour with complimentary tea tasting salon', 75, 'Morning (09:00 - 11:30) or early afternoon', 'Respect working agricultural machinery and factory hygienic zones.', 'Tea salon and main processing pavilion are fully accessible; hillside fields have plantation footpaths.', 'Sample the famous Virgin White Tea, Sapphire Oolong, and specialty cinnamon tea at the salon.', 'https://images.unsplash.com/photo-1576092768241-dec231879fc3?auto=format&fit=crop&w=800&q=80']
        ];

        $placeStmt = $pdo->prepare("INSERT OR IGNORE INTO Place (placeid, name, description, categoryid, latitude, longitude, distancekm, openinginfo, open_hour, close_hour, entrancefee, fee_foreign, fee_notes, visitduration, besttime, restrictions, accessibility, traveltips, imageurl) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($places as $p) {
            $placeStmt->execute($p);
        }

        // Facilities
        $facilities = [
            [1, 'Galle Central Railway & Bus Terminal', 'Public Transport', 6.0355, 80.2140, 'Central transport hub connecting Colombo, Matara, and local tuk-tuk stands.'],
            [2, 'Galle Fort Rampart Car Park', 'Parking', 6.0330, 80.2190, 'Spacious vehicle parking directly outside the Old Gate of Galle Fort.'],
            [3, 'Fort Public Restrooms & Info Point', 'Restrooms', 6.0310, 80.2160, 'Clean public municipal restrooms and tourist information counter.'],
            [4, 'Galle Tourist Police Station', 'Tourist Police', 6.0320, 80.2155, 'Dedicated 24/7 tourist safety assistance, emergency lost item reporting.'],
            [5, 'Pedlar Street Heritage Cafes & Dining', 'Restaurant', 6.0280, 80.2175, 'Concentration of bakeries, Sri Lankan rice & curry cafes, gelato shops.'],
            [6, 'Unawatuna Beach Central Car Park', 'Parking', 6.0120, 80.2470, 'Main vehicle and three-wheeler parking with beach path access.'],
            [7, 'Rumassala Nature Lookout Rest Stop', 'Restrooms', 6.0170, 80.2400, 'Rest stop with ocean viewpoint, drinking coconut stalls, and restrooms.'],
            [8, 'Koggala Lake Boat Safari Jetty', 'Public Transport', 5.9990, 80.3310, 'Authorized boat safari departure station with registered safety life jackets.']
        ];
        $facStmt = $pdo->prepare("INSERT OR IGNORE INTO Facility (facilityid, name, type, latitude, longitude, notes) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($facilities as $f) {
            $facStmt->execute($f);
        }

        // Place-Facility Links
        $pfLinks = [
            [1, 2, 200, 'Parking right at Dutch gate entrance to Galle Fort'],
            [1, 3, 150, 'Municipal public restrooms on Church Street'],
            [1, 4, 100, 'Tourist police station next to main entrance gate'],
            [1, 5, 50, 'Famous restaurants and artisan cafes line Pedlar Street'],
            [2, 5, 250, 'Short 3-minute stroll to closest cafes on Lighthouse Street'],
            [3, 3, 100, 'Restrooms located within walking distance'],
            [4, 3, 120, 'Directly accessible via pedestrian street'],
            [5, 2, 80, 'Opposite the old gate parking pavilion'],
            [6, 6, 150, 'Central public parking area near main beach road'],
            [7, 7, 200, 'Rest area with scenic lookout near trail entrance'],
            [8, 7, 300, 'Parking and rest area at base of Peace Pagoda hill'],
            [10, 8, 50, 'Safari boats depart from lake jetty station']
        ];
        $pfStmt = $pdo->prepare("INSERT OR IGNORE INTO PlaceFacility (placeid, facilityid, distance_meters, notes) VALUES (?, ?, ?, ?)");
        foreach ($pfLinks as $pf) {
            $pfStmt->execute($pf);
        }
    }
}

// Global helper function
function getDB(): PDO {
    return Database::getConnection();
}
