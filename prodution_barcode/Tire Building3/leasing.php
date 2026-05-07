<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Leasing Calculator</title>
  <style>
    body { font-family: Arial; padding: 20px; max-width: 500px; margin: auto; }
    label, input { display: block; width: 100%; margin-bottom: 10px; }
    input[type="number"] { padding: 5px; }
    button { padding: 10px 15px; margin-top: 10px; }
    .result { margin-top: 20px; font-weight: bold; }
  </style>
</head>
<body>
  <h2>Leasing Calculator</h2>
  
  <label>Vehicle Price ($)
    <input type="number" id="price" placeholder="e.g., 30000">
  </label>

  <label>Down Payment ($)
    <input type="number" id="down" placeholder="e.g., 3000">
  </label>

  <label>Interest Rate (% annual)
    <input type="number" step="0.01" id="interest" placeholder="e.g., 3.5">
  </label>

  <label>Lease Term (months)
    <input type="number" id="term" placeholder="e.g., 36">
  </label>

  <label>Residual Value ($)
    <input type="number" id="residual" placeholder="e.g., 15000">
  </label>

  <button onclick="calculateLease()">Calculate Lease</button>

  <div class="result" id="monthlyPayment"></div>

  <script>
    function calculateLease() {
      const price = parseFloat(document.getElementById('price').value);
      const down = parseFloat(document.getElementById('down').value);
      const interest = parseFloat(document.getElementById('interest').value) / 100 / 12;
      const term = parseFloat(document.getElementById('term').value);
      const residual = parseFloat(document.getElementById('residual').value);

      const capCost = price - down;
      const depreciation = (capCost - residual) / term;
      const financeCharge = (capCost + residual) * interest;
      const monthly = depreciation + financeCharge;

      document.getElementById('monthlyPayment').innerText =
        `Estimated Monthly Lease Payment: $${monthly.toFixed(2)}`;
    }
  </script>
</body>
</html>
