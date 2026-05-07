<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Filterable Table</title>
    <style>
        input[type="text"] {
            width: 150px;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
    </style>
    <script>
        function fetchData() {
            var xhr = new XMLHttpRequest();
            var params = {
                customer: document.getElementById('customer').value,
                wono: document.getElementById('wono').value,
                ref: document.getElementById('ref').value,
                erp: document.getElementById('erp').value,
                icode: document.getElementById('icode').value,
                t_size: document.getElementById('t_size').value,
                brand: document.getElementById('brand').value,
                col: document.getElementById('col').value,
                fit: document.getElementById('fit').value,
                rim: document.getElementById('rim').value
            };
            var url = "worder_update2.php?" + new URLSearchParams(params).toString();
            xhr.open("GET", url, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    document.getElementById('table-container').innerHTML = xhr.responseText;
                }
            };
            xhr.send();
        }

        document.addEventListener('DOMContentLoaded', function() {
            var inputs = document.querySelectorAll('input[type="text"]');
            inputs.forEach(function(input) {
                input.addEventListener('input', fetchData);
            });
            fetchData(); // Initial fetch to display all data on page load
        });
    </script>
</head>
<body>
    <h2>Filterable Work Order Table</h2>
    <form>
        Customer: <input type="text" id="customer" name="customer">
        WO No: <input type="text" id="wono" name="wono">
        Reference: <input type="text" id="ref" name="ref">
        ERP: <input type="text" id="erp" name="erp">
        Item Code: <input type="text" id="icode" name="icode">
        Size: <input type="text" id="t_size" name="t_size">
        Brand: <input type="text" id="brand" name="brand">
        Color: <input type="text" id="col" name="col">
        Fit: <input type="text" id="fit" name="fit">
        Rim: <input type="text" id="rim" name="rim">
    </form>
    <div id="table-container"></div>
</body>
</html>
