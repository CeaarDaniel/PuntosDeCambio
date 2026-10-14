    </main>

<?php if (!empty($showModuleNotice)): ?>
    <div id="module-notice" class="module-notice" role="status" aria-live="polite">
        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
        <span></span>
    </div>
<?php endif; ?>

    <script src="<?= $basePathEscaped ?>js/jquery-3.7.1.min.js"></script>
    <script src="<?= $basePathEscaped ?>js/bootstrap.bundle.min.js"></script>
<?php foreach (($pageVendorScripts ?? []) as $vendorScript): ?>
    <script src="<?= $basePathEscaped . htmlspecialchars($vendorScript, ENT_QUOTES, 'UTF-8') ?>"></script>
<?php endforeach; ?>
    <script src="<?= $basePathEscaped ?>js/layout.js"></script>
<?php if (!empty($pageScript)): ?>
    <script src="<?= $basePathEscaped . htmlspecialchars($pageScript, ENT_QUOTES, 'UTF-8') ?>"></script>
<?php endif; ?>
</body>
</html>
