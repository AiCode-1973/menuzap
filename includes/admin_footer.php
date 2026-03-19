            </div>
        </main>
    </div> <!-- Fim flex sidebar -->

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        }

        // Auto hide flash messages timeout (se houver)
        const toastMessage = document.getElementById('toastMessage');
        if (toastMessage) {
            setTimeout(() => {
                toastMessage.style.opacity = '0';
                setTimeout(() => toastMessage.remove(), 300);
            }, 3000);
        }
    </script>
</body>
</html>
