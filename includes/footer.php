<?php
// includes/footer.php
// Calculate base URL dynamically
$doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$proj_root = str_replace('\\', '/', dirname(__DIR__));
$base_url = str_replace($doc_root, '', $proj_root) . '/';
?>
    </main>

    <footer class="app-footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Library Management System. Practical Exam.</p>
        </div>
    </footer>
    
    <!-- Include Member D's validation script -->
    <script src="<?php echo $base_url; ?>js/validation.js"></script>
    <script src="<?php echo $base_url; ?>js/main.js"></script>
</body>
</html>
