<?php

ini_set('display_errors',1); 
error_reporting(E_ALL);

class Igniter
{
    /*
     * Edit the following variables
    */
    
    private $db_host = 'localhost'; // Database Host
    private $db_user = 'root';     // Username
    private $db_pass = 'root';     // Password
    private $db_name = 'facems';   // Database
    /*
     * End edit
     */

    private $con = false;               // Checks to see if the connection is active
    private $result = array();          // Results that are returned from the query
    private $encrypt = true;            //set to true to use md5 encryption for the password

   //table fields
    private $user_table   = 'logon';          //Users table name
    private $user_column  = 'useremail';     //USERNAME column (value MUST be valid email)
    private $pass_column  = 'password';      //PASSWORD column
    private $user_level   = 'userlevel';      //(optional) userlevel column
    private $user_name    = 'name';      //(optional) userlevel column
    private $user_surname = 'surname';      //(optional) userlevel column

    /*
     * Connects to the database, only one connection
     * allowed
     */

     
    function login($table, $username, $password){
        
        //conect to DB
        //$this->dbconnect();
        
        //make sure table name is set
        if($this->user_table == ""){
            $this->user_table = $table;
        }
        //check if encryption is used
        if($this->encrypt == true){
            $password = md5($password);
        }
        //execute login via qry function that prevents MySQL injections
        $result = $this->qry("SELECT * FROM ".$this->user_table." WHERE ".$this->user_column."='?' AND ".$this->pass_column." = '?';" , $username, $password);
        $row = mysql_fetch_assoc($result);
        if($row != "Error"){
            if($row[$this->user_column] !="" && $row[$this->pass_column] !=""){
                //register sessions
                if(!isset($_SESSION)) {session_start();}
                //you can add additional sessions here if needed
                $_SESSION['loggedin'] = $row[$this->pass_column];
                //userlevel session is optional. Use it if you have different user levels
                $_SESSION['userlevel'] = $row[$this->user_level];
                //you can add additional sessions here if needed
                $_SESSION['name'] = $row[$this->user_name];
                //userlevel session is optional. Use it if you have different user levels
                $_SESSION['surname'] = $row[$this->user_surname];
                return true;
            }else{
                session_destroy();
                return false;
            }
        }else{
            return false;
        }
    }
 
    //prevent injection
    function qry($query) {
      
      //$this->connect();
      
      $args  = func_get_args();
      $query = array_shift($args);
      $query = str_replace("?", "%s", $query);
      $args  = array_map('mysql_real_escape_string', $args);
      array_unshift($args,$query);
      $query = call_user_func_array('sprintf',$args);
      //echo($query); die;
      
      $result = mysql_query($query) or die(mysql_error());
          if($result){
            return $result;
          }else{
             $error = "Error";
             return $result;
          }
    }
 
    //logout function
    function logout(){
        session_destroy();
        return;
    }
 
    //check if loggedin
    function logincheck($logincode, $user_table, $pass_column, $user_column){
     
        //conect to DB
        //$this->connect();
        
        if(!isset($_SESSION)) {session_start();}
        
        //make sure password column and table are set
        if($this->pass_column == ""){
            $this->pass_column = $pass_column;
        }
        if($this->user_column == ""){
            $this->user_column = $user_column;
        }
        if($this->user_table == ""){
            $this->user_table = $user_table;
        }
        //exectue query
     
        $result = $this->qry("SELECT * FROM ".$this->user_table." WHERE ".$this->pass_column." = '?';" , $logincode);
        $row = mysql_fetch_assoc($result);
        $rownum = mysql_num_rows($result);
        //return true if logged in and false if not
        if($row != "Error"){
            if($rownum > 0){
                return true;
            }else{
                return false;
            }
        }
    }
 
