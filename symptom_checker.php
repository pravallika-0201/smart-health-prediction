<?php
require_once 'includes/functions.php';
require_once 'config/database.php';
require_once 'includes/prediction_model.php';

// Initialize the predictor
$predictor = new SymptomPredictor($conn);

$symptoms = $predictor->getAllSymptoms();
$message = '';
$predictions = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['symptoms'])) {
    $selectedSymptoms = $_POST['symptoms'];
    $symptomSeverities = isset($_POST['severity']) ? $_POST['severity'] : [];
    
    // Get predictions
    $predictions = $predictor->predictConditions($selectedSymptoms, $symptomSeverities);
    
    // Save to symptom_checks if user is logged in
    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
        $symptomsJson = json_encode($selectedSymptoms);
        $predictionsJson = json_encode($predictions);
        
        $stmt = $conn->prepare("INSERT INTO symptom_checks (user_id, symptoms, possible_conditions) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $userId, $symptomsJson, $predictionsJson);
        $stmt->execute();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Symptom Checker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        .select2-container--default .select2-selection--multiple {
            border: 1px solid #ced4da;
            min-height: 38px;
        }
        .severity-slider {
            width: 100%;
        }
    </style>
</head>
<body class="bg-light">
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container py-4">
        <div class="row">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">Check Your Symptoms</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="symptomForm">
                            <div class="mb-3">
                                <label for="symptoms" class="form-label">Select Your Symptoms:</label>
                                <select class="form-select" id="symptoms" name="symptoms[]" multiple required>
                                    <?php foreach ($symptoms as $symptom): ?>
                                        <option value="<?php echo htmlspecialchars($symptom['name']); ?>">
                                            <?php echo htmlspecialchars($symptom['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div id="severityControls" class="mb-3">
                                <!-- Severity sliders will be added here dynamically -->
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Check Symptoms</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <?php if (!empty($predictions)): ?>
                    <div class="card shadow">
                        <div class="card-header bg-info text-white">
                            <h5 class="card-title mb-0">Possible Conditions</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($predictions as $prediction): ?>
                                <div class="mb-4">
                                    <h5>
                                        <?php echo htmlspecialchars($prediction['condition']['name']); ?>
                                        <span class="badge bg-<?php 
                                            echo $prediction['severity_level'] === 'emergency' ? 'danger' : 
                                                ($prediction['severity_level'] === 'high' ? 'warning' : 
                                                ($prediction['severity_level'] === 'medium' ? 'info' : 'success')); 
                                        ?>">
                                            <?php echo ucfirst($prediction['severity_level']); ?>
                                        </span>
                                    </h5>
                                    <p><strong>Probability:</strong> <?php echo number_format($prediction['probability'], 1); ?>%</p>
                                    <p><?php echo htmlspecialchars($prediction['condition']['description']); ?></p>
                                    
                                    <div class="alert alert-<?php 
                                        echo $prediction['severity_level'] === 'emergency' ? 'danger' : 
                                            ($prediction['severity_level'] === 'high' ? 'warning' : 
                                            ($prediction['severity_level'] === 'medium' ? 'info' : 'success')); 
                                    ?>">
                                        <h6 class="alert-heading">Recommendations:</h6>
                                        <?php if (is_array($prediction['recommendations'])): ?>
                                            <ul class="mb-0">
                                                <?php foreach ($prediction['recommendations'] as $recommendation): ?>
                                                    <li><?php echo htmlspecialchars($recommendation); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php else: ?>
                                            <p class="mb-0"><?php echo htmlspecialchars($prediction['recommendations']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="alert alert-info">
                                <small>Note: This is a preliminary assessment and should not replace professional medical advice. 
                                Always consult with a healthcare provider for proper diagnosis and treatment.</small>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Initialize Select2
        $('#symptoms').select2({
            placeholder: "Select your symptoms",
            width: '100%'
        });
        
        // Handle symptom selection changes
        $('#symptoms').on('change', function() {
            const selectedSymptoms = $(this).val();
            const severityControls = $('#severityControls');
            severityControls.empty();
            
            if (selectedSymptoms && selectedSymptoms.length > 0) {
                severityControls.append('<h6 class="mb-3">Rate the severity of each symptom:</h6>');
                
                selectedSymptoms.forEach(symptom => {
                    const controlHtml = `
                        <div class="mb-3">
                            <label class="form-label">${symptom}:</label>
                            <input type="range" class="form-range severity-slider" 
                                   name="severity[${symptom}]" min="0.1" max="2" step="0.1" value="1">
                            <div class="d-flex justify-content-between">
                                <small>Mild</small>
                                <small>Moderate</small>
                                <small>Severe</small>
                            </div>
                        </div>
                    `;
                    severityControls.append(controlHtml);
                });
            }
        });
    });
    </script>
</body>
</html>
