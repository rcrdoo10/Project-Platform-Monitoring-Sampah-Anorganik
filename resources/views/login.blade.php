<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Universitas Sanata Arjuna</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="container">
        <div class="login-box">
            <div class="header">
                <h2>UNIVERSITAS SANATI ARJUNA</h2>
                <p>YOGYAKARTA</p>
            </div>

            <form id="loginForm">
                <div class="input-group">
                    <input type="text" id="username" placeholder="Username" required>
                </div>
                <div class="input-group">
                    <input type="password" id="password" placeholder="Password" required>
                </div>
                <div class="forgot">
                    <a href="#" id="forgotLink">Forgot Password</a>
                </div>
                <button type="submit" class="login-btn">LOGIN</button>
            </form>

            <div class="divider">
                <span>Media Source</span>
            </div>

            <div class="social">
                <a href="#" id="fb" class="social-link">Facebook</a>
                <a href="#" id="ig" class="social-link">Instagram</a>
                <a href="#" id="yt" class="social-link">YouTube</a>
            </div>
        </div>
    </div>

    <div id="toast" class="toast hidden"></div>

    <script src="js/login.js"></script>
</body>
</html>