    //reset password
    function passwordreset($username, $user_table, $pass_column, $user_column){
        
        //conect to DB
        //$this->connect();
        
        //generate new password
        $newpassword = $this->createPassword();
 
        //make sure password column and table are set
        if($this->pass_column == ""){
            $this->pass_column = $pass_column;
        }
        if($this->user_column == ""){
            $this->user_column = $user_column;
        }
        if($this->user_table == ""){
            $this->user_table = $user_table;
        }
        //check if encryption is used
        if($this->encrypt == true){
            $newpassword_db = md5($newpassword);
        }else{
            $newpassword_db = $newpassword;
        }
 
        //update database with new password
        $qry = "UPDATE ".$this->user_table." SET ".$this->pass_column."='".$newpassword_db."' WHERE ".$this->user_column."='".stripslashes($username)."'";
        $result = mysql_query($qry) or die(mysql_error());
 
        $to = stripslashes($username);
        //some injection protection
        $illegals=array("%0A","%0D","%0a","%0d","bcc:","Content-Type","BCC:","Bcc:","Cc:","CC:","TO:","To:","cc:","to:");
        $to = str_replace($illegals, "", $to);
        $getemail = explode("@",$to);
 
        //send only if there is one email
        if(sizeof($getemail) > 2){
            return false;
        }else{
            //send email
            $from = $_SERVER['SERVER_NAME'];
            $subject = "Password Reset: ".$_SERVER['SERVER_NAME'];
            $msg = "Your new password is: ".$newpassword."";
 
            //now we need to set mail headers
            $headers = "MIME-Version: 1.0 rn" ;
            $headers .= "Content-Type: text/html; \r\n" ;
            $headers .= "From: $from  \r\n" ;
 
            //now we are ready to send mail
            $sent = mail($to, $subject, $msg, $headers);
            if($sent){
                return true;
            }else{
                return false;
            }
        }
    }
 
    //create random password with 8 alphanumerical characters
    function createPassword() {
        $chars = "abcdefghijkmnopqrstuvwxyz023456789";
        srand((double)microtime()*1000000);
        $i = 0;
        $pass = '' ;
        while ($i <= 7) {
            $num = rand() % 33;
            $tmp = substr($chars, $num, 1);
            $pass = $pass . $tmp;
            $i++;
        }
        return $pass;
    }
 
    function killPage() {
     die;
    }
    
    function redirectPage($page) {
     header('Location: '.$page);
     $this->killPage(); 
    }
    //login form
    function loginform($formname, $formclass, $formaction){
        
        //conect to DB
        //$this->dbconnect();
        
        echo'loginform';
    }
    //reset password form
    function resetform($formname, $formclass, $formaction){
        
        //conect to DB
        //$this->dbconnect();
        
        echo'resetform';
    }     
     
    public function connect()
    {
        if(!$this->con)
        {
            $myconn = @mysql_connect($this->db_host,$this->db_user,$this->db_pass);
            if($myconn)
            {
                $seldb = @mysql_select_db($this->db_name,$myconn);
                if($seldb)
                {
                    $this->con = true;
                    return true;
                }
                else
                {
                    return false;
                }
            }
            else
            {
                return false;
            }
        }
        else
        {
            return true;
        }
    }

    /*
    * Changes the new database, sets all current results
    * to null
    */
    public function setDatabase($name)
    {
        if($this->con)
        {
            if(@mysql_close())
            {
                $this->con = false;
                $this->results = null;
                $this->db_name = $name;
                $this->connect();
            }
        }

    }

