<?php
	$conn=mysqli_connect("localhost", "planatir_task_managemen", "Bishan@1919", "planatir_task_managemen");
 
	if(!$conn){
		die(mysqli_error());
	}
?>

<table class="table table-bordered">
	<thead class="alert-info">
		<tr>
			<th>date</th>
			<th>pid</th>
			<th>Description</th>
            <th>Orders</th>
			<th>Stock</th>
			<th>To be produced</th>
		</tr>
	</thead>
	<tbody>
		<?php
			$query=mysqli_query($conn, "SELECT * FROM `compound_planning`  LEFT JOIN `torder` ON torder.pid = compound_planning.pid") or die(mysqli_error());
			while($fetch=mysqli_fetch_array($query)){
		?>
		<tr>
			<td><?php echo $fetch['date']?></td>
			<td><?php echo $fetch['pid']?></td>
			<td><?php echo $fetch['Description']?></td>
            <td><?php echo $fetch['corder']?></td>
            

		</tr>
		<?php
			}
		?>
	</tbody>
</table>
