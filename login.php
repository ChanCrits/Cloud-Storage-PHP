<?php
// Set session cookie to persist for 30 days
session_set_cookie_params(30 * 24 * 60 * 60);
session_start();
require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                header("Location: dashboard.php");
            } else {
                echo "Invalid password.";
            }
        } else {
            echo "No user found with that email.";
        }

        $stmt->close();
    } elseif (isset($_POST['qr_code_token'])) {
        $qr_code_token = $_POST['qr_code_token'];

        $stmt = $conn->prepare("SELECT * FROM users WHERE qr_code = ?");
        $stmt->bind_param("s", $qr_code_token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            header("Location: dashboard.php");
        } else {
            echo "Invalid QR code.";
        }

        $stmt->close();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SDrive</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
</head>

</head>
<body>
    <a href="register.php">Register</a>
    <form method="POST" action="login.php">
        <input type="email" name="email" required placeholder="Email">
        <input type="password" name="password" required placeholder="Password">
        <button type="submit">Login</button>
    </form>

    <h2>Or Login with QR Code</h2>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#qrModal">
        Login using QR Code
    </button>

    <!-- QR Code Modal -->
    <div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="qrModalLabel">Scan QR Code</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <video id="preview" style="width:100%;"></video>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div> 
            </div>
        </div>
    </div>

    <form method="POST" action="login.php" id="qr-login-form" style="display:none;">
        <input type="hidden" name="qr_code_token" id="qr_code_token">
        <button type="submit">Login with QR Code</button>
    </form>

    <?php if (isset($_SESSION['qr_code_path'])): ?>
        <h2>Your QR Code</h2>
        <img src="<?php echo $_SESSION['qr_code_path']; ?>" alt="QR Code">
        <?php unset($_SESSION['qr_code_path']); // Remove QR code path from session after displaying ?>
    <?php endif; ?>

    <script>
        function onScanSuccess(content) {
            // Handle the scanned QR code data
            console.log(`Code matched = ${content}`);
            // Set the hidden input value and submit the form
            document.getElementById('qr_code_token').value = content;
            document.getElementById('qr-login-form').submit();
        }

        $('#qrModal').on('shown.bs.modal', function () {
            let scanner = new Instascan.Scanner({ video: document.getElementById('preview') });
            scanner.addListener('scan', function (content) {
                onScanSuccess(content);
            });
            Instascan.Camera.getCameras().then(function (cameras) {
                if (cameras.length > 0) {
                    scanner.start(cameras[0]);
                } else {
                    console.error('No cameras found.');
                }
            }).catch(function (e) {
                console.error(e);
            });

            // Store the scanner instance so it can be stopped later
            $('#qrModal').data('scanner', scanner);
        });

        $('#qrModal').on('hidden.bs.modal', function () {
            let scanner = $('#qrModal').data('scanner');
            if (scanner) {
                scanner.stop();
            }
        });
    </script>
</body>
</html>