        <!-- Footer -->
        <footer class="relative z-10 theme-transition glass-effect bg-white/80 dark:bg-slate-900/80 border-t border-slate-200/50 dark:border-slate-700/50 px-6 py-6">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 12h-4l-3 9L9 3l-3 9H2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
							Designed and Developed With ❤️ By 
							<a style="font-weight:bold;" 
							   href="https://mohammed-armaan-dhakkanji-portfolio.vercel.app/" 
							   target="_blank" 
							   rel="noopener noreferrer">
							   Mohammed Armaan
							</a>
						</p>
                    </div>
					
                    
                    <div class="flex items-center gap-6 text-sm text-slate-500 dark:text-slate-400">
                        <a href="#" class="hover:text-indigo-500 dark:hover:text-indigo-400 transition-colors">Privacy Policy</a>
                        <a href="#" class="hover:text-indigo-500 dark:hover:text-indigo-400 transition-colors">Terms of Service</a>
                        <a href="#" class="hover:text-indigo-500 dark:hover:text-indigo-400 transition-colors">Help Center</a>
                    </div>
                    
                    <p class="text-sm text-slate-500 dark:text-slate-400">© <?php echo date('Y'); ?> Way Point. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
    
    <!-- JavaScript -->
    <script src="<?php echo SITE_URL; ?>assets/js/main.js"></script>
    <script>
        // Theme toggle functionality
        const themeToggle = document.getElementById('theme-toggle');
        const toggleCircle = document.getElementById('toggle-circle');
        const sunIcon = document.getElementById('sun-icon');
        const moonIcon = document.getElementById('moon-icon');
        
        // Check for saved theme preference
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.documentElement.classList.add('dark');
            toggleCircle.style.transform = 'translateX(28px)';
            toggleCircle.classList.add('bg-slate-800');
            toggleCircle.classList.remove('bg-white');
            sunIcon.classList.add('opacity-0');
            moonIcon.classList.remove('opacity-0');
        }
        
        themeToggle.addEventListener('click', function() {
            const isDark = document.documentElement.classList.toggle('dark');
            
            if (isDark) {
                toggleCircle.style.transform = 'translateX(28px)';
                toggleCircle.classList.add('bg-slate-800');
                toggleCircle.classList.remove('bg-white');
                sunIcon.classList.add('opacity-0');
                moonIcon.classList.remove('opacity-0');
                localStorage.setItem('theme', 'dark');
            } else {
                toggleCircle.style.transform = 'translateX(0)';
                toggleCircle.classList.remove('bg-slate-800');
                toggleCircle.classList.add('bg-white');
                sunIcon.classList.remove('opacity-0');
                moonIcon.classList.add('opacity-0');
                localStorage.setItem('theme', 'light');
            }
        });
    </script>
</body>
</html>
<?php ob_end_flush(); ?>