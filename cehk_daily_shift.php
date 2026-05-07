<form method="post" action="plan_report.php">
    <label for="date">Select Date:</label>
    <input type="date" name="date" id="date" required>

    <label for="shift">Select Shift:</label>
    <select name="shift" id="shift" required>
        <option value="Shift A">Shift A</option>
        <option value="Shift B">Shift B</option>
        <option value="Shift C">Shift C</option>
    </select>

    <input type="submit" name="filter_data" value="Filter Data">
</form>
