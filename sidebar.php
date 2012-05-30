<div id="sidebar"><div id="sidebar-wrapper"> <!-- Sidebar with logo and menu -->
			
			<h1 id="sidebar-title"><a href="index.php">Facems Admin</a></h1>
		  
		  
		  
			<!-- Logo (221px wide) -->
			<a href="index.php"><img id="logo" src="resources/images/logo.png" alt="Simpla Admin logo" /></a>
		  
			<!-- Sidebar Profile links -->
			<div id="profile-links">
				Hello, <a href="#" title="Edit your profile"><?php echo $_SESSION['name']; ?></a> you have <a href="#messages" rel="modal" title="1 Message">1 Message</a><br />
				<br />
				<a href="http://www.facebook.com/pages/Hendymendy/292942664068311?sk=app_114940221954013" target="_blank" title="View the Site">View the Site</a> | <a href="?action=logout" title="Sign Out">Sign Out</a>
			</div>        
			
			<ul id="main-nav">  <!-- Accordion Menu -->
				
				<li>
					<a href="index.php" class="nav-top-item no-submenu current"> <!-- Add the class "no-submenu" to menu items with no sub menu -->
						Dashboard
					</a>       
				</li>
				
				<li> 
					<a href="#" class="nav-top-item"> <!-- Add the class "current" to current menu item -->
					Layout Manager
					</a>
					<ul>
						<li><a href="arranger.php">Edit Layout</a></li> <!-- Add class "current" to sub menu items also -->
						<li><a href="#messages" rel="modal" title="1 Messages">Create Layout</a></li>
						<!-- <li><a href="#">Modules</a></li> -->
					</ul>
				</li>
				<li>
							<a  href="#" class="nav-top-item">
						Media Library
					</a>
					<ul>
						<li><a href="#explorer" rel="modal" title="Explorer">Explorer</a></li>
					<!--	<li><a href="#">Manage Galleries</a></li>
						<li><a href="#">Manage Albums</a></li>
						<li><a href="#">Gallery Settings</a></li> -->
					</ul>
				</li>
						
				<li>
					<a href="#" class="nav-top-item-disabled">
						Page Publisher
					</a>
					<ul>
						<li><a href="#">Create a new Page</a></li>
						<li><a href="#">Edit Pages</a></li>
					</ul>
				</li>
				
				<li>
					<a href="#" class="nav-top-item-disabled">
						Client Management
					</a>
					<ul>
						<li><a href="#">Client Overview</a></li>
						<li><a href="#">Add a new Client</a></li>
						<li><a href="#">Client Settings</a></li>
					</ul>
				</li>
				
				<li>
					<a href="#messages" rel="modal" title="1 Message" class="nav-top-item-disabled" >
						Settings
					</a>
					<!-- <ul>
						<li><a href="#">General</a></li>
						<li><a href="#">Design</a></li>
						<li><a href="#">Your Profile</a></li>
						<li><a href="#">Users and Permissions</a></li>
					</ul> -->
				</li>      
				
			</ul> <!-- End #main-nav -->
				<div id="explorer" style="display: none"> <!-- Messages are shown when a link with these attributes are clicked: href="#messages" rel="modal"  -->
				
				<div style="margin-top:-10px;">
					<iframe src="/explorer" style="width:900px; height:500px; border-width:1px;"></iframe>
				</div>	
		
				
			</div> <!-- End #messages -->
					
		<div id="messages" style="display: none"> <!-- Messages are shown when a link with these attributes are clicked: href="#messages" rel="modal"  -->
				
				<h3 style="margin-left:0px;">Module Settings</h3>
			 
				<p>
					Some module options and settings are available only to the administrator of this site. Send message to the administrator if you would like to upgrade your account.  
					<!-- <small><a href="#" class="remove-link" title="Remove message">Remove</a></small> -->
				</p>

				
				<form action="#" method="post">
					
					<h4>New Message</h4>
					
					<fieldset>
						<textarea class="textarea" name="textfield" cols="79" rows="5"></textarea>
					</fieldset>
					
					<fieldset>
					
						<select name="dropdown" class="small-input">
							<option value="option1">Send to...</option>
							<option value="option2">Everyone</option>
							<option value="option3">Admin</option>
							<option value="option4">Vitally Marinchenko</option>
						</select>
						
						<input class="button" type="submit" value="Send" />
						
					</fieldset>
					
				</form>
				
			</div> <!-- End #messages -->
			
		</div></div> <!-- End #sidebar -->