    /*
    * Checks to see if the table exists when performing
    * queries
    */
    private function tableExists($table)
    {
        $tablesInDb = @mysql_query('SHOW TABLES FROM '.$this->db_name.' LIKE "'.$table.'"');
        if($tablesInDb)
        {
            if(mysql_num_rows($tablesInDb)==1)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }

    /*
    * Selects information from the database.
    * Required: table (the name of the table)
    * Optional: rows (the columns requested, separated by commas)
    *           where (column = value as a string)
    *           order (column DIRECTION as a string)
    */
    public function select($table, $rows = '*', $where = null, $order = null)
    {
        $q = 'SELECT '.$rows.' FROM '.$table;
        if($where != null)
            $q .= ' WHERE '.$where;
        if($order != null)
            $q .= ' ORDER BY '.$order;

        echo($q);
        $query = @mysql_query($q);
        if($query)
        {
            $this->numResults = mysql_num_rows($query);
            for($i = 0; $i < $this->numResults; $i++)
            {
                $r = mysql_fetch_array($query);
                $key = array_keys($r);
                for($x = 0; $x < count($key); $x++)
                {
                    // Sanitizes keys so only alphavalues are allowed
                    if(!is_int($key[$x]))
                    {
                        if(mysql_num_rows($query) > 1)
                            $this->result[$i][$key[$x]] = $r[$key[$x]];
                        else if(mysql_num_rows($query) < 1)
                            $this->result = null;
                        else
                            $this->result[$key[$x]] = $r[$key[$x]];
                    }
                }
            }
            return true;
        }
        else
        {
            return false;
        }
    }

    /*
    * Insert values into the table
    * Required: table (the name of the table)
    *           values (the values to be inserted)
    * Optional: rows (if values don't match the number of rows)
    */
    public function insert($table,$values,$rows = null)
    {
        if($this->tableExists($table))
        {
            $insert = 'INSERT INTO '.$table;
            if($rows != null)
            {
                $insert .= ' ('.$rows.')';
            }

            for($i = 0; $i < count($values); $i++)
            {
                if(is_string($values[$i]))
                    $values[$i] = '"'.$values[$i].'"';
            }
            $values = implode(',',$values);
            $insert .= ' VALUES ('.$values.')';

            //echo($insert);
            $ins = @mysql_query($insert);

            if($ins)
            {
                return true;
            }
            else
            {
                echo(mysql_error());
                return false;
            }
        }
    }

    /*
    * Deletes table or records where condition is true
    * Required: table (the name of the table)
    * Optional: where (condition [column =  value])
    */
    public function delete($table,$where = null)
    {
        if($this->tableExists($table))
        {
            if($where == null)
            {
                $delete = 'DELETE '.$table;
            }
            else
            {
                $delete = 'DELETE FROM '.$table.' WHERE '.$where;
            }
            $del = @mysql_query($delete);

            if($del)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    /*
     * Updates the database with the values sent
     * Required: table (the name of the table to be updated
     *           rows (the rows/values in a key/value array
     *           where (the row/condition in an array (row,condition) )
     */
    public function update($table,$rows,$where)
    {
        if($this->tableExists($table))
        {
            // Parse the where values
            // even values (including 0) contain the where rows
            // odd values contain the clauses for the row
                  for($i = 0; $i < count($where); $i++)
                  {
                  if($i%2 != 0)
                  {
                  if(is_string($where[$i]))
                  {
                  if($where[($i+1)] != null)
                  $where[$i] = "='".$where[$i]."' AND ";
                  else
                  $where[$i] = "='".$where[$i]."'";
                  }
                  }
                  }
                  $where = implode('',$where);

           $update = 'UPDATE '.$table.' SET ';
            $keys = array_keys($rows);
            for($i = 0; $i < count($rows); $i++)
            {
                if(is_string($rows[$keys[$i]]))
                {
                    $update .= $keys[$i].'="'.$rows[$keys[$i]].'"';
                }
                else
                {
                    $update .= $keys[$i].'='.$rows[$keys[$i]];
                }

                // Parse to add commas
                if($i != count($rows)-1)
                {
                    $update .= ',';
                }
            }
            $update .= ' WHERE '.$where;
            //echo($update);
            $query = @mysql_query($update);
            if($query)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    /*
    * Returns the result set
    */
    public function getResult()
    {
        return $this->result;
    }
}
?>