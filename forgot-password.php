<?php
require_once 'includes/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $error = 'Please enter your email address';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Save token to database
            $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$email, $token, $expires]);
            
            // In a real app, you'd send an email here
            // For demo, we'll just show the reset link
            $reset_link = SITE_URL . "reset-password.php?token=" . $token;
            $success = "Password reset link: <a href='$reset_link' class='underline'>$reset_link</a>";
        } else {
            // Don't reveal if email exists or not for security
            $success = 'If your email exists in our system, you will receive a password reset link.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Forgot Password</title>
    <script src="https://cdn.tailwindcss.com/3.4.17"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .animate-float { animation: float 6s ease-in-out infinite; }
        .glass-effect {
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }
    </style>
</head>
<body class="h-full font-body bg-gradient-to-br from-indigo-900 via-purple-900 to-pink-900">
    <!-- Animated Background -->
    <div class="fixed inset-0 overflow-hidden">
        <div class="absolute top-20 left-10 w-72 h-72 bg-indigo-500/30 rounded-full blur-3xl animate-float"></div>
        <div class="absolute bottom-20 right-10 w-96 h-96 bg-purple-500/30 rounded-full blur-3xl animate-float" style="animation-delay: 1s;"></div>
    </div>

    <!-- Forgot Password Container -->
    <div class="relative z-10 min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center gap-3">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M22 12h-4l-3 9L9 3l-3 9H2" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                        </svg>
                    </div>
                    <span class="text-3xl font-display font-bold text-white"><?php echo SITE_NAME; ?></span>
                </div>
            </div>

            <!-- Forgot Password Card -->
            <div class="glass-effect bg-white/10 backdrop-blur-xl rounded-3xl p-8 shadow-2xl border border-white/20">
                <h2 class="text-2xl font-display font-bold text-white mb-2">Reset Password</h2>
                <p class="text-indigo-200 mb-6">Enter your email to receive a reset link</p>
                
                <?php if ($error): ?>
                <div class="mb-6 p-4 bg-red-500/20 border border-red-500/50 rounded-xl text-white">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="mb-6 p-4 bg-green-500/20 border border-green-500/50 rounded-xl text-white">
                    <?php echo $success; ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="" class="space-y-5">
                    <!-- Email -->
                    <div>
                        <label class="block text-indigo-200 text-sm font-medium mb-2">Email Address</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-indigo-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </span>
                            <input type="email" 
                                   name="email" 
                                   class="w-full pl-12 pr-4 py-4 bg-white/10 border border-white/20 rounded-xl text-white placeholder-indigo-300 focus:outline-none focus:border-indigo-400 transition-all"
                                   placeholder="john@example.com"
                                   required>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" 
                            class="w-full py-4 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-semibold rounded-xl hover:shadow-lg transition-all">
                        Send Reset Link
                    </button>
                </form>
                
                <!-- Back to Login -->
                <p class="text-center mt-8 text-indigo-200">
                    Remember your password? 
                    <a href="login.php" class="text-white font-semibold hover:text-indigo-300 underline">Sign In</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>