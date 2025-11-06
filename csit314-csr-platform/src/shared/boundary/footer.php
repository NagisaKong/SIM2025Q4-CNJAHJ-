</main>
<footer class="footer">
    <div class="container">&copy; <?= date('Y') ?> CSR Match Platform</div>
</footer>
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form').forEach((form) => {
        form.setAttribute('novalidate', 'novalidate');
    });

    document.querySelectorAll('.form-error').forEach((errorElement) => {
        const field = errorElement.previousElementSibling;
        if (field instanceof HTMLElement) {
            field.classList.add('field-error');
            field.setAttribute('aria-invalid', 'true');
        }
    });

    const overlay = document.querySelector('[data-flash-overlay]');
    if (!overlay) {
        return;
    }

    const dismiss = () => {
        overlay.classList.add('is-dismissing');
        setTimeout(() => overlay.remove(), 200);
    };

    overlay.addEventListener('click', (event) => {
        if (event.target === overlay) {
            dismiss();
        }
    });

    const dismissButton = overlay.querySelector('[data-flash-dismiss]');
    if (dismissButton instanceof HTMLElement) {
        dismissButton.addEventListener('click', dismiss);
        dismissButton.focus();
    }

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            dismiss();
        }
    }, { once: true });
});
</script>
</body>
</html>
