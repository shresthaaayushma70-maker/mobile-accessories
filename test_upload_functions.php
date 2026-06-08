<?php
/**
 * Diagnostic page to test profile picture upload and camera functions
 */
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    die('Not logged in');
}

require_once "config.php";

$user_id = $_SESSION['user_id'];
$result = mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id");
$user = mysqli_fetch_assoc($result);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Upload Function Test</title>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css'>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .status { padding: 15px; margin: 15px 0; border-radius: 6px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
        code { background: #f0f0f0; padding: 2px 6px; border-radius: 3px; }
        button { background: #667eea; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-size: 14px; }
        button:hover { background: #5568d3; }
    </style>
</head>
<body>
    <div class='container'>
        <h1><i class='fas fa-camera'></i> Upload & Camera Test</h1>
        
        <div class='status info'>
            <strong>User:</strong> " . htmlspecialchars($user['username']) . "<br>
            <strong>ID:</strong> $user_id<br>
            <strong>Current Picture:</strong> " . ($user['profile_picture'] ? $user['profile_picture'] : 'None') . "
        </div>
        
        <h2>1. File Upload Test</h2>
        <form method='post' enctype='multipart/form-data'>
            <input type='file' id='testFile' name='test_file' accept='image/*'>
            <button type='button' onclick='testFileUpload()'>Upload Test File</button>
        </form>
        <div id='fileResult'></div>
        
        <h2>2. Camera Permission Test</h2>
        <button onclick='testCamera()'>Test Camera Access</button>
        <div id='cameraResult'></div>
        
        <h2>3. FormData Test (Simulated Upload)</h2>
        <button onclick='testFormData()'>Test FormData Creation</button>
        <div id='formDataResult'></div>
        
        <h2>4. JavaScript Function Test</h2>
        <button onclick='testFunctions()'>Test All Functions</button>
        <div id='functionResult'></div>
        
        <a href='profile.php' style='display: inline-block; margin-top: 20px; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 6px;'>Back to Profile</a>
    </div>
    
    <script>
        function testFileUpload() {
            const fileInput = document.getElementById('testFile');
            const file = fileInput.files[0];
            const result = document.getElementById('fileResult');
            
            if (!file) {
                result.innerHTML = '<div class=\"status error\"><i class=\"fas fa-times\"></i> No file selected</div>';
                return;
            }
            
            const info = '<div class=\"status success\">' +
                '<strong>✓ File Selected:</strong><br>' +
                'Name: ' + file.name + '<br>' +
                'Size: ' + (file.size / 1024).toFixed(2) + ' KB<br>' +
                'Type: ' + file.type + '<br>' +
                'Last Modified: ' + new Date(file.lastModified).toLocaleString() +
                '</div>';
            
            result.innerHTML = info;
            
            // Simulate upload
            console.log('Would upload:', file);
        }
        
        async function testCamera() {
            const result = document.getElementById('cameraResult');
            
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
                const tracks = stream.getTracks();
                tracks.forEach(track => track.stop());
                
                result.innerHTML = '<div class=\"status success\"><i class=\"fas fa-check\"></i> Camera access GRANTED</div>';
            } catch (error) {
                result.innerHTML = '<div class=\"status error\"><i class=\"fas fa-times\"></i> Camera access DENIED: ' + error.message + '</div>';
                console.error('Camera error:', error);
            }
        }
        
        function testFormData() {
            const result = document.getElementById('formDataResult');
            const formData = new FormData();
            formData.append('upload_picture', '1');
            formData.append('profile_picture', new Blob(['test'], { type: 'image/jpeg' }), 'test.jpg');
            
            result.innerHTML = '<div class=\"status success\"><i class=\"fas fa-check\"></i> FormData created successfully<br>' +
                '<code>upload_picture</code>: 1<br>' +
                '<code>profile_picture</code>: Blob(test.jpg)<br>' +
                'Ready for <code>fetch()</code> POST</div>';
            
            console.log('FormData:', formData);
        }
        
        function testFunctions() {
            const result = document.getElementById('functionResult');
            let output = '';
            
            // Check required functions
            const functions = {
                'handleFilePreview': typeof handleFilePreview,
                'openCameraModal': typeof openCameraModal,
                'closeCameraModal': typeof closeCameraModal,
                'uploadCapturedPhoto': typeof uploadCapturedPhoto,
                'capturePhotoBtn listener': document.getElementById('capturePhotoBtn') ? 'exists' : 'missing'
            };
            
            output += '<div class=\"status info\"><strong>Function Check:</strong><br>';
            for (let [name, type] of Object.entries(functions)) {
                output += (type === 'function' || type === 'exists' ? 
                    '<i class=\"fas fa-check\" style=\"color:green\"></i>' : 
                    '<i class=\"fas fa-times\" style=\"color:red\"></i>') + 
                    ' ' + name + ': ' + type + '<br>';
            }
            output += '</div>';
            
            result.innerHTML = output;
        }
        
        // Auto-run tests on page load
        window.onload = function() {
            console.log('Profile Picture Test Page Loaded');
            console.log('Camera available:', 'mediaDevices' in navigator);
            console.log('File input available:', !!document.getElementById('testFile'));
        };
    </script>
</body>
</html>
";
?>
