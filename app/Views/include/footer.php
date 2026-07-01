<script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= $assetBase ?? '' ?>assets/js/main.js"></script>
<?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
<script>
    if (typeof Swal !== 'undefined') {
        <?php if ($_GET['status'] === 'success' || $_GET['status'] === 'sukses'): ?>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        Toast.fire({
            icon: 'success',
            title: '<?php echo addslashes(htmlspecialchars($_GET['message'])); ?>'
        });
        <?php else: ?>
        Swal.fire({
            icon: 'error',
            title: 'Data Tidak Valid',
            text: '<?php echo addslashes(htmlspecialchars($_GET['message'])); ?>',
            confirmButtonText: 'Mengerti',
            confirmButtonColor: '#10B981'
        });
        <?php endif; ?>
    } else {
        alert('<?php echo addslashes(htmlspecialchars($_GET['message'])); ?>');
    }
</script>
<?php endif; ?>

<!-- Instant.page for just-in-time preloading on hover -->
<script src="//instant.page/5.2.0" type="module" integrity="sha384-jnZcgoEq3ZZVF-r23n0eA638xR1+N+e1tXnO+GqV/75h4T1yK6N9KOLnU5D5wX6+" crossorigin="anonymous"></script>

</body>
</html>
