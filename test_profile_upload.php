<?php
/**
 * Test Profile Picture Upload
 * Simple test page to verify upload functionality
 */

session_start();

// Check if logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    die("ERROR: Please log in first");
}

require_once "config.php";

$user_id = $_SESSION['user_id'];
$response = ['success' => false, 'message' => ''];

// Check upload method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Log the request
    error_log("Upload request received");
    error_log("POST data: " . json_encode($_POST));
    error_log("FILES data: " . json_encode($_FILES));
    
    if (isset($_POST['upload_picture']) && isset($_FILES['profile_picture'])) {
        error_log("File upload detected");
        
        $file = $_FILES['profile_picture'];
        error_log("File info - Name: {$file['name']}, Size: {$file['size']}, Type: {$file['type']}, Tmp: {$file['tmp_name']}");
        
        // Call upload function
        $upload_result = upload_profile_picture($conn, $user_id, $file);
        
        error_log("Upload result: " . json_encode($upload_result));
        
        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode($upload_result);
        exit;
    }
}

// Show test page
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profile Upload Test</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { padding: 20px; background: #f8f9fa; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .form-group label { font-weight: 600; color: #333; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Profile Picture Upload Test</h1>
        <p style="color: #666; margin-bottom: 20px;">Test the file upload and camera capture functionality</p>
        
        <div id="successMsg" style="display: none;" class="alert alert-success"></div>
        <div id="errorMsg" style="display: none;" class="alert alert-danger"></div>
        
        <form id="uploadForm" enctype="multipart/form-data">
            <div class="form-group">
                <label for="fileInput">Select Image File:</label>
                <input type="file" class="form-control" id="fileInput" name="profile_picture" accept=".jpg,.jpeg,.png,.webp" required>
                <small class="text-muted">JPG, PNG, or WEBP (max 5MB)</small>
            </div>
            
            <button type="button" class="btn btn-primary btn-block" onclick="uploadFile()">
                <i class="fas fa-upload"></i> Upload File
            </button>
        </form>
        
        <hr style="margin: 30px 0;">
        
        <h5>Test Camera Capture</h5>
        <button type="button" class="btn btn-info btn-block mb-3" onclick="openCamera()">
            <i class="fas fa-video"></i> Test Camera (if available)
        </button>
        
        <div id="cameraSection" style="display: none; margin-top: 20px;">
            <video id="testCamera" style="width: 100%; border-radius: 10px; background: #000; display: none;" playsinline autoplay muted></video>
            <div id="cameraPlaceholder" style="background: #f0f0f0; border-radius: 10px; padding: 40px; text-align: center;">
                <p>Camera initializing...</p>
            </div>
            <div style="margin-top: 10px;">
                <button type="button" class="btn btn-success" onclick="capturePhoto()">Capture</button>
                <button type="button" class="btn btn-secondary" onclick="closeCamera()">Close</button>
            </div>
        </div>
        
        <div id="uploadProgress" style="display: none; margin-top: 20px;">
            <div class="progress">
                <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%"></div>
            </div>
            <p style="text-align: center; color: #666; font-size: 12px;">Uploading...</p>
        </div>
        
        <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 10px;">
            <h6>Debug Information:</h6>
            <pre id="debugInfo" style="background: white; padding: 10px; border-radius: 5px; font-size: 11px; max-height: 200px; overflow-y: auto;"></pre>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    
    <script>
        const debugInfo = document.getElementById('debugInfo');
        const successMsg = document.getElementById('successMsg');
        const errorMsg = document.getElementById('errorMsg');
        
        function log(msg) {
            const timestamp = new Date().toLocaleTimeString();
            debugInfo.textContent += `[${timestamp}] ${msg}\n`;
            debugInfo.scrollTop = debugInfo.scrollHeight;
        }
        
        function uploadFile() {
            const fileInput = document.getElementById('fileInput');
            if (!fileInput.files || !fileInput.files[0]) {
                alert('Please select a file');
                return;
            }
            
            const file = fileInput.files[0];
            log(`Selected file: ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`);
            
            const formData = new FormData();
            formData.append('profile_picture', file);
            formData.append('upload_picture', '1');
            
            log('Sending upload request...');
            document.getElementById('uploadProgress').style.display = 'block';
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                log(`Response status: ${response.status}`);
                if (response.ok) {
                    return response.json();
                } else {
                    throw new Error(`HTTP ${response.status}`);
                }
            })
            .then(data => {
                log('Response received: ' + JSON.stringify(data));
                document.getElementById('uploadProgress').style.display = 'none';
                
                if (data.success) {
                    successMsg.style.display = 'block';
                    successMsg.textContent = '✓ ' + data.message;
                    errorMsg.style.display = 'none';
                } else {
                    errorMsg.style.display = 'block';
                    errorMsg.textContent = '✗ ' + data.message;
                    successMsg.style.display = 'none';
                }
            })
            .catch(error => {
                log('Error: ' + error.message);
                document.getElementById('uploadProgress').style.display = 'none';
                errorMsg.style.display = 'block';
                errorMsg.textContent = '✗ Error: ' + error.message;
            });
        }
        
        async function openCamera() {
            log('Opening camera...');
            const cameraSection = document.getElementById('cameraSection');
            const testCamera = document.getElementById('testCamera');
            const cameraPlaceholder = document.getElementById('cameraPlaceholder');
            
            cameraSection.style.display = 'block';
            cameraPlaceholder.style.display = 'block';
            testCamera.style.display = 'none';
            
            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user' },
                    audio: false
                });
                
                testCamera.srcObject = stream;
                testCamera.onloadedmetadata = () => {
                    testCamera.play();
                    cameraPlaceholder.style.display = 'none';
                    testCamera.style.display = 'block';
                    log('Camera opened successfully');
                };
            } catch (error) {
                log('Camera error: ' + error.message);
                alert('Camera error: ' + error.message);
                cameraSection.style.display = 'none';
            }
        }
        
        function closeCamera() {
            const testCamera = document.getElementById('testCamera');
            if (testCamera.srcObject) {
                testCamera.srcObject.getTracks().forEach(track => track.stop());
            }
            document.getElementById('cameraSection').style.display = 'none';
            log('Camera closed');
        }
        
        log('Test page loaded. Try uploading a file or opening the camera.');
    </script>
</body>
</html>
