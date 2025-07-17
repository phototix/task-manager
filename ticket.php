<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tickets</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4"><i class="fas fa-ticket-alt"></i> Support Tickets</h2>

    <a href="/?user_id=<?php echo $_GET['user_id'] ?? ''; ?>" style="margin-left:10px;margin-top:10px;">
        <button class="btn btn-warning" style="margin-top:10px;margin-bottom:10px;"><i class="fas fa-list me-2"></i>Tasks List</button>
    </a>

    <a href="/index.php/calendar?user_id=<?php echo $_GET['user_id'] ?? ''; ?>">
        <button class="btn btn-warning" style="margin-top:10px;margin-bottom:10px;"><i class="fas fa-calendar me-2"></i>Calendar</button>
    </a>

    <div id="ticket-table-container">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Issue</th>
                    <th>Company</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody id="ticket-list">
                <tr>
                    <td colspan="5" class="text-center text-muted">Loading tickets...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Ticket Detail Modal -->
<div class="modal fade" id="ticketModal" tabindex="-1" aria-labelledby="ticketModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-info-circle"></i> Ticket Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <dl class="row" id="ticket-details">

          <dt class="col-sm-3">Issuer Name</dt>
          <dd class="col-sm-9" id="detail-name"></dd>

          <dt class="col-sm-3">Issuer Phone</dt>
          <dd class="col-sm-9" id="detail-phone"></dd>

          <dt class="col-sm-3">Issuer Email</dt>
          <dd class="col-sm-9" id="detail-email"></dd>
          
          <dt class="col-sm-3">Issue</dt>
          <dd class="col-sm-9" id="detail-issue"></dd>

          <dt class="col-sm-3">Company</dt>
          <dd class="col-sm-9" id="detail-company"></dd>

          <dt class="col-sm-3">Status</dt>
          <dd class="col-sm-9" id="detail-status"></dd>

          <dt class="col-sm-3">Remarks</dt>
          <dd class="col-sm-9" id="detail-remarks"></dd>

          <dt class="col-sm-3">Created At</dt>
          <dd class="col-sm-9" id="detail-date"></dd>
        </dl>
      </div>
    </div>
  </div>
</div>

<script>
    const urlParams = new URLSearchParams(window.location.search);
    const userId = urlParams.get('user_id');

    function getStatusClass(status) {
        switch (status.toLowerCase()) {
            case 'pending': return 'warning';
            case 'resolved': return 'success';
            case 'in progress': return 'info';
            case 'closed': return 'secondary';
            default: return 'light';
        }
    }

    if (!userId) {
        $('#ticket-list').html('<tr><td colspan="5" class="text-danger text-center">No user_id provided in URL.</td></tr>');
    } else {
        $.getJSON(`/api/ticket.php?user_id=${encodeURIComponent(userId)}`, function(data) {
            if (data.length === 0) {
                $('#ticket-list').html('<tr><td colspan="5" class="text-muted text-center">No tickets found.</td></tr>');
                return;
            }

            const rows = data.map(ticket => `
                <tr class="ticket-row" data-id="${ticket.id}">
                    <td>${ticket.id}</td>
                    <td>${ticket.issue}</td>
                    <td>${ticket.company_name}</td>
                    <td><span class="badge bg-${getStatusClass(ticket.status)}">${ticket.status}</span></td>
                    <td>${ticket.inquiry_date}</td>
                </tr>
            `).join('');
            $('#ticket-list').html(rows);
        }).fail(function() {
            $('#ticket-list').html('<tr><td colspan="5" class="text-danger text-center">Failed to load tickets.</td></tr>');
        });
    }

    // On row click, fetch ticket details
    $(document).on('click', '.ticket-row', function() {
        const ticketId = $(this).data('id');
        $.getJSON(`/api/ticket.php?user_id=${encodeURIComponent(userId)}&id=${ticketId}`, function(ticket) {
            $('#detail-issue').text(ticket.issue);
            $('#detail-name').text(ticket.name);
            $('#detail-phone').text(ticket.phone);
            $('#detail-email').text(ticket.email);
            $('#detail-company').text(ticket.company_name);
            $('#detail-status').html(`<span class="badge bg-${getStatusClass(ticket.status)}">${ticket.status}</span>`);
            $('#detail-remarks').text(ticket.remarks ?? 'No remarks.');
            $('#detail-date').text(ticket.inquiry_date);
            $('#ticketModal').modal('show');
        });
    });
</script>
</body>
</html>