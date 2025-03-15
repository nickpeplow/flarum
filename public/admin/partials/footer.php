        <!-- Main content ends here -->
    </div>

    <footer class="mt-5 pt-4 pb-4 text-center text-muted border-top">
        <p>&copy; <?php echo date('Y'); ?> Flarum Keywords Admin</p>
        <p class="small">Database connection using PDO + Bootstrap 5</p>
    </footer>
</div>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery (for AJAX operations) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>
    // Common JavaScript functions can go here
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize any tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
</body>
</html> 