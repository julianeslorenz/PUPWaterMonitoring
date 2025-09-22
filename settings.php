<?php
include __DIR__ . '/connection.php'; // This must point to your PDO config file
// require __DIR__ . '/../water/authentication.php';

try {
    $stmt = $conn->query("SELECT * FROM email_recipients ORDER BY recipient_id DESC");
    $recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<p style='color:red;'>Failed to load email recipients: " . $e->getMessage() . "</p>";
    $recipients = [];
}
?>
<!-- Settings -->

<div id="settings" class="tab-content">
    <div class="dashboard-grid">
        <div class="card-container">
            <h2 style="text-align: center; margin-bottom: 20px; color: #333;">Email Recepient</h2>
                <div id="emailRecipients">
                    <?php if (count($recipients) > 0): ?>
                        <?php foreach ($recipients as $row): ?>
                            <div style="padding: 10px; background: #f8f9fa; border-radius: 8px; margin-bottom: 10px;">
                                <?= htmlspecialchars($row['email']) ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align:center;">No email recipients found.</p>
                    <?php endif; ?>
                </div>
            <button class="btn btn-danger" style="margin-top: 15px;" data-bs-toggle="modal" data-bs-target="#addEmail">Add Recipient</button>
        </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="addEmail" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h1 class="modal-title fs-5" id="exampleModalLabel">Add Email Recipient</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST" action="add_email.php">
            <div class="modal-body">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter email address" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" name="add_email_submit" class="btn btn-primary">Add</button>
            </div>
        </form>
    </div>
  </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="successModalLabel">Success</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Email added successfully!
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

<!-- Warning Modal -->
<div class="modal fade" id="warningModal" tabindex="-1" aria-labelledby="warningModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-warning">
      <div class="modal-header bg-warning text-dark">
        <h1 class="modal-title fs-5" id="warningModalLabel">Duplicate Email</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        This email is already in the list.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-warning" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (window.location.hash === "#settings" && urlParams.get('success') === '1') {
        // Activate the settings tab if not already active
        var settingsTab = document.querySelector('a[href="#settings"]');
        if (settingsTab) {
            var tab = new bootstrap.Tab(settingsTab);
            tab.show();
        }
        // Show the modal
        var successModal = new bootstrap.Modal(document.getElementById('successModal'));
        successModal.show();

        // Remove success=1 from the URL without reloading
        urlParams.delete('success');
        const newUrl = window.location.pathname + window.location.hash + (urlParams.toString() ? '?' + urlParams.toString() : '');
        window.history.replaceState({}, '', newUrl);
    }
});
</script>
