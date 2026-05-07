<!DOCTYPE html>
<html>
<head>
    <style>
        /* Reset some default styles */
        body, ul, li {
            margin: 0;
            padding: 0;
            background-color: #f28018;
        }

        /* Define styles for the menu container */
        .menu-containerr {
            background-color: #f28018; /* Background color */
            color: #fff; /* Text color */
            weight:50%;
            font-family: 'Cantarell', sans-serif; /* Use Cantarell font or fallback to sans-serif */
        }

        .menu-header {
            background-color: #000000; /* Header background color */
            padding: 10px;
            display: flex;
            justify-content: space-between;
        }

        .menu-logo {
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .logo-image {
            width: 125px;
            height: 21.25px;
            margin-right: 10px;
        }

        .user-profile {
            display: flex;
            align-items: center;
        }

        .avatar img {
            width: 75px;
            height:75px;
            border-radius: 50%;
            margin-right: 5px;
        }

        .user-info {
            display: flex;
            flex-direction: column;
            color: #f28018; /* Text color for user info */
        }

        .user-name {
            font-weight: bold;
            font-size: 1.2em;
            font-family: 'Cantarell', sans-serif; /* Use Cantarell font or fallback to sans-serif */
        }

        .user-role {
            font-size: 1em;
            font-family: 'Cantarell', sans-serif; /* Use Cantarell font or fallback to sans-serif */
        }

        .menu-items {
            padding: 10px;
        }

        .main-menu {
            list-style: none;
        }

        .main-menu li {
            margin-bottom: 10px;
        }

        .main-menu a {
            text-decoration: none;
            color: #fff;
            display: inline-block;
            padding: 10px 20px;
            width: 200px; /* Fixed width for all buttons */
            height: 40px; /* Fixed height for all buttons */
            background-color: #000000; /* Button background color */
            border-radius: 50px; /* Rounded corners */
            font-weight: bold; /* Make text bold */
            text-align: center; /* Center the text */
            font-family: 'Cantarell', sans-serif; /* Use Cantarell font or fallback to sans-serif */
        }

        .main-menu a:hover {
            background-color: #f28018; /* Background color on hover */
        }

        /* Add a line separator between menu items */
        .menu-divider {
            border-top: 1px solid #f28018; /* Divider color */
            margin-top: 5px;
            margin-bottom: 5px;
        }

        /* Submenu Styles */
        .main-menu li {
            position: relative;
        }
        /* Change the color of the submenu button text */
.submenu li a {
    color: #000000; 
    background-color: #FFFFFF;/* Change the color of the submenu button text (can change this color) */
}

        .submenu {
            display: none;
           
            top: 0;
            left: 30%;
            
        }

        .submenu li::before {
    content: "●"; /* Use a bullet point character as content */
    color: #f28018; /* Color of the bullet point */
    display: inline-block;
    width: 15px; /* Adjust the width of the bullet point */
     /* Negative margin to position the bullet point */
}
        .main-menu li:hover .submenu {
            display: block;
        }


        
        .logout-button {
            background-color: #FFFFFF;
            color: #F28018;
            font-size: 18px;
            font-weight: bold;
            padding: 10px 20px;
            border: 2px solid #F28018;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .logout-button:hover {
            background-color: #F28018;
            color: #FFFFFF;
        }

    </style>
</head>
<body>
    <!--------------------
                START - Mobile Menu
                -------------------->

                <?php 
if ($_SESSION['User_type'] == "admin") {
    // Admin menu
?>
<div class="menu-containerr">
        <div class="menu-header">
            <a class="menu-logo" href="#">
                <img src="atire.png" alt="Your Logo" class="logo-image">
            </a>
            <div class="user-profile">
                <div class="avatar">
                    <img alt="" src="user_profile/<?php echo $_SESSION['emp_pro'];?>">adm
                </div>
                <div class="user-info">
                    <div class="user-name"><?php echo $_SESSION['emp_name'];?></div>
                    <div class="user-role"><?php echo $_SESSION['User_type'];?></div>

                    <div class="logout-container">
            <a href="logout.php" class="logout-button">Logout</a>
        </div>
                </div>
            </div>
        </div>
        <div class="menu-items">
            <h6 style="background-color: #000000; color: #fff; border-radius: 50px; padding: 3px; text-align: left; font-weight: bold; font-family: 'Cantarell', sans-serif;">Dashboard - Operational</h6>
            <ul class="main-menu">
       
                <!-- Topic 1: Work Order -->
                <li class="menu-divider">
                    <a href="#">Work Order</a>
                    <ul class="submenu">
                        <li><a href="add_workorder.php">Work order - New</a></li>
                        <li><a href="comparee.php">Work order - Verify</a></li>
                        <li><a href="workdelete.php">Work order - Remove</a></li>
                        <li><a href="import22bnew32.php">Pause Stock Orders</a></li>
                        <li><a href="stock_order_rep.php">Resume Stock Orders</a></li>
                        <li><a href="worder_rev_button.php">Work order - Revise</a></li>
                        <li><a href="add_work_order_hold.php">Work order - Hold</a></li>
                    </ul>
                </li>
                <!-- Topic 2: Plan Management -->
                <li class="menu-divider">
                    <a href="#">Production plan</a>
                    <ul class="submenu">
                    <li><a href="convertstock.php">Plan - Work order</a></li>
                        <li><a href="deleteplan.php">Plan - Remove</a></li>
                        <li><a href="select_cavity.php">Plan - Get Auto Cavity</a></li>
                        <li><a href="date_update12.php">Plan - Update</a></li>
                       
                        <li><a href="updatedate.php">Plan - Date Update</a></li>
                        <li><a href="time_range2.php">Plan - Shift Vise</a></li>
                        <li><a href="date_update.php">Plan - Date Change</a></li>
                    </ul>
                </li>
                <!-- Topic 3: Daily Production -->
                <li class="menu-divider">
                    <a href="#">Tires Input</a>
                    <ul class="submenu">
                        <li><a href="add_daily_production.php">Daily production</a></li>
                    </ul>
                </li>
                <!-- Topic 4: Dispatch -->
                <li class="menu-divider">
                    <a href="#">Tires Output -QA</a>
                    <ul class="submenu">
                        <li><a href="add_reject.php">Daily Reject</a></li>
                        <li><a href="add_rejectb.php">Daily B Grade</a></li>
                        <li><a href="">Daily Hold</a></li>
                    </ul>
                </li>
                <!-- Topic 5: Daily Reject -->
                <li class="menu-divider">
                    <a href="#">Tire Output - Sales
</a>
                    <ul class="submenu">
                        <li><a href="dispatch.php">Order Despatch</a></li>
                    </ul>
                </li>

                <li class="menu-divider">
                    <a href="#">SYSTEM</a>
                    <ul class="submenu">
                        <li><a href="get.php">Refresh System</a></li>
                       
                    </ul>
                </li>
                <!-- Add more topics and menu items as needed -->

                  <!-- New Button 1 -->
    
        
   
            </ul>
        </div>
        <!DOCTYPE html>
<html>
<head>
<style>
  .red-text {
    color:#f28018 ;
  }
  .blue-text {
    color:#f28018;
  }
  /* Add more styles as needed */
</style>
</head>
<body>
  <p class="red-text">ddddddddddddddddddd</p>
  <p class="red-text">ddddddddd</p>
  <p class="blue-text">ddddddd</p>
  <p class="red-text">dddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
</body>
</html>

    </div>

   
<?php
}
















elseif ($_SESSION['User_type'] == "Planning") {
    // Admin menu

}











elseif ($_SESSION['User_type'] == "Director") {
    // Admin menu
?>



<?php
}



elseif ($_SESSION['User_type'] == "General Manager") {
    // Admin menu
?>



<?php
}




elseif ($_SESSION['User_type'] == "MIX_MAN") {
    // Admin menu
?>
<div class="menu-containerr">
        <div class="menu-header">
            <a class="menu-logo" href="#">
                <img src="atire.png" alt="Your Logo" class="logo-image">
            </a>
            <div class="user-profile">
                <div class="avatar">
                    <img alt="" src="user_profile/<?php echo $_SESSION['emp_pro'];?>">
                </div>
                <div class="user-info">
                    <div class="user-name"><?php echo $_SESSION['emp_name'];?></div>
                    <div class="user-role"><?php echo $_SESSION['User_type'];?></div>
                </div>
            </div>
        </div>
        <div class="logout-container">
            <a href="logout.php" class="logout-button">Logout</a>
        </div>
        <div class="menu-items">
            <h6 style="background-color: #000000; color: #fff; border-radius: 50px; padding: 3px; text-align: left; font-weight: bold; font-family: 'Cantarell', sans-serif;">Dashboard - Operational</h6>
            <ul class="main-menu">
                <!-- Topic 1: Work Order -->
                <li class="menu-divider">
                    <li><a href="add_mixing.php">Add compound</a></li>
                    <li><a href="edit_mix2.php">Update compound</a></li>
                </li>
               
              
                
        </div>
        <!DOCTYPE html>
<html>
<head>
<style>
  .red-text {
    color:#f28018 ;
  }
  .blue-text {
    color:#f28018;
  }
  /* Add more styles as needed */
</style>
</head>
<body>
  <p class="red-text">ddddddddddddddddddd</p>
  <p class="red-text">ddddddddd</p>
  <p class="blue-text">ddddddd</p>
  <p class="red-text">dddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
</body>
</html>

    </div>





   
<?php
}


elseif ($_SESSION['User_type'] == "pro_sup") {
    // Admin menu
?>
<div class="menu-containerr">
        <div class="menu-header">
            <a class="menu-logo" href="#">
                <img src="atire.png" alt="Your Logo" class="logo-image">
            </a>
            <div class="user-profile">
                <div class="avatar">
                    <img alt="" src="user_profile/<?php echo $_SESSION['emp_pro'];?>">
                </div>
                <div class="user-info">
                    <div class="user-name"><?php echo $_SESSION['emp_name'];?></div>
                    <div class="user-role"><?php echo $_SESSION['User_type'];?></div>
                </div>
            </div>
        </div>
        <div class="logout-container">
            <a href="logout.php" class="logout-button">Logout</a>
        </div>
        <div class="menu-items">
            <h6 style="background-color: #000000; color: #fff; border-radius: 50px; padding: 3px; text-align: left; font-weight: bold; font-family: 'Cantarell', sans-serif;">Dashboard - Operational</h6>
            <ul class="main-menu">
                <!-- Topic 1: Work Order -->
                <li class="menu-divider">
                    <li><a href="supervisor/add_daily_production.php">Add Production</a></li>
         
                </li>
               
              
                
        </div>
        <!DOCTYPE html>
<html>
<head>
<style>
  .red-text {
    color:#f28018 ;
  }
  .blue-text {
    color:#f28018;
  }
  /* Add more styles as needed */
</style>
</head>
<body>
  <p class="red-text">ddddddddddddddddddd</p>
  <p class="red-text">ddddddddd</p>
  <p class="blue-text">ddddddd</p>
  <p class="red-text">dddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
</body>
</html>

    </div>





   
<?php
}



elseif ($_SESSION['User_type'] == "Planning") {
    // Admin menu
?>





<?php
}





elseif ($_SESSION['User_type'] == "uk") {
    // Admin menu
?>

        <!DOCTYPE html>
<html>
<head>
<style>
  .red-text {
    color:#f28018 ;
  }
  .blue-text {
    color:#f28018;
  }
  /* Add more styles as needed */
</style>
</head>

</html>

    </div>









<?php
}






elseif ($_SESSION['User_type'] == "mixing") {
    // Admin menu
?>
<div class="menu-containerr">
        <div class="menu-header">
            <a class="menu-logo" href="#">
                <img src="atire.png" alt="Your Logo" class="logo-image">
            </a>
            <div class="user-profile">
                <div class="avatar">
                    <img alt="" src="user_profile/<?php echo $_SESSION['emp_pro'];?>">
                </div>
                <div class="user-info">
                    <div class="user-name"><?php echo $_SESSION['emp_name'];?></div>
                    <div class="user-role"><?php echo $_SESSION['User_type'];?></div>
                </div>

            </div>
           
        </div>
        <div class="logout-container">
            <a href="logout.php" class="logout-button">Logout</a>
        </div>
        <div class="menu-items">
            <h6 style="background-color: #000000; color: #fff; border-radius: 50px; padding: 3px; text-align: left; font-weight: bold; font-family: 'Cantarell', sans-serif;">Dashboard - Operational</h6>
            <ul class="main-menu">
                <!-- Topic 1: Work Order -->
                <li class="menu-divider">
                    <a href="#">compound</a>
                    <ul class="submenu">
                        <li><a href="add_mixing.php">Add Compound</a></li>
                        <li><a href="compound_filter.php">Compound Issue</a></li>
                         <li><a href="edit_mix.php">Edit Compound</a></li>
                     
                       
                    </ul>

                    
                </li>


                
               
              
                
        </div>
        <!DOCTYPE html>
<html>
<head>
<style>
  .red-text {
    color:#f28018 ;
  }
  .blue-text {
    color:#f28018;
  }
  /* Add more styles as needed */
</style>
</head>
<body>
  <p class="red-text">ddddddddddddddddddd</p>
  <p class="red-text">ddddddddd</p>
  <p class="blue-text">ddddddd</p>
  <p class="red-text">dddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
</body>
</html>

    </div>





   
<?php
}






elseif ($_SESSION['User_type'] == "Source department") {
    // Admin menu
?>
<div class="menu-containerr">
        <div class="menu-header">
            <a class="menu-logo" href="#">
                <img src="atire.png" alt="Your Logo" class="logo-image">
            </a>
            <div class="user-profile">
                <div class="avatar">
                    <img alt="" src="user_profile/<?php echo $_SESSION['emp_pro'];?>">
                </div>
                <div class="user-info">
                    <div class="user-name"><?php echo $_SESSION['emp_name'];?></div>
                    <div class="user-role"><?php echo $_SESSION['User_type'];?></div>
                </div>

            </div>
           
        </div>
        <div class="logout-container">
            <a href="logout.php" class="logout-button">Logout</a>
        </div>
        <div class="menu-items">
            <h6 style="background-color: #000000; color: #fff; border-radius: 50px; padding: 3px; text-align: left; font-weight: bold; font-family: 'Cantarell', sans-serif;">Dashboard - Operational</h6>
            <ul class="main-menu">
                <!-- Topic 1: Work Order -->
                <li class="menu-divider">
                    <a href="#">Purchase Order</a>
                    <ul class="submenu">
                        <li><a href="po_add.php">Po</a></li>
                        <li><a href="insert_po_sup.php">Add Suplires</a></li>
                        <li><a href="po_export.php">Ex Excel(po & name) </a></li>
                        <li><a href="po_export2.php">Export Excel (full) </a></li>
                     
                       
                    </ul>

                    
                </li>


                 <!-- Topic 1: Work Order -->
                 <li class="menu-divider">
                    <a href="#">Loan Inward</a>
                    <ul class="submenu">
                        <li><a href="loan_inward.php">Add Inward</a></li>
                        <li><a href="insert_loan_inward_sup.php">Add Suplires</a></li>
                        
                     
                       
                    </ul>

                    
                </li>
               

                 <!-- Topic 1: Work Order -->
                 <li class="menu-divider">
                    <a href="#">Loan Outward</a>
                    <ul class="submenu">
                        <li><a href="loan_outward.php">Add outward</a></li>
                        <li><a href="insert_loan_outward_sup.php">Add Customer</a></li>
                        
                     
                       
                    </ul>

                    
                </li>
               
              
                
        </div>
        <!DOCTYPE html>
<html>
<head>
<style>
  .red-text {
    color:#f28018 ;
  }
  .blue-text {
    color:#f28018;
  }
  /* Add more styles as needed */
</style>
</head>
<body>
  <p class="red-text">ddddddddddddddddddd</p>
  <p class="red-text">ddddddddd</p>
  <p class="blue-text">ddddddd</p>
  <p class="red-text">dddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>

  <p class="red-text">dddddddddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  
  <p class="red-text">dddddddddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  
</body>
</html>

    </div>





   
<?php
}


elseif ($_SESSION['User_type'] == "rm stores") {
    // Admin menu
?>
<div class="menu-containerr">
        <div class="menu-header">
            <a class="menu-logo" href="#">
                <img src="atire.png" alt="Your Logo" class="logo-image">
            </a>
            <div class="user-profile">
                <div class="avatar">
                    <img alt="" src="user_profile/<?php echo $_SESSION['emp_pro'];?>">
                </div>
                <div class="user-info">
                    <div class="user-name"><?php echo $_SESSION['emp_name'];?></div>
                    <div class="user-role"><?php echo $_SESSION['User_type'];?></div>
                </div>

            </div>
           
        </div>
        <div class="logout-container">
            <a href="logout.php" class="logout-button">Logout</a>
        </div>
        <div class="menu-items">
            <h6 style="background-color: #000000; color: #fff; border-radius: 50px; padding: 3px; text-align: left; font-weight: bold; font-family: 'Cantarell', sans-serif;">Dashboard - Operational</h6>
            <ul class="main-menu">
                <!-- Topic 1: Work Order -->
                <li class="menu-divider">
                    <a href="#">purchase Order</a>
                    <ul class="submenu">
                        <li><a href="s_order_inward.php">Inward To Stock</a></li>
                       
                        
                     
                       
                    </ul>

                    
                </li>


                 <!-- Topic 1: Work Order -->
                 <li class="menu-divider">
                    <a href="#">Loan Inward</a>
                    <ul class="submenu">
                        <li><a href="l_order_inward.php">Loan Inward Stock</a></li>
                       
                        
                     
                       
                    </ul>

                    
                </li>
               

                 <!-- Topic 1: Work Order -->
                 <li class="menu-divider">
                    <a href="#">Loan Outward</a>
                    <ul class="submenu">
                        <li><a href="l_order_outward.php">Add outward</a></li>
                        
                        
                     
                       
                    </ul>

                    
                </li>


                 <!-- Topic 1: Work Order -->
                 <li class="menu-divider">
                    <a href="#">Material Issue</a>
                    <ul class="submenu">
                        <li><a href="search_mrn.php">Material Issue</a></li>
                        
                        
                     
                       
                    </ul>

                    
                </li>
                 <!-- Topic 1: Work Order -->
                 <li class="menu-divider">
                    <a href="#">Loan Settlment</a>
                    <ul class="submenu">
                        <li><a href="loan_settlement.php">Settle Loan</a></li>
                        
                        
                     
                       
                    </ul>

                    
                </li>


                </li>
                 <!-- Topic 1: Work Order -->
                 <li class="menu-divider">
                    <a href="#">Loan Given Settlment</a>
                    <ul class="submenu">
                        <li><a href="loan_given_settlement.php">Settle Given Loan</a></li>
                        
                        
                     
                       
                    </ul>

                    
                </li>

                <li class="menu-divider">
                    <a href="#">Edit Data</a>
                    <ul class="submenu">
                        <li><a href="edit_data/rmband">Update RM data</a></li>
                        
                        
                     
                       
                    </ul>

                    
                </li>
               
               
              
                
        </div>
        <!DOCTYPE html>
<html>
<head>
<style>
  .red-text {
    color:#f28018 ;
  }
  .blue-text {
    color:#f28018;
  }
  /* Add more styles as needed */
</style>
</head>
<body>
  <p class="red-text">ddddddddddddddddddd</p>
  <p class="red-text">ddddddddd</p>
  <p class="blue-text">ddddddd</p>
  <p class="red-text">dddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
</body>
</html>

    </div>





   
<?php
}





elseif ($_SESSION['User_type'] == "mixing2") {
    // Admin menu
?>
<div class="menu-containerr">
        <div class="menu-header">
            <a class="menu-logo" href="#">
                <img src="atire.png" alt="Your Logo" class="logo-image">
            </a>
            <div class="user-profile">
                <div class="avatar">
                    <img alt="" src="user_profile/<?php echo $_SESSION['emp_pro'];?>">
                </div>
                <div class="user-info">
                    <div class="user-name"><?php echo $_SESSION['emp_name'];?></div>
                    <div class="user-role"><?php echo $_SESSION['User_type'];?></div>
                </div>
                
            </div>

          
        </div>
        <div class="logout-container">
            <a href="logout.php" class="logout-button">Logout</a>
        </div>
        <div class="menu-items">
            <h6 style="background-color: #000000; color: #fff; border-radius: 50px; padding: 3px; text-align: left; font-weight: bold; font-family: 'Cantarell', sans-serif;">Dashboard - Operational</h6>
            <ul class="main-menu">
                <!-- Topic 1: Work Order -->
                <li class="menu-divider">
                    <a href="#">compound</a>
                    <ul class="submenu">
                        <li><a href="compound_filter.php">Compound Issue</a></li>
                     
                       
                    </ul>
                     <li class="menu-divider">
                    <li><a href="add_mixing.php">Add compound</a></li>
                    
                </li>
<li><a href="edit_mix.php">Edit Compound</a></li>
                     
                    
                </li>
               
              
                
        </div>
        <!DOCTYPE html>
<html>
<head>
<style>
  .red-text {
    color:#f28018 ;
  }
  .blue-text {
    color:#f28018;
  }
  /* Add more styles as needed */
</style>
</head>
<body>
  <p class="red-text">ddddddddddddddddddd</p>
  <p class="red-text">ddddddddd</p>
  <p class="blue-text">ddddddd</p>
  <p class="red-text">dddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
</body>
</html>

    </div>





   
<?php
}











elseif ($_SESSION['User_type'] == "lab") {
    // Admin menu
?>
<div class="menu-containerr">
        <div class="menu-header">
            <a class="menu-logo" href="#">
                <img src="atire.png" alt="Your Logo" class="logo-image">
            </a>
            <div class="user-profile">
                <div class="avatar">
                    <img alt="" src="user_profile/<?php echo $_SESSION['emp_pro'];?>">
                </div>
                <div class="user-info">
                    <div class="user-name"><?php echo $_SESSION['emp_name'];?></div>
                    <div class="user-role"><?php echo $_SESSION['User_type'];?></div>
                </div>
            </div>
            
        </div>

        <div class="logout-container">
            <a href="logout.php" class="logout-button">Logout</a>
        </div>
        <div class="menu-items">
            <h6 style="background-color: #000000; color: #fff; border-radius: 50px; padding: 3px; text-align: left; font-weight: bold; font-family: 'Cantarell', sans-serif;">Dashboard - Operational</h6>
            <ul class="main-menu">
                <!-- Topic 1: Work Order -->
                <li class="menu-divider">
                    <a href="#">compound</a>
                    <ul class="submenu">
                        <li><a href="lab.php">Create QR</a></li>
                        <li><a href="lab_re.php">Re Create QR</a></li>
                      
                        <li><a href="confirm_lab_re.php">Confirm Re Create QR</a></li>
                    </ul>
                </li>
                <li class="menu-divider">
                    <a href="#">Report</a>
                    <ul class="submenu">
                        <li><a href="import_lab.php">Import Test Report</a></li>
                     
                       
                    </ul>
                </li>

                <li class="menu-divider">
                    <a href="#">ReWork</a>
                    <ul class="submenu">
                        <li><a href="rebatch.php">ReWork QR</a></li>
                     
                       
                    </ul>
                </li>
              
                
        </div>
        <!DOCTYPE html>
<html>
<head>
<style>
  .red-text {
    color:#f28018 ;
  }
  .blue-text {
    color:#f28018;
  }
  /* Add more styles as needed */
</style>
</head>
<body>
  <p class="red-text">ddddddddddddddddddd</p>
  <p class="red-text">ddddddddd</p>
  <p class="blue-text">ddddddd</p>
  <p class="red-text">dddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
</body>
</html>

    </div>

   
<?php
}







    elseif ($_SESSION['User_type'] == "lab_ndr") {
    // Admin menu
?>
<div class="menu-containerr">
        <div class="menu-header">
            <a class="menu-logo" href="#">
                <img src="atire.png" alt="Your Logo" class="logo-image">
            </a>
            <div class="user-profile">
                <div class="avatar">
                    <img alt="" src="user_profile/<?php echo $_SESSION['emp_pro'];?>">
                </div>
                <div class="user-info">
                    <div class="user-name"><?php echo $_SESSION['emp_name'];?></div>
                    <div class="user-role"><?php echo $_SESSION['User_type'];?></div>
                </div>
            </div>
            
        </div>

        <div class="logout-container">
            <a href="logout.php" class="logout-button">Logout</a>
        </div>
        <div class="menu-items">
            <h6 style="background-color: #000000; color: #fff; border-radius: 50px; padding: 3px; text-align: left; font-weight: bold; font-family: 'Cantarell', sans-serif;">Dashboard - Operational</h6>
            <ul class="main-menu">
                <!-- Topic 1: Work Order -->
                <li class="menu-divider">
                    <a href="#">compound</a>
                    <ul class="submenu">
                        <li><a href="lab_b.php">Create QR</a></li>
                        <li><a href="lab_re.php">Re Create QR</a></li>
                      
                        <li><a href="confirm_lab_re.php">Confirm Re Create QR</a></li>
                    </ul>
                </li>
                <li class="menu-divider">
                    <a href="#">Report</a>
                    <ul class="submenu">
                        <li><a href="import_labb.php">Import Test Report</a></li>
                     
                       
                    </ul>
                </li>

                <li class="menu-divider">
                    <a href="#">ReWork</a>
                    <ul class="submenu">
                        <li><a href="rebatch.php">ReWork QR</a></li>
                     
                       
                    </ul>
                </li>
              
                
        </div>
        <!DOCTYPE html>
<html>
<head>
<style>
  .red-text {
    color:#f28018 ;
  }
  .blue-text {
    color:#f28018;
  }
  /* Add more styles as needed */
</style>
</head>
<body>
  <p class="red-text">ddddddddddddddddddd</p>
  <p class="red-text">ddddddddd</p>
  <p class="blue-text">ddddddd</p>
  <p class="red-text">dddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
</body>
</html>

    </div>





   
<?php
}


elseif ($_SESSION['User_type'] == "Erp_user") {
    // Admin menu
?>
<div class="menu-containerr">
        <div class="menu-header">
            <a class="menu-logo" href="#">
                <img src="atire.png" alt="Your Logo" class="logo-image">
            </a>
            <div class="user-profile">
                <div class="avatar">
                    <img alt="" src="user_profile/<?php echo $_SESSION['emp_pro'];?>">
                </div>
                <div class="user-info">
                    <div class="user-name"><?php echo $_SESSION['emp_name'];?></div>
                    <div class="user-role"><?php echo $_SESSION['User_type'];?></div>
                </div>
            </div>
            
        </div>

        <div class="logout-container">
            <a href="logout.php" class="logout-button">Logout</a>
        </div>
        <div class="menu-items">
            <h6 style="background-color: #000000; color: #fff; border-radius: 50px; padding: 3px; text-align: left; font-weight: bold; font-family: 'Cantarell', sans-serif;">Dashboard - Operational</h6>
            <ul class="main-menu">
                <!-- Topic 1: Work Order -->
                <li class="menu-divider">
                    <a href="#">QR</a>
                    <ul class="submenu">
                        <li><a href="sticker3.php">Create QR</a></li>
                        <li><a href="sticker3_t.php">Create Test Tire QR</a></li>
                    </ul>
                </li>
                

              
                
        </div>
        <!DOCTYPE html>
<html>
<head>
<style>
  .red-text {
    color:#f28018 ;
  }
  .blue-text {
    color:#f28018;
  }
  /* Add more styles as needed */
</style>
</head>
<body>
  <p class="red-text">ddddddddddddddddddd</p>
  <p class="red-text">ddddddddd</p>
  <p class="blue-text">ddddddd</p>
  <p class="red-text">dddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
</body>
</html>

    </div>





   
<?php
}




elseif ($_SESSION['User_type'] == "mold") {
   
?>



<div class="menu-containerr">
        <div class="menu-header">
            <a class="menu-logo" href="#">
                <img src="atire.png" alt="Your Logo" class="logo-image">
            </a>
            <div class="user-profile">
                <div class="avatar">
                    <img alt="" src="user_profile/<?php echo $_SESSION['emp_pro'];?>">
                </div>
                <div class="user-info">
                    <div class="user-name"><?php echo $_SESSION['emp_name'];?></div>
                    <div class="user-role"><?php echo $_SESSION['User_type'];?></div>
               
                 
                    <button class="logout-btn" onclick="logout()">Logout</button>
                </div>
            </div>
        </div>
        <div class="menu-items">
            <h6 style="background-color: #000000; color: #fff; border-radius: 50px; padding: 3px; text-align: left; font-weight: bold; font-family: 'Cantarell', sans-serif;">Dashboard - Operational</h6>
            <ul class="main-menu">
                <!-- Topic 1: Work Order -->
                <li class="menu-divider">
                    <a href="#">ADD </a>
                    <ul class="submenu">
                    <ul class="submenu">
                        <li><a href="enter_mold.php">Add New Mold</a></li>
                        <li><a href="enter_tire_mold.php">Add New Tire Mold</a></li>
                    </ul>
   
            </ul>

            <li class="menu-divider">
                    <a href="#">Update </a>
                    <ul class="submenu">
                    <ul class="submenu">
                  
                        <li><a href="update_mold.php">Update Tire Mold</a></li>
                    </ul>
   
        </div>

        
            
           
        <!DOCTYPE html>
<html>
<head>
<style>
  .red-text {
    color:#f28018 ;
  }
  .blue-text {
    color:#f28018;
  }
  /* Add more styles as needed */
</style>
</head>
<body>
  <p class="red-text">ddddddddddddddddddd</p>
  <p class="red-text">ddddddddd</p>
  <p class="blue-text">ddddddd</p>
  <p class="red-text">dddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  <p class="blue-text">ddddddd</p>
  <p class="red-text">dddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  <p class="blue-text">ddddddd</p>
  <p class="blue-text">ddddddd</p>
  <p class="red-text">dddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  <p class="blue-text">ddddddd</p>
  <p class="red-text">dddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
 
</body>
</html>

    </div>

<div class="menu-container">
    <!-- Customize this part for other_user_type -->
</div>
<?php
} 




elseif ($_SESSION['User_type'] == "fmanager") {
   
    ?>
    
    
    
    <div class="menu-containerr">
            <div class="menu-header">
                <a class="menu-logo" href="#">
                    <img src="atire.png" alt="Your Logo" class="logo-image">
                </a>
                <div class="user-profile">
                    <div class="avatar">
                        <img alt="" src="user_profile/<?php echo $_SESSION['emp_pro'];?>">
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?php echo $_SESSION['emp_name'];?></div>
                        <div class="user-role"><?php echo $_SESSION['User_type'];?></div>

            <a href="logout.php" class="logout-button">Logout</a>
        
                    </div>
                </div>
            </div>
            <div class="menu-items">
                <h6 style="background-color: #000000; color: #fff; border-radius: 50px; padding: 3px; text-align: left; font-weight: bold; font-family: 'Cantarell', sans-serif;">Dashboard - Operational</h6>
                <ul class="main-menu">
                    <!-- Topic 1: Work Order -->
                    <li class="menu-divider">
                        <a href="#">Tire input</a>
                        <ul class="submenu">
                        <ul class="submenu">
                            <li><a href="confirm_daily.php">Confirm Production</a></li>
                        </ul>
       
                </ul>
            </div>
            <!DOCTYPE html>
    <html>
    <head>
    <style>
      .red-text {
        color:#f28018 ;
      }
      .blue-text {
        color:#f28018;
      }
      /* Add more styles as needed */
    </style>
    </head>
    <body>
      <p class="red-text">ddddddddddddddddddd</p>
      <p class="red-text">ddddddddd</p>
      <p class="blue-text">ddddddd</p>
      <p class="red-text">dddddddddddd</p>
      <p class="red-text">dddddddddddddddddd</p>
      <p class="red-text">dddddddddddddddddd</p>
      <p class="red-text">dddddddddddddddddd</p>
      <p class="red-text">dddddddddddddddddd</p>
      <p class="red-text">ddddddddddddddddddd</p>
      <p class="red-text">ddddddddd</p>
      <p class="blue-text">ddddddd</p>
      <p class="red-text">dddddddddddd</p>
      <p class="red-text">dddddddddddddddddd</p>
      <p class="red-text">dddddddddddddddddd</p>
      <p class="red-text">dddddddddddddddddd</p>
      <p class="red-text">dddddddddddddddddd</p>
      <p class="red-text">ddddddddddddddddddd</p>
      <p class="red-text">ddddddddd</p>
      <p class="blue-text">ddddddd</p>
      <p class="red-text">dddddddddddd</p>
      <p class="red-text">dddddddddddddddddd</p>
      <p class="red-text">dddddddddddddddddd</p>
      <p class="red-text">dddddddddddddddddd</p>
      <p class="red-text">dddddddddddddddddd</p>
    </body>
    </html>
    
        </div>
    
    <div class="menu-container">
        <!-- Customize this part for other_user_type -->
    </div>
    <?php
    } 








elseif ($_SESSION['User_type'] == "qmanager") {
   



    
    ?>
    
    
    
    <div class="menu-containerr">
        <div class="menu-header">
          
            <div class="user-profile">
                <div class="avatar">
                    <img alt="" src="user_profile/<?php echo $_SESSION['emp_pro'];?>">
                </div>
              <div class="user-info">
                        <div class="user-name"><?php echo $_SESSION['emp_name'];?></div>
                        <div class="user-role"><?php echo $_SESSION['User_type'];?></div>
                        <a href="logout.php" class="logout-button">Logout</a>
                    </div>
            </div>
        </div>
        <div class="menu-items">
            <h6 style="background-color: #000000; color: #fff; border-radius: 50px; padding: 3px; text-align: left; font-weight: bold; font-family: 'Cantarell', sans-serif;">Dashboard - Operational</h6>
            <ul class="main-menu">
             
                <!-- Topic 4: Dispatch -->
                <li class="menu-divider">
                    <a href="#">Tires Output -QA</a>
                    <ul class="submenu">
                        <li><a href="showdaily3.php">Confirm Daily Reject</a></li>
                        
                    </ul>
                </li>
                   <!-- Topic 4: Dispatch -->
                   
                
               
                
                <li class="menu-divider">
                    <a href="#">BOM</a>
                    <ul class="submenu">
                        <li><a href="update_bom.php">Update Bom</a></li>
                       
                    </ul>
                </li>

               
   
            </ul>
        </div>


        
        <!DOCTYPE html>
<html>
<head>
<style>
  .red-text {
    color:#f28018 ;
  }
  .blue-text {
    color:#f28018;
  }
  /* Add more styles as needed */
</style>
</head>
<body>

</body>
</html>

    </div>

   
<?php
    }


    elseif ($_SESSION['User_type'] == "stock") {
   
        ?>
        
        
        
        <div class="menu-containerr">
            <div class="menu-header">
                <a class="menu-logo" href="#">
                    <img src="atire.png" alt="Your Logo" class="logo-image">
                </a>
                <div class="user-profile">
                    <div class="avatar">
                        <img alt="" src="user_profile/<?php echo $_SESSION['emp_pro'];?>">
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?php echo $_SESSION['emp_name'];?></div>
                        <div class="user-role"><?php echo $_SESSION['User_type'];?></div>
                        
            <a href="logout.php" class="logout-button">Logout</a>
            
                    </div>
                </div>
            </div>
            <div class="menu-items">
                <h6 style="background-color: #000000; color: #fff; border-radius: 50px; padding: 3px; text-align: left; font-weight: bold; font-family: 'Cantarell', sans-serif;">Dashboard - Operational</h6>
                <ul class="main-menu">
                 
                <li class="menu-divider">
                    <a href="#">Tire Output - Sales
</a>
                    <ul class="submenu">
                        <li><a href="dispatch.php">Order Despatch</a></li>
                       <li><a href="dispatch_order_serial.php">Order Despatch Serial</a></li> 
                        <li><a href="stockworder.php">Work Order</a></li>
                    </ul>
                </li>
       
                <li class="menu-divider">
                    <a href="#">Stock
</a>
                    <ul class="submenu">
                          <li><a href="new_tire_transfer.php">Tire Transfer </a></li>
                        
                        <li><a href="stockb_change.php">B GARDE STOCK UPDATE</a></li>
                     
                        <li><a href="stock_erp_excel.php">Daily Stock Transfer Excel </a></li>
                        <li><a href="select_erp_stock.php">Daily Stock Transfer Selected </a></li>
                    </ul>
                </li>
       

                </ul>
                
                 
                
            </div>
            <!DOCTYPE html>
    <html>
    <head>
    <style>
      .red-text {
        color:#f28018 ;
      }
      .blue-text {
        color:#f28018;
      }
      /* Add more styles as needed */
    </style>
    </head>
    <body>
      <p class="red-text">ddddddddddddddddddd</p>
      <p class="red-text">ddddddddd</p>
      <p class="blue-text">ddddddd</p>
      <p class="red-text">dddddddddddd</p>
      <p class="red-text">dddddddddddddddddd</p>
      <p class="red-text">dddddddddddddddddd</p>
      <p class="red-text">dddddddddddddddddd</p>
      <p class="red-text">dddddddddddddddddd</p>
    </body>
    </html>
    
        </div>
    
       
    <?php
        }
    elseif ($_SESSION['User_type'] == "qad") {
   
        ?>
        
        
        
        <div class="menu-containerr">
            <div class="menu-header">
                <a class="menu-logo" href="#">
                    <img src="atire.png" alt="Your Logo" class="logo-image">
                </a>
                <div class="user-profile">
                    <div class="avatar">
                        <img alt="" src="user_profile/<?php echo $_SESSION['emp_pro'];?>">
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?php echo $_SESSION['emp_name'];?></div>
                        <div class="user-role"><?php echo $_SESSION['User_type'];?></div>
                        <a href="logout.php" class="logout-button">Logout</a>
                    </div>
                </div>
            </div>
           
            <div class="menu-items">
                <h6 style="background-color: #000000; color: #fff; border-radius: 50px; padding: 3px; text-align: left; font-weight: bold; font-family: 'Cantarell', sans-serif;">Dashboard - Operational</h6>
                <ul class="main-menu">
                <li class="menu-divider">
                    <a href="#">Tires Output -QA</a>
                    <ul class="submenu">
                        <li><a href="add_reject.php">Daily Reject</a></li>
                        <li><a href="add_rejectb.php">Daily B Grade</a></li>
                        <li><a href="add_hold.php">Daily Hold</a></li>
                        <li><a href="add_cut.php">Cross Cut</a></li>
                        <li><a href="add_sample.php">Test Tire</a></li>
                    </ul>
                </li>

                <li class="menu-divider">
                    <a href="#">Pending - Reject</a>
                    <ul class="submenu">
                        <li><a href="pending_reject.php">Daily Pending Reject</a></li>
                       
                    </ul>
                </li>

                <li class="menu-divider">
                    <a href="#">BOM</a>
                    <ul class="submenu">
                        <li><a href="update_bom.php">Update Bom</a></li>
                       
                    </ul>
                </li>


                <li class="menu-divider">
                    <a href="#">STOCK</a>
                    <ul class="submenu">
                        <li><a href="stock_erp.php">ADD STOCK</a></li>
                       
                    </ul>
                </li>
                   
                   
       
                </ul>
            </div>
            <!DOCTYPE html>
    <html>
    <head>
    <style>
      .red-text {
        color:#f28018 ;
      }
      .blue-text {
        color:#f28018;
      }
      /* Add more styles as needed */
    </style>
    </head>
    <body>
 
    </body>
    </html>
    
        </div>
    
       
    <?php
        }

        elseif ($_SESSION['User_type'] == "production") {
    // Default menu for users who don't match any specific user type
?>

<div class="menu-containerr">

  
<div class="menu-header">
    <a class="menu-logo" href="#">
        <img src="atire.png" alt="Your Logo" class="logo-image">
    </a>
    <div class="user-profile">
        <div class="avatar">
            <img alt="" src="user_profile/<?php echo $_SESSION['emp_pro'];?>">
        </div>
        <div class="user-info">
            <div class="user-name"><?php echo $_SESSION['emp_name'];?></div>
            <div class="user-role"><?php echo $_SESSION['User_type'];?></div>
        </div>
        <div class="logout-container">
            <a href="logout.php" class="logout-button">Logout</a>
        </div>
    </div>
</div>

                    

        <div class="menu-items">
            <h6 style="background-color: #000000; color: #fff; border-radius: 50px; padding: 3px; text-align: left; font-weight: bold; font-family: 'Cantarell', sans-serif;">Dashboard - Operational</h6>
            <ul class="main-menu">
                <!-- Topic 1: Work Order -->
                <li class="menu-divider">
                    <a href="#">Tire input</a>
                    
                    <ul class="submenu">
                        <li><a href="add_daily_production.php">Daily production</a></li>
                        <li><a href="serial_production.php">Daily production Serial</a></li>
                    </ul>

                
                </li>

                <li class="menu-divider">
                    <a href="#">Compound Input</a>
                    <ul class="submenu">
                        <li><a href="pcompound_filter.php">Daily Production</a></li>
                       
                    </ul>
                </li>

                <li class="menu-divider">
                    <a href="#">Compound OutPut</a>
                    <ul class="submenu">
                        <li><a href="p2compound_filter.php">Daily Production</a></li>
                       
                    </ul>
                </li>
                   

                <li class="menu-divider">
                    <a href="#"> Materials                     </a>
                    <ul class="submenu">
                        <li><a href="insert_material_request.php">Materials Request                        </a></li>
                       
                    </ul>
                </li>

                <li class="menu-divider">
                    <a href="#"> Supervisor                     </a>
                    <ul class="submenu">
                        <li><a href="add_plan_sup.php">Add Daily Plan Supervisor                      </a></li>
                       
                    </ul>
                </li>

   
            </ul>


            
        </div>
        <!DOCTYPE html>
<html>
<head>
<style>
  .red-text {
    color:#f28018 ;
  }
  .blue-text {
    color:#f28018;
  }
  /* Add more styles as needed */
</style>
</head>

    </div>

   
<div class="menu-container">
    <!-- Customize this part for the default menu -->
</div>
<?php
}
?>









