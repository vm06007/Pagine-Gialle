<?php

//VITALLY MARINCHENKO
//vitally.marinchenko@gmail.com

ini_set('display_errors',1);
include('igniter.php'); 
error_reporting(E_ALL);

$ignite = new Igniter();
$ignite->connect();

session_start();

if($ignite->logincheck(@$_SESSION['loggedin'], 'logon', 'password', 'useremail') == false) { 
  if(isset($_REQUEST['action']) && $_REQUEST['action'] == "login") { 
     if($ignite->login("logon", $_REQUEST['username'], $_REQUEST['password']) == true) { 
       //do something on successful login
    } else $ignite->redirectPage('login.php');
  } else $ignite->redirectPage('login.php');
}

if(isset($_REQUEST['action']) && $_REQUEST['action'] == "logout") {
 $ignite->logout();
  $ignite->redirectPage('login.php');
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>Facems Admin | Welcome</title>
    <?php require_once 'includes/head.inc'; ?>
  </head>
  <body>
    <div id="body-wrapper">
      <!-- Wrapper for the radial gradient background -->
      <?php include_once "sidebar.php" ?>
      <div id="main-content">
        <!-- Main Content Section with everything -->
        <noscript>
          <!-- Show a notification if the user has disabled javascript -->
          <div class="notification error png_bg">
            <div>Javascript is disabled or is not supported by your browser. Please 
            <a href="http://browsehappy.com/" title="Upgrade to a better browser">upgrade</a>your
            browser or 
            <a href="" title="Enable Javascript in your browser">enable</a>Javascript to navigate
            the interface properly. Download From 
            <a href="http://www.exet.tk">exet.tk</a></div>
          </div>
        </noscript>
        <!-- Page Head -->
        <h2>Welcome <?php echo $_SESSION['name']; ?><?php echo $_SESSION['surname']; ?></h2>
        <p id="page-intro">What would you like to do?</p>
        <ul class="shortcut-buttons-set">
          <li>
            <a class="shortcut-button" href="/arranger.php">
              <span>
              <img src="resources/images/icons/pencil_48.png" alt="icon" />
              <br />Edit Layout</span>
            </a>
          </li>
          <li>
            <a class="shortcut-button" href="#messages" rel="modal" title="1 Messages">
              <span>
              <img src="resources/images/icons/paper_content_pencil_48.png" alt="icon" />
              <br />Create Layout</span>
            </a>
          </li>
          <li>
            <a class="shortcut-button" href="#explorer" rel="modal" title="Explorer">
              <span>
              <img src="resources/images/icons/image_add_48.png" alt="icon" />
              <br />Upload Media</span>
            </a>
          </li>
          <li>
            <a class="shortcut-button" target="_blank"
            href="http://www.facebook.com/pages/Hendymendy/292942664068311?sk=app_114940221954013">
              <span>
              <img src="resources/images/icons/clock_48.png" alt="icon" />
              <br />Overview</span>
            </a>
          </li>
          <li>
            <a class="shortcut-button" href="#messages" rel="modal">
              <span>
              <img src="resources/images/icons/comment_48.png" alt="icon" />
              <br />Send Message</span>
            </a>
          </li>
        </ul>
        <!-- End .shortcut-buttons-set -->
        <div class="clear"></div>
        <!-- End .clear -->
        <div class="content-box">
          <!-- Start Content Box -->
          <div class="content-box-header">
            <h3>Module List</h3>
            <!-- 
                                        <ul class="content-box-tabs" >
                                                <li><a href="#tab1" class="default-tab">Fans</a></li>
                                                <li><a href="#tab2">Non Fans</a></li>
                                        </ul>
                                        -->
            <div class="clear"></div>
          </div>
          <!-- End .content-box-header -->
          <div class="content-box-content" id="s">
            <div class="tab-content default-tab" id="tab1">
              <!-- This is the target div. id must match the href of this div's tab -->
              <table id="modules">
                <thead>
                  <tr>
                    <th width="10px">
                      <img src="resources/images/updown2.gif" />
                    </th>
                    <th>
                      <input class="check-all" type="checkbox" />
                    </th>
                    <th>Module Name</th>
                    <th>Layout</th>
                    <th>Dimensions</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tfoot style="display:none">
                  <th>
                    <td colspan="7">
                      <div class="bulk-actions align-left">
                        <select name="dropdown">
                          <option value="option1">Choose an action...</option>
                          <option value="option2">Edit</option>
                          <option value="option3">Delete</option>
                        </select>
                        <a class="button" href="#">Apply to selected</a>
                      </div>
                      <div class="pagination">
                        <a href="#" title="First Page">« First</a>
                        <a href="#" title="Previous Page">« Previous</a>
                        <a href="#" class="number current" title="1">1</a>
                        <a href="#" class="number" title="2">2</a>
                        <a href="#" class="number" title="3">3</a>
                        <a href="#" class="number" title="4">4</a>
                        <a href="#" title="Next Page">Next »</a>
                        <a href="#" title="Last Page">Last »</a>
                      </div>
                      <!-- End .pagination -->
                      <div class="clear"></div>
                    </td>
                  </th>
                </tfoot>
                <tbody>
                  <?php 

                    $sum = 0;     
                    $sql = mysql_query("SELECT b.grid_id,b.grid_name,b.grid_width,b.grid_height, c.grid_status FROM logon a, grids b, usergrid_relation c where a.userid=c.user_id_fk and b.grid_id=c.grid_id_fk and a.userid='1' order by c.grid_order;");
                    while($row = mysql_fetch_array($sql))
                    {

                      $grid_id     = $row['grid_id'];
                      $grid_name   = $row['grid_name'];
                      $grid_width  = $row['grid_width'];
                      $grid_height = $row['grid_height'];
                      $grid_status = $row['grid_status'];
                      
                      $sum = $sum + $grid_height;
                          
                      $status = "Disabled"; 
                      if($grid_status) {$status = "Enabled";} ?>
                  <tr id="modules-row-&lt;?php echo($grid_id);?&gt;">
                    <td class="dragHandle"></td>
                    <td>
                      <input type="checkbox" />
                    </td>
                    <td>
                      <?php echo($grid_name); ?>
                    </td>
                    <td>
                      <a href="/arranger.php" title="title">Seat PG</a>
                    </td>
                    <td><?php echo($grid_width); ?>X <?php echo($grid_height); ?></td>
                    <td>
                      <?php echo($status); ?>
                    </td>
                    <td>
                      <!-- Icons -->
                      <a href="#messages" rel="modal" title="Edit">
                        <img src="resources/images/icons/pencil.png" alt="Edit" />
                      </a>
                      <a href="#messages" rel="modal" title="Delete">
                        <img src="resources/images/icons/cross.png" alt="Delete" />
                      </a>
                      <a href="#messages" rel="modal" title="Edit Meta">
                        <img src="resources/images/icons/hammer_screwdriver.png" alt="Edit Meta" />
                      </a>
                    </td>
                  </tr><?php } ?>
                </tbody>
              </table>
            </div>
            <!-- End #tab1 -->
            <script>$('#modules').tableDnD({ onDrop: function(table, row) { $.ajax({ type: "POST",
            url: "ajax.php", data: $('#modules').tableDnDSerialize(), success: function(data) {
            //alert(data); //$('.flash').fadeIn('slow'); //$('.flash').html("Updated") } }); },
            dragHandle: "dragHandle" }); $("#modules tr").hover(function() {
            //$(this).attr("style","background-color:#f3f3f3;")
            $(this.cells[0]).addClass('showDragHandle'); }, function() {
            //$(this).attr("style","background-color:#ffffff;")
            $(this.cells[0]).removeClass('showDragHandle'); });</script>
            <div class="tab-content" id="tab2">
              <table>
                <thead>
                  <tr>
                    <th>
                      <input class="check-all" type="checkbox" />
                    </th>
                    <th>Module Name</th>
                    <th>Layout</th>
                    <th>Dimensions</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tfoot>
                  <tr>
                    <td colspan="6">
                      <div class="bulk-actions align-left">
                        <select name="dropdown">
                          <option value="option1">Choose an action...</option>
                          <option value="option2">Edit</option>
                          <option value="option3">Delete</option>
                        </select>
                        <a class="button" href="#">Apply to selected</a>
                      </div>
                      <div class="clear"></div>
                    </td>
                  </tr>
                </tfoot>
                <tbody>
                  <tr>
                    <td>
                      <input type="checkbox" />
                    </td>
                    <td>Cover Image Enabled</td>
                    <td>
                      <a href="#" title="title">Seat PG</a>
                    </td>
                    <td>520 X 600</td>
                    <td>Enabled</td>
                    <td>
                      <!-- Icons -->
                      <a href="#messages" rel="modal" title="Edit">
                        <img src="resources/images/icons/pencil.png" alt="Edit" />
                      </a>
                      <a href="#messages" rel="modal" title="Delete">
                        <img src="resources/images/icons/cross.png" alt="Delete" />
                      </a>
                      <a href="#messages" rel="modal" title="Edit Meta">
                        <img src="resources/images/icons/hammer_screwdriver.png" alt="Edit Meta" />
                      </a>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <input type="checkbox" />
                    </td>
                    <td>Cover Image Disabled</td>
                    <td>
                      <a href="#" title="title">Seat PG</a>
                    </td>
                    <td>520 X 600</td>
                    <td>Disabled</td>
                    <td>
                      <!-- Icons -->
                      <a href="#" title="Edit">
                        <img src="resources/images/icons/pencil.png" alt="Edit" />
                      </a>
                      <a href="#" title="Delete">
                        <img src="resources/images/icons/cross.png" alt="Delete" />
                      </a>
                      <a href="#" title="Edit Meta">
                        <img src="resources/images/icons/hammer_screwdriver.png" alt="Edit Meta" />
                      </a>
                    </td>
                  </tr>
                </tbody>
              </table>
              <form action="#" method="post">
                <fieldset>
                  <!-- Set class to "column-left" or "column-right" on fieldsets to divide the form into columns -->
                  <p>
                    <label>Small form input</label>
                    <input class="text-input small-input" type="text" id="small-input"
                    name="small-input" />
                    <span class="input-notification success png_bg">Successful message</span>
                    <!-- Classes for input-notification: success, error, information, attention -->
                    <br />
                    <small>A small description of the field</small>
                  </p>
                  <p>
                    <label>Medium form input</label>
                    <input class="text-input medium-input datepicker" type="text" id="medium-input"
                    name="medium-input" />
                    <span class="input-notification error png_bg">Error message</span>
                  </p>
                  <p>
                    <label>Large form input</label>
                    <input class="text-input large-input" type="text" id="large-input"
                    name="large-input" />
                  </p>
                  <p>
                  <label>Checkboxes</label>
                  <input type="checkbox" name="checkbox1" />This is a checkbox 
                  <input type="checkbox" name="checkbox2" />And this is another checkbox</p>
                  <p>
                  <label>Radio buttons</label>
                  <input type="radio" name="radio1" />This is a radio button
                  <br />
                  <input type="radio" name="radio2" />This is another radio button</p>
                  <p>
                    <label>This is a drop down list</label>
                    <select name="dropdown" class="small-input">
                      <option value="option1">Option 1</option>
                      <option value="option2">Option 2</option>
                      <option value="option3">Option 3</option>
                      <option value="option4">Option 4</option>
                    </select>
                  </p>
                  <p>
                    <label>Textarea with WYSIWYG</label>
                    <textarea class="text-input textarea wysiwyg" id="textarea" name="textfield"
                    cols="79" rows="15"></textarea>
                  </p>
                  <p>
                    <input class="button" type="submit" value="Submit" />
                  </p>
                </fieldset>
                <div class="clear"></div>
                <!-- End .clear -->
              </form>
            </div>
            <!-- End #tab2 -->
          </div>
          <!-- End .content-box-content -->
        </div>
        <!-- End .content-box -->
        <div class="content-box column-left" style="display:none">
          <div class="content-box-header">
            <h3>Content box left</h3>
          </div>
          <!-- End .content-box-header -->
          <div class="content-box-content">
            <div class="tab-content default-tab">
              <h4>Maecenas dignissim</h4>
              <p>Lorem ipsum dolor Seat PG, consectetur adipiscing elit. Sed in porta lectus.
              Maecenas dignissim enim quis ipsum mattis aliquet. Maecenas id velit et elit gravida
              bibendum. Duis nec rutrum lorem. Donec egestas metus a risus euismod ultricies.
              Maecenas lacinia orci at neque commodo commodo.</p>
            </div>
            <!-- End #tab3 -->
          </div>
          <!-- End .content-box-content -->
        </div>
        <!-- End .content-box -->
        <div class="content-box column-right" style="display:none">
          <div class="content-box-header">
            <!-- Add the class "closed" to the Content box header to have it closed by default -->
            <h3>Content box right</h3>
          </div>
          <!-- End .content-box-header -->
          <div class="content-box-content">
            <div class="tab-content default-tab">
              <h4>This box is closed by default</h4>
              <p>Lorem ipsum dolor Seat PG, consectetur adipiscing elit. Sed in porta lectus.
              Maecenas dignissim enim quis ipsum mattis aliquet. Maecenas id velit et elit gravida
              bibendum. Duis nec rutrum lorem. Donec egestas metus a risus euismod ultricies.
              Maecenas lacinia orci at neque commodo commodo.</p>
            </div>
            <!-- End #tab3 -->
          </div>
          <!-- End .content-box-content -->
        </div>
        <!-- End .content-box -->
        <div class="clear"></div>
        <!-- Start Notifications -->
        <div class="notification attention png_bg">
          <a href="#" class="close">
            <img src="resources/images/icons/cross_grey_small.png" title="Close this notification"
            alt="close" />
          </a>
          <div>Attention notification.</div>
        </div>
        <div class="notification information png_bg">
          <a href="#" class="close">
            <img src="resources/images/icons/cross_grey_small.png" title="Close this notification"
            alt="close" />
          </a>
          <div>Information notification.</div>
        </div>
        <div class="notification success png_bg">
          <a href="#" class="close">
            <img src="resources/images/icons/cross_grey_small.png" title="Close this notification"
            alt="close" />
          </a>
          <div>Success notification.</div>
        </div>
        <div class="notification error png_bg">
          <a href="#" class="close">
            <img src="resources/images/icons/cross_grey_small.png" title="Close this notification"
            alt="close" />
          </a>
          <div>Error notification.</div>
        </div>
        <!-- End Notifications -->
        <div id="footer">
          <small>
          <!-- Remove this notice or replace it with whatever you want -->
          © Copyright 2011 
          <a target="_blank" href="http://www.facebook.com/vitally.marinchenko">Vitally
          Marinchenko</a>| Powered by 77Agency</small>
        </div>
        <!-- End #footer -->
      </div>
      <!-- End #main-content -->
    </div>
  </body>
</html>