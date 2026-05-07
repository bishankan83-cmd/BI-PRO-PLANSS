<marquee direction="left" style="background: #000000;">
            <span class="breadcrumb-item">
            <img src="atire.png" alt="Logo" style="height: 50px; margin-right: 20px;">
                <?php
                $qry = mysqli_query($connection, "SELECT * FROM news_and_update where news_type='alert' order by created desc") or die("select query fail" . mysqli_error());
                while ($row = mysqli_fetch_assoc($qry)) {
                    $news_title = $row['news_title'];
                    ?>
                    <a href="#" style="color:#f28018; font-size: 18px;"><?php echo $news_title; ?>&nbsp;<strong></strong></a>
                <?php } ?>
               
            </span>
        </marquee>