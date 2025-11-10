<?php
session_start();
// Redirect to the appropriate user page if the user is already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin_page.php");
    } elseif ($_SESSION['role'] === 'security') {
        header("Location: security_dashboard.php");
    } else {
        header("Location: user_page.php");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome to Campus Lost & Found</title>
    <link rel="stylesheet" href="login.css"> <!-- Re-using login.css for consistent background -->
    <style>
        /* Additional styles for the new landing page content */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .main-container {
            flex: 1;
            overflow-y: auto; /* Allow scrolling */
            display: flex; /* Enable flexbox for centering */
            align-items: center; /* Vertically center content */
        }
        .landing-content {
            color: white;
            text-align: center;
            padding: 50px 20px;
            max-width: 1200px;
            margin: auto;
            z-index: 2;
            position: relative;
        }
        .main-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.7);
        }
        .subtitle {
            font-size: 1.5rem;
            font-weight: 400;
            margin-bottom: 40px;
        }
        .btn-main {
            background: linear-gradient(to right, #00BFFF, #87CEEB);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: bold;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 191, 255, 0.4);
        }
        .btn-main:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 191, 255, 0.6);
        }
        .info-section {
            margin-top: 80px;
            padding: 40px;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .info-section h2 {
            font-size: 2.5rem;
            margin-bottom: 30px;
            color: #00BFFF;
        }
        .how-it-works {
            display: flex;
            justify-content: space-around;
            gap: 30px;
            margin-top: 20px;
        }
        .step {
            flex: 1;
            padding: 20px;
        }
        .step h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }
        .footer {
            position: relative;
            bottom: auto;
            padding: 20px;
            color: rgba(255, 255, 255, 0.8);
            text-align: center;
            width: 100%;
            z-index: 2;
        }
        /* Hide scrollbar for Chrome, Safari and Opera */
        .main-container::-webkit-scrollbar {
            display: none;
        }
        /* Hide scrollbar for IE, Edge and Firefox */
        .main-container {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
        .step-icon {
            width: 80px; /* Adjust as needed */
            height: 80px; /* Adjust as needed */
            /* Filter to make the icons yellow */
            filter: invert(98%) sepia(99%) saturate(7470%) hue-rotate(320deg) brightness(100%) contrast(100%);
            object-fit: contain;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="bg-overlay"></div>
    <div class="main-container">
        <div class="landing-content">
            <h1 class="main-title">CAMPUS LOST & FOUND</h1>
            <p class="subtitle">ibalik nimo or ibalik nimo??!! USTPVILLA returnit Website yow GET STARTED!</p>
            <a href="login_register.php" class="btn-main">Get Started</a>
    
            <section class="info-section">
                <h2>How It Works</h2>
                <div class="how-it-works">
                    <div class="step">
                        <img src="icons/reportissue.png" alt="Report an Item" class="step-icon">
                        <h3> Report an Item</h3>
                        <p>Whether you've lost something valuable or found an item that's not yours, you can create a detailed report in seconds.</p>
                    </div>
                    <div class="step">
                        <img src="icons/notify.png" alt="We Notify" class="step-icon">
                        <h3> We Notify</h3>
                        <p>Our system intelligently works to find matches. Owners are notified when their lost item is found, and finders are guided on the next steps.</p>
                    </div>
                    <div class="step">
                        <img src="icons/reclaim.png" alt="Reclaim at Security" class="step-icon">
                        <h3> Reclaim at Security</h3>
                        <p>Once a claim is verified by our admins, you can securely reclaim your item from the campus security desk.</p>
                    </div>
                </div>
            </section>

            <section class="info-section">
                <h2>Contact Us</h2>
                <p>Have questions, feedback, or need assistance? We're here to help. Reach out to our support team.</p>
                <div class="how-it-works" style="margin-top: 20px;">
                    <div class="step">
                        <img src="icons/mail.png" alt="Email" class="step-icon" style="filter: invert(77%) sepia(66%) saturate(428%) hue-rotate(72deg) brightness(89%) contrast(94%); width: 60px; height: 60px;">
                        <h3>Email</h3>
                        <p><a href="mailto:jeogardones@gmail.com" style="color: #00BFFF; text-decoration: none; font-weight: bold;">jeogardones@gmail.com</a></p>
                    </div>
                    <div class="step"> 
                        <a href="https://web.facebook.com/rizjeo.jeoriz.ziroej" target="_blank">
                            <img src="icons/fb.png" alt="Facebook" class="step-icon" style="filter: invert(20%) sepia(90%) saturate(3000%) hue-rotate(200deg) brightness(90%) contrast(100%); width: 60px; height: 60px;">
                        </a>
                        <h3>Facebook</h3>
                        <p style="color: #00BFFF;">Jeoriz Gardones Honculada</p>
                    </div> 
                    <div class="step">
                        <img src="icons/sms.png" alt="SMS" class="step-icon" style="filter: invert(98%) sepia(99%) saturate(7470%) hue-rotate(320deg) brightness(100%) contrast(100%); width: 60px; height: 60px;">
                        <h3>SMS</h3>
                        <p style="color: #00BFFF;">09638463359</p>
                    </div>
                </div>
            </section>
        </div>
    </div>
    <footer class="footer">Developed by <strong>Kupal Company</strong></footer>
    <script>
        // Prevent back button from leaving the page
        (function (window, location) {
            history.replaceState(null, document.title, location.pathname + "#!/stealingyourhistory");
            history.pushState(null, document.title, location.pathname);
            window.addEventListener("popstate", function () {
                if (location.hash === "#!/stealingyourhistory") {
                    history.replaceState(null, document.title, location.pathname);
                    setTimeout(function () {
                        location.replace("index.php");
                    }, 0);
                }
            }, false);
        }(window, location));
    </script>
</body>
</html>
