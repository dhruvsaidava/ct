<?php
/**
 * Helper Functions for Team Management Platform
 */

require_once 'db.php';

/**
 * Get person by SR number
 * @param int $sr_no
 * @return array|false
 */
function getPersonBySR($sr_no) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM people WHERE sr_no = ?");
    $stmt->execute([$sr_no]);
    return $stmt->fetch();
}

/**
 * Check if person is already in any team
 * @param int $sr_no
 * @return bool
 */
function isPersonInTeam($sr_no) {
    $db = getDB();
    
    // Check if person is a member
    $stmt = $db->prepare("SELECT id FROM team_members WHERE member_sr_no = ?");
    $stmt->execute([$sr_no]);
    return (bool) $stmt->fetch();
}

/**
 * Get team by ID
 * @param int $team_id
 * @return array|false
 */
function getTeam($team_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM teams WHERE id = ?");
    $stmt->execute([$team_id]);
    return $stmt->fetch();
}

/**
 * Get team by name
 * @param string $team_name
 * @return array|false
 */
function getTeamByName($team_name) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT t.*, o.owner_name
        FROM teams t
        JOIN owners o ON t.owner_id = o.id
        WHERE t.team_name = ?
    ");
    $stmt->execute([$team_name]);
    return $stmt->fetch();
}

/**
 * Get team URL slug (e.g., "Team A" -> "a")
 * @param string $team_name
 * @return string
 */
function getTeamSlug($team_name) {
    // Extract letter from "Team A" -> "a"
    if (preg_match('/Team\s+([A-Z])/i', $team_name, $matches)) {
        return strtolower($matches[1]);
    }
    // Fallback: use lowercase team name
    return strtolower(str_replace(['Team ', ' '], '', $team_name));
}

/**
 * Get all teams with owner info
 * @return array
 */
function getAllTeams() {
    $db = getDB();
    $stmt = $db->query("
        SELECT t.*, o.owner_name
        FROM teams t
        JOIN owners o ON t.owner_id = o.id
        ORDER BY t.id
    ");
    return $stmt->fetchAll();
}

/**
 * Get team members for a specific team
 * @param int $team_id
 * @return array
 */
function getTeamMembers($team_id) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT tm.*, p.full_name, p.mobile_number, p.sr_no
        FROM team_members tm
        JOIN people p ON tm.member_sr_no = p.sr_no
        WHERE tm.team_id = ?
        ORDER BY tm.id
    ");
    $stmt->execute([$team_id]);
    return $stmt->fetchAll();
}

/**
 * Get all people
 * @return array
 */
function getAllPeople() {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM people ORDER BY sr_no");
    return $stmt->fetchAll();
}

/**
 * Parse CSV or line-based input
 * @param string $input
 * @return array Array of parsed rows
 */
function parseBulkInput($input) {
    $lines = explode("\n", trim($input));
    $people = [];
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        // Try CSV format first (comma-separated)
        if (strpos($line, ',') !== false) {
            $parts = array_map('trim', explode(',', $line));
            if (count($parts) >= 3) {
                $people[] = [
                    'entry_no' => $parts[0] ?? '',
                    'sr_no' => $parts[1] ?? '',
                    'full_name' => $parts[2] ?? '',
                    'mobile_number' => $parts[3] ?? ''
                ];
            }
        } else {
            // Try tab-separated
            $parts = preg_split('/\s+/', $line);
            if (count($parts) >= 3) {
                $people[] = [
                    'entry_no' => $parts[0] ?? '',
                    'sr_no' => $parts[1] ?? '',
                    'full_name' => $parts[2] ?? '',
                    'mobile_number' => $parts[3] ?? ''
                ];
            }
        }
    }
    
    return $people;
}

/**
 * Delete a person by ID
 * @param int $person_id
 * @return array ['success' => bool, 'message' => string]
 */
