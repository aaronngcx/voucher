<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Vouchers</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 50px;
        }
        #loading-spinner {
            display: none;
            border: 4px solid #f3f3f3;
            border-radius: 50%;
            border-top: 4px solid #3498db;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        a:hover {
            background-color: #2980b9;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <h1>Voucher Generation</h1>
    <p id="status-message">Generating vouchers... please wait.</p>
    <div id="loading-spinner"></div>
    <a id="download-link" href="#" class="hidden">Download CSV</a>

    <script>
        $(document).ready(function() {
            $('#loading-spinner').show();
            checkCsvStatus();
        });

        function checkCsvStatus() {
            $.get('/check-csv-status', function(data) {
                if (data.download_url) {
                    $('#download-link').attr('href', data.download_url).removeClass('hidden');
                    $('#status-message').text('Your CSV is ready! Click the link to download.');
                    $('#loading-spinner').hide();
                } else {
                    setTimeout(checkCsvStatus, 10000);
                }
            }).fail(function() {
                $('#status-message').text('Error checking CSV status. Retrying...');
                setTimeout(checkCsvStatus, 5000);
            });
        }
    </script>
</body>
</html>
