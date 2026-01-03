<?php
/**
 * People Entry Screen
 * Allows bulk addition of people with CSV/line-based input support
 */

require_once 'auth.php';
requireLogin();

require_once 'db.php';
require_once 'functions.php';

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDB();
    
    // Check if bulk input or single entry
    if (!empty($_POST['bulk_input'])) {
        // Bulk input mode
        $people = parseBulkInput($_POST['bulk_input']);
        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        
        foreach ($people as $person) {
            if (empty($person['sr_no']) || empty($person['full_name']) || empty($person['mobile_number'])) {
                $errorCount++;
                continue;
            }
            
            try {
                $stmt = $db->prepare("
                    INSERT INTO people (entry_no, sr_no, full_name, mobile_number)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([
                    $person['entry_no'] ?? '',
                    $person['sr_no'],
                    $person['full_name'],
                    $person['mobile_number']
                ]);
                $successCount++;
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'UNIQUE constraint') !== false) {
                    $errors[] = "SR No {$person['sr_no']} already exists";
                } else {
                    $errors[] = "Error adding SR No {$person['sr_no']}: " . $e->getMessage();
                }
                $errorCount++;
            }
        }
        
        if ($successCount > 0) {
            $message = "Successfully added {$successCount} person(s).";
            $messageType = 'success';
        }
        if ($errorCount > 0) {
            $message .= ($message ? ' ' : '') . "Failed to add {$errorCount} person(s).";
            if (!empty($errors)) {
                $message .= '<br><small>' . implode('<br>', array_slice($errors, 0, 5)) . '</small>';
            }
            $messageType = $successCount > 0 ? 'info' : 'error';
        }
    } else {
        // Single entry mode
        $entry_no = $_POST['entry_no'] ?? '';
        $sr_no = $_POST['sr_no'] ?? '';
        $full_name = $_POST['full_name'] ?? '';
        $mobile_number = $_POST['mobile_number'] ?? '';
        
        if (empty($sr_no) || empty($full_name) || empty($mobile_number)) {
            $message = "Please fill in all required fields (SR No, Full Name, Mobile Number).";
            $messageType = 'error';
        } else {
            try {
                $stmt = $db->prepare("
                    INSERT INTO people (entry_no, sr_no, full_name, mobile_number)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$entry_no, $sr_no, $full_name, $mobile_number]);
                $message = "Person added successfully!";
                $messageType = 'success';
                
                // Clear form
                $_POST = [];
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'UNIQUE constraint') !== false) {
                    $message = "Error: SR No {$sr_no} already exists.";
                } else {
                    $message = "Error: " . $e->getMessage();
                }
                $messageType = 'error';
            }
        }
    }
}

// Get all people for display
$allPeople = getAllPeople();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>People Entry - Team Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <div class="page-title">
                    <span>People Entry</span>
                </div>
            </div>
            
            <div class="container">
                <?php if ($message): ?>
                    <div class="message message-<?php echo $messageType; ?>">
                        <span><?php echo $message; ?></span>
                    </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <span>Add New Person</span>
                        </div>
                    </div>
                    
                    <form method="POST" action="">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="entry_no">Entry No (Optional)</label>
                                <input type="text" id="entry_no" name="entry_no" 
                                       value="<?php echo htmlspecialchars($_POST['entry_no'] ?? ''); ?>"
                                       placeholder="Enter entry number">
                            </div>
                            
                            <div class="form-group">
                                <label for="sr_no">SR No *</label>
                                <input type="number" id="sr_no" name="sr_no" required
                                       value="<?php echo htmlspecialchars($_POST['sr_no'] ?? ''); ?>"
                                       placeholder="Enter SR number">
                            </div>
                            
                            <div class="form-group">
                                <label for="full_name">Full Name *</label>
                                <input type="text" id="full_name" name="full_name" required
                                       value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>"
                                       placeholder="Enter full name">
                            </div>
                            
                            <div class="form-group">
                                <label for="mobile_number">Mobile Number *</label>
                                <input type="text" id="mobile_number" name="mobile_number" required
                                       value="<?php echo htmlspecialchars($_POST['mobile_number'] ?? ''); ?>"
                                       placeholder="Enter mobile number">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-success">
                            <span>Add Person</span>
                        </button>
                    </form>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <span>Bulk Add (CSV/Line-based)</span>
                        </div>
                    </div>
                    
                    <p class="help-text">
                        Paste multiple rows. Format: Entry No, SR No, Full Name, Mobile Number (comma-separated)<br>
                        Or use space/tab-separated format. One person per line.
                    </p>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="bulk_input">Bulk Input *</label>
                            <textarea id="bulk_input" name="bulk_input" required 
                                      placeholder="Entry No, SR No, Full Name, Mobile Number&#10;1, 101, John Doe, 9876543210&#10;2, 102, Jane Smith, 9876543211"></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-success">
                            <span>Add All</span>
                        </button>
                    </form>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <span>All People (<?php echo count($allPeople); ?>)</span>
                        </div>
                    </div>
                    
                    <?php if (empty($allPeople)): ?>
                        <div class="no-members">No people added yet. Start by adding people above.</div>
                    <?php else: ?>
                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Entry No</th>
                                        <th>SR No</th>
                                        <th>Full Name</th>
                                        <th>Mobile Number</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($allPeople as $person): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($person['entry_no']); ?></td>
                                            <td><strong><?php echo htmlspecialchars($person['sr_no']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($person['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($person['mobile_number']); ?></td>
                                            <td>
                                                <a href="delete.php?action=person&id=<?php echo $person['id']; ?>" 
                                                   class="btn btn-danger btn-small"
                                                   onclick="return confirm('Delete <?php echo htmlspecialchars($person['full_name']); ?> (SR: <?php echo $person['sr_no']; ?>)?\n\nNote: Cannot delete if person is assigned to a team.');">
                                                    <span>Delete</span>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