function deletePerson($person_id) {
    $db = getDB();
    
    // Get person info
    $stmt = $db->prepare("SELECT sr_no FROM people WHERE id = ?");
    $stmt->execute([$person_id]);
    $person = $stmt->fetch();
    
    if (!$person) {
        return ['success' => false, 'message' => 'Person not found.'];
    }
    
    $sr_no = $person['sr_no'];
    
    // Check if person is in any team
    if (isPersonInTeam($sr_no)) {
        return ['success' => false, 'message' => 'Cannot delete person. They are assigned to a team. Remove them from team first.'];
    }
    
    // Delete person
    try {
        $stmt = $db->prepare("DELETE FROM people WHERE id = ?");
        $stmt->execute([$person_id]);
        return ['success' => true, 'message' => 'Person deleted successfully.'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Delete a team member
 * @param int $member_id
 * @return array ['success' => bool, 'message' => string]
 */
function deleteTeamMember($member_id) {
    $db = getDB();
    
    try {
        $stmt = $db->prepare("DELETE FROM team_members WHERE id = ?");
        $stmt->execute([$member_id]);
        return ['success' => true, 'message' => 'Member removed from team successfully.'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Delete a team (and all its members)
 * @param int $team_id
 * @return array ['success' => bool, 'message' => string]
 */
function deleteTeam($team_id) {
    $db = getDB();
    
    try {
        // Delete team members first
        $stmt = $db->prepare("DELETE FROM team_members WHERE team_id = ?");
        $stmt->execute([$team_id]);
        
        // Delete team
        $stmt = $db->prepare("DELETE FROM teams WHERE id = ?");
        $stmt->execute([$team_id]);
        
        return ['success' => true, 'message' => 'Team deleted successfully.'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Delete an owner (and all teams under that owner)
 * @param int $owner_id
 * @return array ['success' => bool, 'message' => string]
 */
function deleteOwner($owner_id) {
    $db = getDB();
    
    try {
        // Get all teams for this owner
        $stmt = $db->prepare("SELECT id FROM teams WHERE owner_id = ?");
        $stmt->execute([$owner_id]);
        $teams = $stmt->fetchAll();
        
        // Delete team members for all teams
        foreach ($teams as $team) {
            $stmt = $db->prepare("DELETE FROM team_members WHERE team_id = ?");
            $stmt->execute([$team['id']]);
        }
        
        // Delete teams
        $stmt = $db->prepare("DELETE FROM teams WHERE owner_id = ?");
        $stmt->execute([$owner_id]);
        
        // Delete owner
        $stmt = $db->prepare("DELETE FROM owners WHERE id = ?");
        $stmt->execute([$owner_id]);
        
        return ['success' => true, 'message' => 'Owner deleted successfully.'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Get all owners
 * @return array
 */
function getAllOwners() {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM owners ORDER BY owner_name");
    return $stmt->fetchAll();
}

/**
 * Get owner by ID
 * @param int $owner_id
 * @return array|false
 */
function getOwner($owner_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM owners WHERE id = ?");
    $stmt->execute([$owner_id]);
    return $stmt->fetch();
}

/**
 * Generate sample data (55 members)
 * @return array ['success' => bool, 'message' => string, 'count' => int]
 */
function generateSampleData() {
    $db = getDB();
    
    // Sample names
    $firstNames = ['Raj', 'Priya', 'Amit', 'Sneha', 'Vikram', 'Anjali', 'Rahul', 'Kavya', 'Suresh', 'Divya', 
                   'Mohit', 'Neha', 'Ravi', 'Pooja', 'Karan', 'Meera', 'Arjun', 'Shreya', 'Nikhil', 'Isha',
                   'Dhruv', 'Ananya', 'Kunal', 'Riya', 'Varun', 'Sanjana', 'Yash', 'Aditi', 'Harsh', 'Tanvi',
                   'Manish', 'Sakshi', 'Gaurav', 'Aishwarya', 'Rohan', 'Kritika', 'Abhishek', 'Swati', 'Prateek', 'Nidhi',
                   'Siddharth', 'Juhi', 'Vivek', 'Mansi', 'Akash', 'Shivani', 'Rishabh', 'Pallavi', 'Sagar', 'Deepika'];
    
    $lastNames = ['Patel', 'Sharma', 'Kumar', 'Singh', 'Gupta', 'Verma', 'Yadav', 'Jain', 'Shah', 'Mehta',
                  'Reddy', 'Rao', 'Nair', 'Iyer', 'Menon', 'Pillai', 'Desai', 'Joshi', 'Malhotra', 'Agarwal',
                  'Kapoor', 'Chopra', 'Bansal', 'Goyal', 'Arora', 'Khanna', 'Sethi', 'Bhatia', 'Tiwari', 'Mishra'];
    
    $count = 0;
    $errors = 0;
    
    // Clear existing data first (optional - comment out if you want to keep existing)
    // $db->exec("DELETE FROM team_members");
    // $db->exec("DELETE FROM teams");
    // $db->exec("DELETE FROM people");
    
    for ($i = 1; $i <= 55; $i++) {
        $firstName = $firstNames[array_rand($firstNames)];
        $lastName = $lastNames[array_rand($lastNames)];
        $fullName = $firstName . ' ' . $lastName;
        $mobile = '9' . str_pad(rand(100000000, 999999999), 9, '0', STR_PAD_LEFT);
        
        try {
            $stmt = $db->prepare("
                INSERT INTO people (entry_no, sr_no, full_name, mobile_number)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                'ENT' . str_pad($i, 3, '0', STR_PAD_LEFT),
                $i,
                $fullName,
                $mobile
            ]);
            $count++;
        } catch (PDOException $e) {
            // Skip if duplicate
            $errors++;
        }
    }
    
    return [
        'success' => true,
        'message' => "Sample data generated: {$count} people added." . ($errors > 0 ? " ({$errors} skipped - duplicates)" : ''),
        'count' => $count
    ];
}
?>

