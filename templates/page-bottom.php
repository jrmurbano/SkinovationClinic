    </div><!-- /.main-content -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Handle sidebar toggling on mobile -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Optional: Add a sidebar toggle button for mobile if needed
            if (document.querySelector('.side-nav')) {
                document.querySelector('.navbar-toggler').addEventListener('click', function() {
                    document.querySelector('.side-nav').classList.toggle('show');
                });
            }
        });
    </script>

    <?php if (isset($additional_scripts)) {
        echo $additional_scripts;
    } ?>
    </body>

    </html>
