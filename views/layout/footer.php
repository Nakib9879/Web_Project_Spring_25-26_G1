</main>
<footer>
    <p>&copy; <?php echo date('Y'); ?> Food Order System. All rights reserved.</p>
</footer>
<script src="public/js/app.js"></script>
</body>
</html>

<script>
    function editCategory(id, currentName) {
        let newName = prompt("Enter new category name:", currentName);
        if (newName && newName.trim() !== "") {
            document.getElementById('edit-name-' + id).value = newName;
            document.getElementById('edit-form-' + id).submit();
        }
    }
</script>