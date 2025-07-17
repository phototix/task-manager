<!-- details.html -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Contact Details</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div class="container mt-4">
  <h2>Contact Details</h2>
  <form id="updateForm">
    <input type="hidden" id="recipients" name="recipients">
    <div class="form-group">
      <label>Name</label>
      <input type="text" class="form-control" id="name" name="name">
    </div>
    <div class="form-group">
      <label>Email</label>
      <input type="email" class="form-control" id="email" name="email" readonly>
    </div>
    <div class="form-group">
      <label>Topics</label>
      <textarea class="form-control" id="topics" name="topics"></textarea>
    </div>
    <div class="form-group">
      <label>Contact Type</label>
      <input type="text" class="form-control" id="contact_type" name="contact_type">
    </div>
    <div class="form-group">
      <label>Language</label>
      <input type="text" class="form-control" id="lang" name="lang">
    </div>
    <button type="submit" class="btn btn-success">Update</button>
    <a href="index.html" class="btn btn-secondary">Back</a>
  </form>
</div>

<script>
const urlParams = new URLSearchParams(window.location.search);
const userId = urlParams.get('user_id');

if (!userId) {
  alert("Missing user_id");
  location.href = '/index.php/manageContacts';
}

$.getJSON("/api/contactDetails.php?user_id=" + userId, function(data) {
  if (data) {
    $('#recipients').val(data.recipients);
    $('#name').val(data.name);
    $('#email').val(data.email);
    $('#topics').val(data.topics);
    $('#contact_type').val(data.contact_type);
    $('#lang').val(data.lang);
  }
});

$('#updateForm').submit(function(e) {
  e.preventDefault();
  $.post('/api/contactUpdate.php', $(this).serialize(), function(response) {
    alert(response.message);
  }, 'json');
});
</script>
</body>
</html>
