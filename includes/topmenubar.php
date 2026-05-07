<!--------------------
START - Top Bar
-------------------->
<div class="top-bar color-scheme-transparent">
    <!--------------------
    START - Top Menu Controls
    -------------------->
    <div class="top-menu-controls">
        <!--<div class="element-search autosuggest-search-activator"><input placeholder="Start typing to search..." type="text"></div>-->
        <div class="logged-user-w">
            <div class="logged-user-i">
                <div class="avatar-w"><img alt="" src="user_profile/<?php echo $_SESSION['emp_pro'];?>"></div>
                <div class="logged-user-menu color-style-bright">
                    <div class="logged-user-avatar-info">
                      <div class="avatar-w"><img alt="" src="user_profile/<?php echo $_SESSION['emp_pro'];?>"></div>
                        <div class="logged-user-info-w">
                              <div class="logged-user-name"><?php echo $_SESSION['emp_name'];?></div>
                                <div class="logged-user-role"><?php echo $_SESSION['User_type'];?></div>
                        </div>
                    </div>
                    <div class="bg-icon"><i class="os-icon os-icon-wallet-loaded"></i></div>
                    <ul>
                         <li><a href="update_emp_profile.php"><i class="os-icon os-icon-user-male-circle2"></i><span>Profile Details</span></a></li>
                        <li><a href="change_password.php"><i class="os-icon os-icon-others-43"></i><span>Change Password</span></a></li>
                        <li><a href="logout.php"><i class="os-icon os-icon-signs-11"></i><span>Logout</span></a></li>
                    </ul>
                </div>
            </div>
        </div>
        <!--------------------
        END - User avatar and menu in secondary top menu
        -------------------->
    </div>
    <!--------------------
    END - Top Menu Controls
    -------------------->
</div>
<!--------------------
END - Top Bar
-------------------->