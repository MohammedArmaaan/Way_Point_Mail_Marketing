<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Way-Point Admin</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com/3.4.17"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?php echo SITE_URL; ?>assets/css/style.css" rel="stylesheet">
    <script>
		document.addEventListener('DOMContentLoaded', function() {
    const profileButton = document.getElementById('profile-menu-button');
    const profileMenu = document.getElementById('profile-menu');
    const arrow = document.getElementById('dropdown-arrow');

    profileButton.addEventListener('click', function(e) {
        e.stopPropagation();
        const isHidden = profileMenu.classList.contains('hidden');
        
        // Toggle Menu
        profileMenu.classList.toggle('hidden');
        
        // Rotate Arrow
        if (isHidden) {
            arrow.style.transform = 'rotate(180deg)';
        } else {
            arrow.style.transform = 'rotate(0deg)';
        }
    });

    // Close menu when clicking anywhere else on the screen
    document.addEventListener('click', function() {
        if (!profileMenu.classList.contains('hidden')) {
            profileMenu.classList.add('hidden');
            arrow.style.transform = 'rotate(0deg)';
        }
    });
});
	</script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        'display': ['Space Grotesk', 'sans-serif'],
                        'body': ['DM Sans', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <style>
        /* Your animation styles here */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 20px rgba(99, 102, 241, 0.3); }
            50% { box-shadow: 0 0 40px rgba(99, 102, 241, 0.6); }
        }
        @keyframes slide-up {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fade-in {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        @keyframes bounce-in {
            0% { transform: scale(0.3); opacity: 0; }
            50% { transform: scale(1.05); }
            70% { transform: scale(0.9); }
            100% { transform: scale(1); opacity: 1; }
        }
        .animate-float { animation: float 3s ease-in-out infinite; }
        .animate-pulse-glow { animation: pulse-glow 2s ease-in-out infinite; }
        .animate-slide-up { animation: slide-up 0.6s ease-out forwards; }
        .animate-fade-in { animation: fade-in 0.8s ease-out forwards; }
        .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            background-size: 200% 100%;
            animation: shimmer 2s infinite;
        }
        .animate-bounce-in { animation: bounce-in 0.6s ease-out forwards; }
        .glass-effect {
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        .theme-transition {
            transition: background-color 0.5s ease, color 0.5s ease, border-color 0.5s ease, box-shadow 0.5s ease;
        }
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-hover:hover {
            transform: translateY(-8px) scale(1.02);
        }
        .stagger-1 { animation-delay: 0.1s; }
        .stagger-2 { animation-delay: 0.2s; }
        .stagger-3 { animation-delay: 0.3s; }
        .stagger-4 { animation-delay: 0.4s; }
        .stagger-5 { animation-delay: 0.5s; }
    </style>
</head>
<body class="h-full font-body theme-transition bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100">
    <div id="app-container" class="h-full w-full flex flex-col overflow-auto">
        
        <!-- Animated Background Elements -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-indigo-500/20 to-purple-500/20 dark:from-indigo-500/10 dark:to-purple-500/10 rounded-full blur-3xl animate-float"></div>
            <div class="absolute top-1/2 -left-40 w-96 h-96 bg-gradient-to-br from-cyan-500/20 to-blue-500/20 dark:from-cyan-500/10 dark:to-blue-500/10 rounded-full blur-3xl animate-float" style="animation-delay: 1s;"></div>
            <div class="absolute -bottom-40 right-1/4 w-72 h-72 bg-gradient-to-br from-pink-500/20 to-rose-500/20 dark:from-pink-500/10 dark:to-rose-500/10 rounded-full blur-3xl animate-float" style="animation-delay: 2s;"></div>
        </div>
        
        <!-- Header -->
        <header class="relative z-50 glass-effect theme-transition bg-white/80 dark:bg-slate-900/80 border-b border-slate-200/50 dark:border-slate-700/50 px-6 py-4">
    <div class="max-w-7xl mx-auto flex items-center justify-between">
        
        <div class="flex items-center gap-3 animate-fade-in">
            <div class="relative">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center animate-pulse-glow">
                    <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 12h-4l-3 9L9 3l-3 9H2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <div class="absolute -top-1 -right-1 w-3 h-3 bg-green-400 rounded-full border-2 border-white dark:border-slate-900"></div>
            </div>
            <span class="text-xl font-display font-bold bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400 bg-clip-text text-transparent">Way Point Email-Marketing</span>
        </div>
        
        <nav class="hidden md:flex items-center gap-1 animate-fade-in" style="animation-delay: 0.2s;">
            <a href="index.php" class="px-4 py-2 rounded-lg font-medium <?php echo $current_page == 'index' ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'; ?> transition-all hover:scale-105">Dashboard</a>
            <a href="campaigns.php" class="px-4 py-2 rounded-lg font-medium <?php echo $current_page == 'campaigns' ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'; ?> transition-all hover:scale-105">Campaigns</a>
            <a href="template_list.php" class="px-4 py-2 rounded-lg font-medium <?php echo $current_page == 'template_list' ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'; ?> transition-all hover:scale-105">Template</a>
            <a href="subscriberList.php" class="px-4 py-2 rounded-lg font-medium <?php echo $current_page == 'subscribers' ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'; ?> transition-all hover:scale-105">Subscribers</a>
            <a href="analytics.php" class="px-4 py-2 rounded-lg font-medium <?php echo $current_page == 'analytics' ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'; ?> transition-all hover:scale-105">Analytics</a>
        </nav>
        
        <div class="flex items-center gap-4 animate-fade-in" style="animation-delay: 0.3s;">
            <button id="theme-toggle" class="relative w-14 h-7 rounded-full bg-slate-200 dark:bg-slate-700 transition-all duration-500 hover:scale-110 focus:outline-none">
                <div id="toggle-circle" class="absolute top-0.5 left-0.5 w-6 h-6 rounded-full bg-white shadow-lg transition-all duration-500 flex items-center justify-center">
                    <svg id="sun-icon" class="w-4 h-4 text-amber-500 transition-opacity duration-300" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd" />
                    </svg>
                    <svg id="moon-icon" class="w-4 h-4 text-indigo-600 absolute transition-opacity duration-300 opacity-0" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                    </svg>
                </div>
            </button>
            
            <button class="relative p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-all hover:scale-110">
                <svg class="w-6 h-6 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-red-500 rounded-full animate-pulse"></span>
            </button>
            
            <div class="relative group" id="profile-dropdown">
                <button id="profile-menu-button" class="flex items-center gap-3 pl-4 border-l border-slate-200 dark:border-slate-700 focus:outline-none transition-all hover:opacity-80">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-semibold text-sm shadow-lg shadow-indigo-500/20">
                        <?php echo isset($_SESSION['user_name']) ? strtoupper(substr($_SESSION['user_name'], 0, 2)) : 'JD'; ?>
                    </div>
                    <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" id="dropdown-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div id="profile-menu" class="hidden absolute right-0 mt-3 w-48 glass-effect bg-white/90 dark:bg-slate-900/90 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-xl py-2 z-50 animate-slide-up">
                    <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-800">
                        <p class="text-xs text-slate-500">Signed in as</p>
                        <p class="text-sm font-bold truncate text-slate-900 dark:text-white"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin User'); ?></p>
                    </div>
                    
                    <a href="profile.php" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-600 dark:text-slate-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        My Profile
                    </a>

                    <div class="h-px bg-slate-100 dark:bg-slate-800 my-1"></div>

                    <a href="logout.php" class="flex items-center gap-3 px-4 py-2 text-sm text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-colors font-semibold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>