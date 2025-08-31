<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - RentEase</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { margin: 0; font-family: Arial, sans-serif; }
        .sidebar { width: 220px; background: #2c3e50; height: 100vh; color: white; position: fixed; }
        .sidebar h2, .sidebar ul { padding: 0 15px; }
        .sidebar ul { list-style: none; padding-top: 20px; }
        .sidebar ul li { padding: 12px 0; cursor: pointer; }
        .sidebar ul li:hover, .sidebar ul li.active { background-color: #34495e; }
        .sidebar ul li a { color: white; text-decoration: none; display: block; width: 100%; }
        .main { margin-left: 220px; padding: 20px; }
        .notification { background: #e74c3c; color: white; padding: 5px 10px; border-radius: 5px; margin: 10px 0; font-size: 14px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>RentEase Admin</h2>
        <ul>
            <li class="nav-link active" data-page="dashboard_content">Dashboard</li>
            <li class="nav-link" data-page="users">Users</li>
            <li class="nav-link" data-page="reviews">Reviews</li>
            <li class="nav-link" data-page="rooms">Rooms</li>
            <li class="nav-link" data-page="shift_request">Shift Requests</li>
            <li class="nav-link" data-page="admin_notifications">Notifications</li>
            <li class="nav-link" data-page="property_approvals">Property Approvals</li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="main" id="main-content">
        <!-- Default content will load here -->
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Load default page
            loadPage('dashboard_content');

            // Handle sidebar clicks
            document.querySelectorAll('.nav-link').forEach(function (el) {
                el.addEventListener('click', function () {
                    document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
                    this.classList.add('active');
                    loadPage(this.getAttribute('data-page'));
                });
            });

            // AJAX loader
            function loadPage(page) {
                const xhr = new XMLHttpRequest();
                xhr.open('GET', 'ajax/' + page + '.php', true);
                xhr.onload = function () {
                    if (xhr.status === 200) {
                        document.getElementById('main-content').innerHTML = xhr.responseText;
                    } else {
                        document.getElementById('main-content').innerHTML = "<p style='color:red;'>Failed to load " + page + "</p>";
                    }
                };
                xhr.send();
            }

            // Event delegation for approve/reject buttons
            document.addEventListener('click', function(e){
                if(e.target.classList.contains('approve-btn') || e.target.classList.contains('reject-btn')){
                    const button = e.target;
                    const propertyId = button.getAttribute('data-id');
                    const status = button.getAttribute('data-status');

                    if(!confirm(`Are you sure you want to ${status} this property?`)) return;

                    const formData = new FormData();
                    formData.append('property_id', propertyId);
                    formData.append('status', status);

                    fetch('ajax/update_status.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.text())
                    .then(data => {
                        data = data.trim();
                        if(data === 'success'){
                            alert(`Property ${status} successfully!`);
                            const div = document.getElementById(`property-${propertyId}`);
                            if(div) div.remove();
                        } else {
                            alert('Error: ' + data);
                        }
                    })
                    .catch(err => alert('AJAX Error: ' + err));
                }
            });
        });
    </script>
</body>
</html>
