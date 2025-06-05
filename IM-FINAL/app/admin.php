<?php
require_once 'auth.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Panel - ELE Center</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="home.css" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 font-sans">
    <header>
        <nav class="navbar">
            <div class="logo">
                <img src="ELE-LOGO.png" alt="ELE Technical Training Logo">
            </div>
            <ul class="nav-links">
                <li><a href="index.html">Home</a></li>
                <li><a href="about.html">About Us</a></li>
                <li><a href="contact.html">Contact Us</a></li>
                <li><a href="admin.php" class="active">Login</a></li>
            </ul>
        </nav>
    </header>

    <section class="section admin-section flex items-center justify-center min-h-screen bg-gray-100">
        <div class="form-container bg-white rounded-2xl shadow-xl p-8 w-full max-w-md">
            <div class="form-box">
                <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Registrar Login</h2>
                 <?php if ($error): ?>
                    <p class="error"><?php echo $error; ?></p>
                <?php endif; ?>
                <form action="admin.php" method="POST">
                    <div class="input-container relative mb-4">
                        <input type="text" name="username" placeholder="Username" required
                            class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
                        <i
                            class="fas fa-envelope absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>

                    <div class="input-container relative mb-4">
                        <input type="password" name="password" placeholder="Password" required
                            class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
                        <i class="fas fa-lock absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>

                    <button type="submit"
                        class="btn w-full bg-green-600 text-white p-3 rounded-lg hover:bg-green-700 transition font-semibold">Log
                        In</button>

                    <img src="ELE-LOGO.png" alt="Admin Illustration" class="mt-6 w-32 h-auto block mx-auto" />
                </form>
            </div>
        </div>
    </section>

    <footer>
        <div class="footer-content">
            <div class="copyright">
                <p>© 2025 ELE Technical Training and Assessment Center. All rights reserved.</p>
            </div>
            <div class="footer-social">
                <h4>Connect With Us</h4>
                <div class="footer-social-icons">
                    <a href="https://web.facebook.com/EleTechnicalTrainingCenter">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24"
                            fill="#1877F2">
                            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                        </svg>
                    </a>
                    <a href="https://www.instagram.com/eletechnicaltraining/">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24">
                            <defs>
                                <radialGradient id="instagram-gradient" cx="30%" cy="107%" r="150%">
                                    <stop offset="0%" stop-color="#fdf497" />
                                    <stop offset="5%" stop-color="#fdf497" />
                                    <stop offset="45%" stop-color="#fd5949" />
                                    <stop offset="60%" stop-color="#d6249f" />
                                    <stop offset="90%" stop-color="#285AEB" />
                                </radialGradient>
                            </defs>
                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5" fill="url(#instagram-gradient)" />
                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" fill="white" />
                            <circle cx="17.5" cy="6.5" r="1" fill="white" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </footer>
</body>

</html>