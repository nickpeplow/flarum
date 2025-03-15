        <!-- Main content ends here -->
    </div>

    <footer class="mt-5 pb-3 text-center text-muted">
        <hr>
        <p>&copy; <?php echo date('Y'); ?> Keywords Automation Dashboard</p>
        <p class="small">Using PDO Database Connection & Bootstrap 5</p>
    </footer>
</div>

<!-- Bootstrap and jQuery JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    });
</script>
</body>
</html> 