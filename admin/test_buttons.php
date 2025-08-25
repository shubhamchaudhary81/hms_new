<?php
// Simple test file to verify button functionality
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Button Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .test-card {
            border: 1px solid #ddd;
            padding: 20px;
            margin: 20px;
            border-radius: 8px;
        }
        .test-button {
            margin: 10px;
            padding: 10px 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Button Functionality Test</h1>
        
        <div class="test-card">
            <h3>Test View Button</h3>
            <button class="btn btn-primary test-button" onclick="testView()">Test View Function</button>
            <p id="view-result">Click the button to test view functionality</p>
        </div>
        
        <div class="test-card">
            <h3>Test Edit Button</h3>
            <button class="btn btn-success test-button" onclick="testEdit()">Test Edit Function</button>
            <p id="edit-result">Click the button to test edit functionality</p>
        </div>
        
        <div class="test-card">
            <h3>Test Modal</h3>
            <button class="btn btn-warning test-button" data-bs-toggle="modal" data-bs-target="#testModal">Test Modal</button>
        </div>
    </div>

    <!-- Test Modal -->
    <div class="modal fade" id="testModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Test Modal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>This is a test modal to verify Bootstrap is working.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function testView() {
            document.getElementById('view-result').innerHTML = '✅ View function is working!';
            console.log('View function test successful');
        }
        
        function testEdit() {
            document.getElementById('edit-result').innerHTML = '✅ Edit function is working!';
            console.log('Edit function test successful');
        }
        
        // Test if Bootstrap is loaded
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Bootstrap version:', typeof bootstrap !== 'undefined' ? 'Loaded' : 'Not loaded');
            console.log('jQuery version:', typeof $ !== 'undefined' ? 'Loaded' : 'Not loaded');
        });
    </script>
</body>
</html>
