<!-- index.html -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Contacts Dashboard</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div class="container mt-4">
  <h2 class="mb-4">Contacts Dashboard</h2>
  <table class="table table-bordered table-striped" id="contactsTable">
    <thead>
      <tr>
        <th>Recipients</th>
        <th>Name</th>
        <th>Topics</th>
        <th>Language</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <!-- Records loaded via JS -->
    </tbody>
  </table>
</div>

<script>
$(document).ready(function() {
  $.getJSON("/api/contactsList.php", function(data) {
    var rows = '';
    data.forEach(function(row) {
      rows += `<tr>
        <td>${row.recipients}</td>
        <td>${row.name || ''}</td>
        <td>${row.topics || ''}</td>
        <td>${row.lang || ''}</td>
        <td>
        <a href="/index.php/manageGroup?user_id=${row.recipients}" class="btn btn-sm btn-warning">Manage</a>
        <a href="/index.php/manageContactDetails?user_id=${row.recipients}" class="btn btn-sm btn-primary">View</a>
        <a class="btn btn-sm btn-danger deleteBtn" data-recipient="${row.recipients}">Delete</a>
        </td>
      </tr>`;
    });
    $('#contactsTable tbody').html(rows);
  });

  $(document).on('click', '.deleteBtn', function() {
    if (!confirm("Are you sure you want to delete this contact?")) return;
    const recipient = $(this).data('recipient');

    fetch('/api/contactDelete.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ recipients: recipient })
    })
    .then(res => res.json())
    .then(data => {
      alert(data.message);
      location.reload();
    });
  });

});
</script>
</body>
</html>
