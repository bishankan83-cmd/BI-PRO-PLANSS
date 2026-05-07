<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filter and Export Data</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 600px;
            margin-top: 20px;
        }

        body {
            font-family: 'Cantarell', sans-serif;
            background: url('atire3.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #000000;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.3);
            width: 400px;
        }

        h1 {
            font-weight: bold;
            font-size: 28px;
            font-family: 'Cantarell Bold', sans-serif;
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            text-align: left;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            font-weight: bold;
        }

        select, input[type="date"] {
            width: 91%;
            padding: 10px;
            border: 1px solid #000000;
            border-radius: 4px;
            font-family: 'Open Sans', sans-serif;
        }

        input[type="submit"] {
            background-color: #F28018;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
        }

        .form-group:last-child {
            margin-bottom: 0;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Filter and Export Data</h1>
    <form method="POST" action="export2.php">
        <div class="form-group">
            <label for="inputDate">Input Date:</label>
            <input type="date" name="inputDate" id="inputDate" class="form-control" onchange="fetchOptions()">
        </div>

        <div class="form-group">
            <label for="compound_name">Compound Name:</label>
            <select name="compound_name" id="compound_name" class="form-control" onchange="fetchBatchRange()">
                <option value="">Select Compound Name</option>
            </select>
        </div>

        <div class="form-group">
            <label for="serial_number">Job Number:</label>
            <select name="serial_number" id="serial_number" class="form-control" onchange="fetchBatchRange()">
                <option value="">Select Serial Number</option>
            </select>
        </div>

       

        <div class="form-group">
            <label for="batch_from">Batch Range (From):</label>
            <input type="text" name="batch_from" id="batch_from" class="form-control" placeholder="Batch Range (From)">
        </div>

        <div class="form-group">
            <label for="batch_to">Batch Range (To):</label>
            <input type="text" name="batch_to" id="batch_to" class="form-control" placeholder="Batch Range (To)">
        </div>

        <input type="hidden" name="export" value="true" /> <!-- Hidden export field -->
        <input type="submit" value="Filter and Export to Excel" class="btn btn-primary">
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
    function fetchOptions() {
        let inputDate = document.getElementById('inputDate').value;
        if (inputDate) {
            $.ajax({
                url: 'export2.php?fetch_options=true&inputDate=' + inputDate,
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    let compoundNameSelect = document.getElementById('compound_name');
                    compoundNameSelect.innerHTML = '<option value="">Select Compound Name</option>';
                    data.compound_name.forEach(function(name) {
                        compoundNameSelect.innerHTML += `<option value="${name}">${name}</option>`;
                    });

                    let serialNumberSelect = document.getElementById('serial_number');
                    serialNumberSelect.innerHTML = '<option value="">Select Serial Number</option>';
                    data.serial_number.forEach(function(number) {
                        serialNumberSelect.innerHTML += `<option value="${number}">${number}</option>`;
                    });

                    let palletSelect = document.getElementById('pallet');
                    palletSelect.innerHTML = '<option value="">Select Pallet</option>';
                    data.pallet.forEach(function(pallet) {
                        palletSelect.innerHTML += `<option value="${pallet}">${pallet}</option>`;
                    });
                }
            });
        }
    }

    function fetchBatchRange() {
        let inputDate = document.getElementById('inputDate').value;
        let compoundName = document.getElementById('compound_name').value;
        let serialNumber = document.getElementById('serial_number').value;

        if (inputDate || compoundName || serialNumber) {
            $.ajax({
                url: 'export2.php?fetch_batch_range=true&inputDate=' + inputDate + '&compound_name=' + compoundName + '&serial_number=' + serialNumber,
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    document.getElementById('batch_from').value = data.batch_from;
                    document.getElementById('batch_to').value = data.batch_to;
                }
            });
        }
    }
</script>

</body>
</html>
