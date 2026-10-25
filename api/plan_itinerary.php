<?php
/**
 * API: Plan One-Day Itinerary 
 */
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../config/db.php';

//  distance formula in kilometers
function calculateDistanceKm($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // km
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return round($earthRadius * $c, 2);
}

// Convert "HH:MM:SS" to minutes from midnight
function timeToMinutes($timeStr) {
    $parts = explode(':', $timeStr);
    return ((int)$parts[0]) * 60 + ((int)($parts[1] ?? 0));
}

// Convert minutes from midnight to "HH:MM AM/PM"
function minutesToTimeStr($minutes) {
    $hours = floor($minutes / 60) % 24;
    $mins = $minutes % 60;
    $ampm = $hours >= 12 ? 'PM' : 'AM';
    $displayH = $hours % 12;
    if ($displayH == 0) $displayH = 12;
    return sprintf("%02d:%02d %s", $displayH, $mins, $ampm);
}

// Convert minutes to 24-hr "HH:MM:SS" for DB
function minutesToTime24($minutes) {
    $hours = floor($minutes / 60) % 24;
    $mins = $minutes % 60;
    return sprintf("%02d:%02d:00", $hours, $mins);
}

try {
    // Parse Input
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $placeIds = $input['place_ids'] ?? [];
    if (empty($placeIds)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Please select at least one attraction to build an itinerary.']);
        exit;
    }

    $date = !empty($input['date']) ? $input['date'] : date('Y-m-d');
    $startTimeStr = !empty($input['start_time']) ? $input['start_time'] : '08:30';
    $endTimeStr = !empty($input['end_time']) ? $input['end_time'] : '18:30';
    $travelMode = !empty($input['travel_mode']) ? strtolower($input['travel_mode']) : 'tuk-tuk';
    $pace = !empty($input['pace']) ? strtolower($input['pace']) : 'moderate';
    $includeLunch = isset($input['include_lunch']) ? (bool)$input['include_lunch'] : true;
    $optimizeRoute = isset($input['optimize_route']) ? (bool)$input['optimize_route'] : true;
    $visitorType = !empty($input['visitor_type']) ? strtolower($input['visitor_type']) : 'local'; // 'local' or 'foreign'
    $mealBudgetPerPerson = isset($input['meal_budget']) ? (float)$input['meal_budget'] : 2000.00;
    $customTransportCost = isset($input['custom_transport_cost']) ? (float)$input['custom_transport_cost'] : null;

    $pdo = getDB();

    // Fetch places from database
    $placeIds = array_map('intval', (array)$placeIds);
    $inClause = implode(',', array_fill(0, count($placeIds), '?'));
    $stmt = $pdo->prepare("SELECT p.*, c.categoryname, c.icon as category_icon 
                           FROM Place p 
                           JOIN Category c ON p.categoryid = c.categoryid 
                           WHERE p.placeid IN ($inClause) AND p.isdeleted = 0");
    $stmt->execute($placeIds);
    $fetched = $stmt->fetchAll();

    if (empty($fetched)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Selected places could not be found.']);
        exit;
    }

    // Map by placeid
    $placesById = [];
    foreach ($fetched as $p) {
        $placesById[$p['placeid']] = $p;
    }

    // Speed by travel mode (km/h) & buffer per trip
    $speedKmH = 25; // default tuk-tuk
    $modeBufferMins = 3;
    $transportBaseFare = 150; // base LKR
    $transportRatePerKm = 120; // LKR/km for Galle tuk-tuk

    if ($travelMode === 'walking') {
        $speedKmH = 4.5;
        $modeBufferMins = 0;
        $transportBaseFare = 0;
        $transportRatePerKm = 0;
    } elseif ($travelMode === 'scooter') {
        $speedKmH = 32;
        $modeBufferMins = 2;
        $transportBaseFare = 1500; // Daily rental average
        $transportRatePerKm = 30; // Fuel
    } elseif ($travelMode === 'private_car' || $travelMode === 'car') {
        $speedKmH = 30;
        $modeBufferMins = 6;
        $transportBaseFare = 4000; // Car with driver base
        $transportRatePerKm = 160;
    }

    // Pace multipliers
    $durationMultiplier = 1.0;
    $stopBuffer = 15; // minutes buffer after visit
    if ($pace === 'relaxed') {
        $durationMultiplier = 1.25;
        $stopBuffer = 20;
    } elseif ($pace === 'fast-paced' || $pace === 'fast') {
        $durationMultiplier = 0.85;
        $stopBuffer = 10;
    }

    // Starting Point: Galle Clock Tower / Railway Terminal
    $startLat = 6.0355;
    $startLon = 80.2140;

    // Route Ordering: Backtracking Reduction using Greedy Nearest Neighbor
    $orderedPlaces = [];
    $remainingPlaces = [];
    foreach ($placeIds as $pid) {
        if (isset($placesById[$pid])) {
            $remainingPlaces[] = $placesById[$pid];
        }
    }

    if ($optimizeRoute && count($remainingPlaces) > 1) {
        $currentLat = $startLat;
        $currentLon = $startLon;
        while (!empty($remainingPlaces)) {
            $bestIndex = 0;
            $bestDistance = 999999;
            foreach ($remainingPlaces as $idx => $cand) {
                $d = calculateDistanceKm($currentLat, $currentLon, (float)$cand['latitude'], (float)$cand['longitude']);
                if ($d < $bestDistance) {
                    $bestDistance = $d;
                    $bestIndex = $idx;
                }
            }
            $chosen = $remainingPlaces[$bestIndex];
            $orderedPlaces[] = $chosen;
            $currentLat = (float)$chosen['latitude'];
            $currentLon = (float)$chosen['longitude'];
            array_splice($remainingPlaces, $bestIndex, 1);
        }
    } else {
        $orderedPlaces = $remainingPlaces;
    }

    // Scheduling Simulation
    $startMinutes = timeToMinutes($startTimeStr);
    $endLimitMinutes = timeToMinutes($endTimeStr);
    $currentMinutes = $startMinutes;

    $timeline = [];
    $warnings = [];
    $totalDistanceKm = 0.0;
    $totalEntranceFees = 0.0;
    $lunchInserted = false;

    $prevLat = $startLat;
    $prevLon = $startLon;
    $prevName = "Galle City Center (Start Hub)";

    foreach ($orderedPlaces as $seq => $place) {
        $lat = (float)$place['latitude'];
        $lon = (float)$place['longitude'];

        // Distance & travel time from previous location
        $legDist = calculateDistanceKm($prevLat, $prevLon, $lat, $lon);
        $totalDistanceKm += $legDist;

        $transitMinutes = (int)ceil(($legDist / $speedKmH) * 60) + $modeBufferMins;
        if ($transitMinutes < 3 && $legDist > 0.05) $transitMinutes = 3;

        // Travel segment
        $travelStartMin = $currentMinutes;
        $travelEndMin = $currentMinutes + $transitMinutes;
        $currentMinutes = $travelEndMin;

        $timeline[] = [
            'type' => 'travel',
            'from_name' => $prevName,
            'to_name' => $place['name'],
            'distance_km' => $legDist,
            'duration_mins' => $transitMinutes,
            'start_time_str' => minutesToTimeStr($travelStartMin),
            'end_time_str' => minutesToTimeStr($travelEndMin),
            'travel_mode' => $travelMode
        ];

        // Mid-day Lunch Break 
        if ($includeLunch && !$lunchInserted && $currentMinutes >= 730 && $currentMinutes <= 840) {
            $lunchDuration = 50;
            $lunchStart = $currentMinutes;
            $lunchEnd = $currentMinutes + $lunchDuration;
            $currentMinutes = $lunchEnd;
            $lunchInserted = true;

            $timeline[] = [
                'type' => 'break',
                'title' => 'Midday Rest & Lunch Break',
                'description' => 'Enjoy authentic southern Sri Lankan cuisine or relax in a shaded local cafe (e.g. Pedlar Street or coastal restaurant).',
                'duration_mins' => $lunchDuration,
                'start_time_str' => minutesToTimeStr($lunchStart),
                'end_time_str' => minutesToTimeStr($lunchEnd)
            ];
        }

        // Attraction Visit
        $visitStartMin = $currentMinutes;
        $adjustedVisitDuration = (int)round($place['visitduration'] * $durationMultiplier);
        if ($adjustedVisitDuration < 20) $adjustedVisitDuration = 20;
        $visitEndMin = $visitStartMin + $adjustedVisitDuration;

        // Opening Hours Validation 
        $openMin = timeToMinutes($place['open_hour'] ?? '08:00');
        $closeMin = timeToMinutes($place['close_hour'] ?? '18:00');

        if ($visitStartMin < $openMin) {
            $warnings[] = sprintf("⚠️ '%s' opens at %s. You may arrive before doors open.", $place['name'], minutesToTimeStr($openMin));
        }
        if ($visitEndMin > $closeMin) {
            $warnings[] = sprintf("⚠️ '%s' closes at %s. Your visit window (%s - %s) may extend past closing time.", $place['name'], minutesToTimeStr($closeMin), minutesToTimeStr($visitStartMin), minutesToTimeStr($visitEndMin));
        }

        // Walking distance warning for long legs
        if ($travelMode === 'walking' && $legDist > 2.0) {
            $warnings[] = sprintf("🚶 Note: Walking segment to '%s' is %.1f km. You may prefer a 5-minute tuk-tuk to avoid southern heat.", $place['name'], $legDist);
        }

        // Fee accounting
        $fee = ($visitorType === 'foreign') ? (float)$place['fee_foreign'] : (float)$place['entrancefee'];
        $totalEntranceFees += $fee;

        $timeline[] = [
            'type' => 'place',
            'place_id' => $place['placeid'],
            'sequence' => $seq + 1,
            'name' => $place['name'],
            'category' => $place['categoryname'],
            'category_icon' => $place['category_icon'],
            'latitude' => $lat,
            'longitude' => $lon,
            'duration_mins' => $adjustedVisitDuration,
            'start_time_str' => minutesToTimeStr($visitStartMin),
            'end_time_str' => minutesToTimeStr($visitEndMin),
            'start_time_24' => minutesToTime24($visitStartMin),
            'end_time_24' => minutesToTime24($visitEndMin),
            'entrance_fee' => $fee,
            'fee_notes' => $place['fee_notes'],
            'besttime' => $place['besttime'],
            'restrictions' => $place['restrictions'],
            'accessibility' => $place['accessibility'],
            'traveltips' => $place['traveltips'],
            'imageurl' => $place['imageurl'],
            'openinginfo' => $place['openinginfo']
        ];

        // Buffer time 
        $bufferStart = $visitEndMin;
        $bufferEnd = $bufferStart + $stopBuffer;
        $currentMinutes = $bufferEnd;

        $timeline[] = [
            'type' => 'buffer',
            'title' => 'Buffer & Transition Window',
            'description' => 'Rest, photography, local refreshment, or souvenir browse.',
            'duration_mins' => $stopBuffer,
            'start_time_str' => minutesToTimeStr($bufferStart),
            'end_time_str' => minutesToTimeStr($bufferEnd)
        ];

        $prevLat = $lat;
        $prevLon = $lon;
        $prevName = $place['name'];
    }

    // Feasibility Warning: Day Window Exceedeed
    if ($currentMinutes > $endLimitMinutes) {
        $overMins = $currentMinutes - $endLimitMinutes;
        $warnings[] = sprintf("⏰ Time Feasibility Notice: This itinerary finishes at %s, exceeding your scheduled end time (%s) by %d minutes. Consider removing 1-2 attractions or switching to a fast pace.", minutesToTimeStr($currentMinutes), minutesToTimeStr($endLimitMinutes), $overMins);
    }

    // Budget Calculations 
    if ($customTransportCost !== null && $customTransportCost >= 0) {
        $estimatedTransportCost = $customTransportCost;
    } else {
        if ($travelMode === 'walking') {
            $estimatedTransportCost = 0;
        } elseif ($travelMode === 'scooter') {
            $estimatedTransportCost = round($transportBaseFare + ($totalDistanceKm * $transportRatePerKm));
        } else {
            // Tuk-tuk or car
            $estimatedTransportCost = round($transportBaseFare + ($totalDistanceKm * $transportRatePerKm));
        }
    }

    $estimatedTotalBudget = $totalEntranceFees + $estimatedTransportCost + $mealBudgetPerPerson;

    // Save itinerary to database
    $itineraryId = null;
    try {
        $insItin = $pdo->prepare("INSERT INTO Itinerary (title, date, starttime, endtime, travelmode, pace, estimatedbudget) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insItin->execute([
            'Galle Smart Day Itinerary (' . count($orderedPlaces) . ' stops)',
            $date,
            minutesToTime24($startMinutes),
            minutesToTime24(min($currentMinutes, 1439)),
            $travelMode,
            $pace,
            $estimatedTotalBudget
        ]);
        $itineraryId = $pdo->lastInsertId();

        // Save items
        $itemStmt = $pdo->prepare("INSERT INTO ItineraryItem (itineraryid, placeid, sequenceno, starttime, endtime, travelminutes, visitminutes, bufferminutes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $itemSeq = 1;
        foreach ($timeline as $tItem) {
            if ($tItem['type'] === 'place') {
                $itemStmt->execute([
                    $itineraryId,
                    $tItem['place_id'],
                    $itemSeq++,
                    $tItem['start_time_24'],
                    $tItem['end_time_24'],
                    15,
                    $tItem['duration_mins'],
                    $stopBuffer
                ]);
            }
        }
    } catch (Exception $dbEx) {
    
    }

    // Return Complete Response
    echo json_encode([
        'status' => 'success',
        'itinerary_id' => $itineraryId,
        'summary' => [
            'date' => $date,
            'start_time' => minutesToTimeStr($startMinutes),
            'estimated_finish' => minutesToTimeStr($currentMinutes),
            'target_end_time' => minutesToTimeStr($endLimitMinutes),
            'stops_count' => count($orderedPlaces),
            'travel_mode' => ucfirst($travelMode),
            'pace' => ucfirst($pace),
            'visitor_type' => ucfirst($visitorType),
            'total_distance_km' => round($totalDistanceKm, 1),
            'is_feasible' => ($currentMinutes <= $endLimitMinutes),
            'route_optimized' => $optimizeRoute
        ],
        'budget' => [
            'entrance_fees' => round($totalEntranceFees, 2),
            'estimated_transport' => round($estimatedTransportCost, 2),
            'meal_budget' => round($mealBudgetPerPerson, 2),
            'total_estimated' => round($estimatedTotalBudget, 2),
            'currency' => 'LKR',
            'is_estimate' => true,
            'disclaimer' => 'All figures are estimates and subject to changes in fuel prices, season, and exchange rates.'
        ],
        'warnings' => $warnings,
        'timeline' => $timeline,
        'places' => $orderedPlaces
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Planning failed: ' . $e->getMessage()]);
}
