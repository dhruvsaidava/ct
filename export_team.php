<?php
/**
 * Team Export Handler
 * Exports team data in PDF or List (CSV/Text) format
 */

require_once 'auth.php';
requireLogin();

require_once 'db.php';
require_once 'functions.php';

// Get team ID
$team_id = isset($_GET['team_id']) ? (int)$_GET['team_id'] : 0;
$format = isset($_GET['format']) ? $_GET['format'] : 'pdf'; // pdf or list

if ($team_id <= 0) {
    header('Location: teams.php');
    exit;
}

// Get team info
$team = getTeam($team_id);
if (!$team) {
    header('Location: teams.php');
    exit;
}

// Get team with owner info
$db = getDB();
$stmt = $db->prepare("
    SELECT t.*, o.owner_name
    FROM teams t
    JOIN owners o ON t.owner_id = o.id
    WHERE t.id = ?
");
$stmt->execute([$team_id]);
$team = $stmt->fetch();

// Get team members
$members = getTeamMembers($team_id);

if ($format === 'pdf') {
    // Export as PDF (HTML format that can be printed to PDF)
    exportTeamPDF($team, $members);
} else {
    // Export as List (CSV format)
    exportTeamList($team, $members);
}

/**
 * Export team as PDF (HTML format)
 */
function exportTeamPDF($team, $members) {
    $date = date('Y-m-d H:i:s');
    
    header('Content-Type: text/html; charset=utf-8');
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($team['team_name']); ?> - Export</title>
    <style>
        @media print {
            @page {
                margin: 1cm;
            }
            body {
                margin: 0;
                padding: 0;
            }
        }
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 28px;
        }
        .header h2 {
            margin: 10px 0 0 0;
            color: #666;
            font-size: 18px;
            font-weight: normal;
        }
        .info-section {
            margin-bottom: 30px;
        }
        .info-row {
            display: flex;
            margin-bottom: 10px;
            padding: 8px;
            background: #f5f5f5;
        }
        .info-label {
            font-weight: bold;
            width: 150px;
            color: #333;
        }
        .info-value {
            flex: 1;
            color: #666;
        }
        .members-section {
            margin-top: 30px;
        }
        .members-section h3 {
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table th {
            background: #333;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: bold;
        }
        table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        table tr:nth-child(even) {
            background: #f9f9f9;
        }
        table tr:hover {
            background: #f5f5f5;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #999;
            font-size: 12px;
        }
        .no-print {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background: #e8f4f8;
            border: 1px solid #bee5eb;
            border-radius: 5px;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <p><strong>Print this page to save as PDF</strong></p>
        <p>Press Ctrl+P (Windows) or Cmd+P (Mac) to print, then select "Save as PDF" as the destination.</p>
    </div>
    
    <div class="header">
        <h1><?php echo htmlspecialchars($team['team_name']); ?></h1>
        <h2>Team Export</h2>
    </div>
    
    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Team Name:</div>
            <div class="info-value"><?php echo htmlspecialchars($team['team_name']); ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Owner:</div>
            <div class="info-value"><?php echo htmlspecialchars($team['owner_name']); ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Total Members:</div>
            <div class="info-value"><?php echo count($members); ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Export Date:</div>
            <div class="info-value"><?php echo $date; ?></div>
        </div>
    </div>
    
    <div class="members-section">
        <h3>Team Members</h3>
        <?php if (empty($members)): ?>
            <p>No members in this team.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>SR No</th>
                        <th>Full Name</th>
                        <th>Mobile Number</th>
                        <th>Entry No</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $counter = 1;
                    foreach ($members as $member): 
                        $person = getPersonBySR($member['sr_no']);
                    ?>
                        <tr>
                            <td><?php echo $counter++; ?></td>
                            <td><?php echo htmlspecialchars($member['sr_no']); ?></td>
                            <td><strong><?php echo htmlspecialchars($member['full_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($member['mobile_number']); ?></td>
                            <td><?php echo htmlspecialchars($person['entry_no'] ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <div class="footer">
        <p>Generated on <?php echo $date; ?> | Team Management System</p>
    </div>
    
    <script>
        // Auto-trigger print dialog after page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
    <?php
    exit;
}

/**
 * Export team as List (CSV format)
 */
function exportTeamList($team, $members) {
    $filename = str_replace(' ', '_', $team['team_name']) . '_' . date('Y-m-d') . '.csv';
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Write header row
    fputcsv($output, ['Team Name', 'Owner', 'Total Members', 'Export Date']);
    fputcsv($output, [
        $team['team_name'],
        $team['owner_name'],
        count($members),
        date('Y-m-d H:i:s')
    ]);
    
    // Empty row
    fputcsv($output, []);
    
    // Write members header
    fputcsv($output, ['#', 'SR No', 'Full Name', 'Mobile Number', 'Entry No']);
    
    // Write members data
    $counter = 1;
    foreach ($members as $member) {
        $person = getPersonBySR($member['sr_no']);
        fputcsv($output, [
            $counter++,
            $member['sr_no'],
            $member['full_name'],
            $member['mobile_number'],
            $person['entry_no'] ?? ''
        ]);
    }
    
    fclose($output);
    exit;
}
?>

