<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tickets</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">

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

<script>
    // Get user_id from URL
    const urlParams = new URLSearchParams(window.location.search);
    const userId = urlParams.get('user_id');

    if (!userId) {
        $('#ticket-list').html('<tr><td colspan="5" class="text-danger text-center">No user_id provided in URL.</td></tr>');
    } else {
        // Fetch ticket list from PHP
        $.getJSON(`/api/ticket.php?user_id=${encodeURIComponent(userId)}`, function(data) {
            if (data.length === 0) {
                $('#ticket-list').html('<tr><td colspan="5" class="text-muted text-center">No tickets found.</td></tr>');
                return;
            }

            const rows = data.map(ticket => `
                <tr>
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

    function getStatusClass(status) {
        switch (status.toLowerCase()) {
            case 'pending': return 'warning';
            case 'resolved': return 'success';
            case 'in progress': return 'info';
            case 'closed': return 'secondary';
            default: return 'light';
        }
    }
</script>
</body>
</html>