<!DOCTYPE html>
<html>
<head>
    <title>PHP Table Example</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }

        th, td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
<form method="post" action="process_form.php"> <!-- Create a form to submit data to a PHP script -->
    <table>
        <tr>
            <th>Item</th>
            <th>Icode</th>
            <th>Tire size</th>
            <th>Item Description</th>
            <th>B-ATS 15</th>
            <th>B-BNS 24</th>
            <th>BG-BLS 12</th>
            <th>CG - BS 901</th>
            <th>C - SMS 501</th>
            <th>C-ATS 20</th>
            <th>C-SMS 702</th>
            <th>T - TRS 102</th>
            <th>T-ATNM S</th>
            <th>T-ATS 30</th>
            <th>T-ATS 35</th>
            <th>T-KS 40</th>
            <th>T-TRNMS 402</th>
            <th>T-TRNMS 402G</th>
            <th>T-TRS 202</th>
            <th>Grand Totalcompound weight</th>
            <th>Color</th>
            <th>Brand</th>
            <th>Green Tire weight</th>
        </tr>
        <!-- You can add rows with input fields for the user to fill -->
        <!-- You can add rows with input fields for the user to fill -->
<tr>
    <td><input type="text" name="item[]"></td>
    <td><input type="text" name="icode[]"></td>
    <td><input type="text" name="t_size[]"></td>
    <td><input type="text" name="item_description[]"></td>
    <td><input type="text" name="b_ats_15[]"></td> <!-- Added input field for B-ATS 15 -->
    <td><input type="text" name="b_bns_24[]"></td> <!-- Added input field for B-BNS 24 -->
    <td><input type="text" name="bg_bls_12[]"></td> <!-- Added input field for BG-BLS 12 -->
    <td><input type="text" name="cg_bs_901[]"></td> <!-- Added input field for CG - BS 901 -->
    <td><input type="text" name="c_sms_501[]"></td> <!-- Added input field for C - SMS 501 -->
    <td><input type="text" name="c_ats_20[]"></td> <!-- Added input field for C-ATS 20 -->
    <td><input type="text" name="c_sms_702[]"></td> <!-- Added input field for C-SMS 702 -->
    <td><input type="text" name="t_trs_102[]"></td> <!-- Added input field for T - TRS 102 -->
    <td><input type="text" name="t_atnm_s[]"></td> <!-- Added input field for T-ATNM S -->
    <td><input type="text" name="t_ats_30[]"></td> <!-- Added input field for T-ATS 30 -->
    <td><input type="text" name="t_ats_35[]"></td> <!-- Added input field for T-ATS 35 -->
    <td><input type="text" name="t_ks_40[]"></td> <!-- Added input field for T-KS 40 -->
    <td><input type="text" name="t_trnms_402[]"></td> <!-- Added input field for T-TRNMS 402 -->
    <td><input type="text" name="t_trnms_402g[]"></td> <!-- Added input field for T-TRNMS 402G -->
    <td><input type="text" name="t_trs_202[]"></td> <!-- Added input field for T-TRS 202 -->
    <td><input type="text" name="grand_total_compound_weight[]"></td> <!-- Added input field for Grand Totalcompound weight -->
    <td><input type="text" name="color[]"></td> <!-- Added input field for Color -->
    <td><input type="text" name="brand[]"></td> <!-- Added input field for Brand -->
    <td><input type="text" name="green_tire_weight[]"></td> <!-- Added input field for Green Tire weight -->
</tr>
<!-- You can add more rows as needed -->

        <!-- You can add more rows as needed -->
    </table>
    <input type="submit" value="Submit">
</form>
</body>
</html>
