<?php
/*
* Sessionara - Session based data environment with MySQL
* Copyright (c) 2001-2004 Dario Nuevo
* http://php.xbe.ch | dn(*at*)xbe(*dot*)ch
* Release 1.7-r1 [build 20041103]
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*
* ADDITION: Please send me improvement ideas and realized stuff. At least tell
* me about it *g*. I's great to have some input.
*
* You'll find the entire GNU GPL in the LICENSE file.
* --------------------------------------------------
* Quick guide
* For questions regarding the install process, take a look at the documentation (you'll find it on the website).
* Here's a little example how you could use it:
*
* $ses = new Sessionara("W/MYSQL");
* $ses->var_set("foo","bar");
* echo "Session has ID ".($ses->get_sid());
*
* (on another page, pass the $sid through GET or POST):
*
* $ses = new Sessionara(); // <- calling withough param = resuming session
* echo $ses->var_get("foo");
*/

class Sessionara
{

	var $cfg;
	var $sid;

	function Sessionara($sqlquery = "") {

		// **** configuration **** [default]
		$this->cfg = array();
		// Generated $sid's string length [26]
		$this->cfg["sid_howlong"] = 26;
		// Session lifetime in minutes [10]
		$this->cfg["session_howlong"] = 0;
		// Class' table name
		$this->cfg["db_table"] = "sessions";
		// What should the class do in a case of an expired session?
		$this->cfg["timeout_action"] = "error"; // options "redir", "error"
		// In the case of an expired session, we'll send the user to this location
		$this->cfg["timeoutpage"] = "index.php";
		// Keyword to pass if you don't pass a sql select statement (V1.1)
		$this->cfg["placeholder"] = "W/MYSQL";
		// transport the sid automatically with a cookie? ("enable cookies?") (V1.7)
		$this->cfg["use_cookies"] = true;
		// BETA FEATURE: Decide to (not) use first "security" tries (V1.1) [false]
		$this->cfg["use_addr"] = true; // true, false or "old" (last as string!)

		/*
		mysql table field names
		here you can alter the mysql field names that are used. if you just use the normal sql to create the table, don't care about this... *g
		but if you want to integrate this class into a project-based database scheme, it's useful that you can define the field names that are used..

		i hope i don't have to mention that you just have to change the _value_ of this array! i.e. $this->cfg["tfields"]["autoid"] = "ID";
		*/
		$this->cfg["tfields"]["autoid"] = "autoid"; // primary key
		$this->cfg["tfields"]["sid"] = "sid"; // session id
		$this->cfg["tfields"]["data"] = "data"; // data container
		$this->cfg["tfields"]["addr"] = "addr"; // remote address or strid
		$this->cfg["tfields"]["opened"] = "opened"; // timestamp open
		$this->cfg["tfields"]["expire"] = "expire"; // timestamp expire time

		$this->session_table_cleanup();
		$this->logged_in = false;
		$this->sid = $this->lookup_sid();
		if((strlen($this->sid) == $this->cfg["sid_howlong"]) && (strlen($sqlquery)<1)) $this->resume_session();
		elseif(strlen($sqlquery) > 1) $this->open_session($sqlquery);
		else $this->timeout();

		if($sqlquery == $this->cfg["placeholder"]) $this->login();
		$this->res = array();
	}

	function lookup_sid() {
		// bad coding, but variable variables doesnt't work on superglobals like $_POST..
		if(strlen($_GET["sid"]) == $this->cfg["sid_howlong"]) {
			return $_GET["sid"];
		}else{
			if(strlen($_POST["sid"]) == $this->cfg["sid_howlong"]) {
				return $_POST["sid"];
			}else{
				if(strlen($_COOKIE["sid"]) == $this->cfg["sid_howlong"]) {
					return $_COOKIE["sid"];
				}else{
					return false;
				}
			}
		}
	}

	/* *******************************************************************************************
	session creating, etc..
	*/

	function open_session($sqlquery) {
		$this->sqlquery = $sqlquery;
		$this->sid = $this->get_hashy();
		return true;
	}

	function resume_session() {
		$query = $this->complete_sql("SELECT `".$this->fname("autoid")."` FROM `{$this->cfg["db_table"]}` WHERE `".$this->fname("sid")."`='{$this->sid}'");
		$rs = mysql_query($query);
		if(mysql_num_rows($rs) != 0) {
			$this->logged_in = true;
			$this->var_load();
			$this->give_lifetime();
			return true;
			$rs = null;
		} else {
			$this->timeout();
		}

	}

	function login() {
		/*
		* If you pass a SQL statement to open a session, you have to call this function to check
		* the query. Make a if($ses->login()) over it.
		* Returns true if the query returns more than 0 row, otherwise false.
		*/

		if($this->logged_in == false) {
			$this->data = array();
			if($this->sqlquery != $this->cfg["placeholder"]) {
				if(strtolower(substr($this->sqlquery,0,6)) != "select") $this->perror("Invalid call to login(). Pass SELECT statements only.");
				$rs = mysql_query($this->sqlquery);
				if(mysql_num_rows($rs) == 0) {
					$makeit = false;
				} else {
					$makeit = true;
					$resi = mysql_fetch_array($rs);
					$rs = null;
				}
			} else {
				$makeit = true;
			}

			if($makeit == true) {
				if(($this->session_create($this->sid))==true) {
					$this->logged_in = true;
					$this->var_set($resi);
					//$this->var_save();
					if($this->cfg["use_cookies"]) $this->send_cookie();
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		} else {
			return true;
		}
	}

	function session_create($sid) {
		$this->datavarset( array(
		"SESSION_START" => (time()),
		"SESSION_EXPIRE" => ((time())+(60*$this->cfg["session_howlong"])),
		"SESSION_STRID" => ($this->get_strid()),
		"SESSION_QUERIES" => array(),
		"SID" => $sid
		));
		$rs = mysql_query("INSERT INTO `{$this->cfg["db_table"]}` (`".$this->fname("sid")."`, `".$this->fname("opened")."`, `".$this->fname("expire")."`, `".$this->fname("addr")."`) VALUES ('{$sid}', '{$this->data["SESSION_START"]}', '{$this->data["SESSION_EXPIRE"]}', '".($this->get_strid())."');");
		if(mysql_affected_rows()==0) $this->perror("DB error, could not insert new session row");
		else return true;
	}

	function session_close() {
		mysql_query(($this->complete_sql("UPDATE `{$this->cfg["db_table"]}` SET `".$this->fname("expire")."`=100 WHERE `".$this->fname("sid")."`='{$this->sid}'")));
		return $this->session_table_cleanup();
	}

	// lazy people like me make function for everything.. returns defined table name
	function fname($name) {
		if(isset($this->cfg["tfields"][$name])) return $this->cfg["tfields"][$name];
		else $this->perror("Invalid internal call! Didn't find a table field name...");
	}

	/* *******************************************************************************************
	session timing stuff
	*/

	function session_table_cleanup() {
		/*
		* Deletes all outdated sessions from the table
		*/
		mysql_query("DELETE FROM `{$this->cfg["db_table"]}` WHERE (`".$this->fname("expire")."` < ".(time()).");");
		return true;
	}

	function give_lifetime() {
		$this->data["SESSION_EXPIRE"] = (time()+(60*$this->cfg["session_howlong"]));
		mysql_query(($this->complete_sql("UPDATE `{$this->cfg["db_table"]}` SET `".$this->fname("expire")."`={$this->data["SESSION_EXPIRE"]} WHERE `".$this->fname("sid")."`='{$this->sid}'")));

		$this->set_cookie( $timy );
		if(mysql_affected_rows() > 0) {
			if($this->cfg["use_cookies"]) $this->set_cookie( $this->data["SESSION_EXPIRE"] );
			return $this->data["SESSION_EXPIRE"];
		}else{
			return false;
		}
	}

	function timeout() {
		if($this->cfg["timeout_action"] == "redir") header("Location: {$this->cfg["timeoutpage"]}");
		else $this->perror("Session could not be established.");
	}

	/* *******************************************************************************************
	session variable stuff
	*/

	// returns a session variable/query
	function var_get($name, $number = false) {
		if( $this->logged_in() ) {
			if( $this->is_sessionquery( $name ) ) {
				if($number) return $this->data[$name][$number];
				else return $this->next($name);
			} else {
				if(strlen($this->data[$name]) != 0) return $this->data[$name];
				else return "";
			}
		}
	}

	// sets a variable value
	function var_set($name, $value = "", $save = true) {
		if($this->logged_in()) {
			if(is_array($name)) {
				foreach($name as $key=>$val) {
					if(strlen($key)>0 && strlen($val)>0) $this->var_set($key,$val,false);
				}
				$this->var_save();
			}else{
				$this->data[$name] = $value;
				if($save) $this->var_save();
			}
		}else{
			return false;
		}
	}

	// unsets ("deletes") a variable
	function var_unset($name) {
		if($this->logged_in()) {
			if($this->is_sessionquery($name)) $this->data["SESSION_QUERIES"][$name] = null;
			if(isset($this->data[$name])) unset($this->data[$name]);
			return $this->var_save();
		}
	}

	// private; saves data to db table
	function var_save() {
		$this->data["cfg"] = $this->cfg;
		$tosave = addslashes(serialize($this->data));
		$query = $this->complete_sql("UPDATE `{$this->cfg["db_table"]}` SET ".$this->fname("data")."='{$tosave}' WHERE `".$this->fname("sid")."`='{$this->sid}'");
		$rs = mysql_query($query);
		if(mysql_affected_rows()!=0) return true;
		else return false;
	}

	// private; loads data from db table
	function var_load() {
		$query = $this->complete_sql("SELECT * FROM `{$this->cfg["db_table"]}` WHERE `".$this->fname("sid")."`='{$this->sid}'");
		$rs = mysql_query($query);
		if(mysql_num_rows($rs)!=0) {
			$resi = mysql_fetch_array($rs);
			$data = stripslashes($resi["data"]);
			$data = unserialize($data);
			$this->data = $data;
			$this->cfg = $this->data["cfg"];
			return true;
		} else {
			return false;
		}
	}

	// abandoned as of release 1.7
	function rs2session(&$obj_recordset) {
		return false;
	}

	// private; internal way to set a variable group. this and the one below should be cleaned out..
	function datavarset($name, $val = "") {
		if(is_array($name)) {
			foreach($name as $key=>$rval) {
				$this->datavarset_single($key, $rval);
			}
		}else{
			$this->datavarset_single($name, $val);
		}
	}

	// private; single variable setting..
	function datavarset_single($name, $val) {
		$this->data[$name] = $val;
	}

	/* *******************************************************************************************
	session queries
	*/

	// sets/opens a session query - see the doc
	function sql2session($name, $select_statement) {
		$data = array();
		$rs = mysql_query($select_statement);

		if(mysql_num_rows($rs) != 0) {
			while($resi = mysql_fetch_array($rs)) {
				$data[] = $resi;
			}
		}

		$this->data[$name] = $data;
		$this->definequery($name);
		$this->var_save();

		$rs = null;
		return true;
	}

	// returns next row of a session query
	function next($name) {
		if($this->is_sessionquery($name)) {
			$return = $this->data[$name][$this->data["SESSION_QUERIES"][$name]];
			if($this->data["SESSION_QUERIES"][$name] < count($this->data[$name])+1) {
				$this->data["SESSION_QUERIES"][$name]++;
				$this->var_save();
			}else{
				$return = false;
			}
			return $return;
		}else{
			return false;
		}
	}

	// returns the number of element that a session query holds
	function count($name) {
		if($this->is_sessionquery($name)) return count($this->data[$name]);
		else return false;
	}

	// returns an entire content of a session query
	function sq_get($name) {
		if($this->is_sessionquery($name)) return $this->data[$name];
		else return false;
	}

	// resets a session query
	function reset($name) {
		return $this->definequery($name);
	}

	// private; "registers" a variable as a session query [V1.5]
	function definequery($name) {
		$this->data["SESSION_QUERIES"][$name] = (int)0;
		return true;
	}

	// private; check function
	function is_sessionquery($name) {
		if(array_key_exists($name,$this->data["SESSION_QUERIES"])) return true;
		else return false;
	}

	/* *******************************************************************************************
	cookies
	*/

	// private; sends a cookie
	function send_cookie() {
		setcookie ( "sid", $this->sid, time() + ( 60 * $this->cfg["session_howlong"] ), "/" );
	}

	// private; sends cookie with variable time
	function set_cookie( $expire ) {
		setcookie ( "sid", $this->sid, $expire, "/" );
	}

	// private; deletes the sid cookie
	function del_cookies( $all = false ) {
		setcookie ( "sid", "", time()-3000, "/" );
	}

	/* *******************************************************************************************
	strings
	*/

	// returns html link with session id; customizable
	function get_link($document, $text = "", $target = "", $qstring = "", $getsid = true) {
		if($getsid == true) {
			$sid = "?sid=".$this->data["SID"];
			$sid .= "&";
		} else {
			$sid = "?";
		}
		if(strlen($target) != 0) {
			$target = " target=\"{$target}\"";
		}
		return "<a href=\"{$document}{$sid}{$qstring}\"{$target}>{$text}</a>";
	}

	// returns the current session id
	function get_sid() {
		return $this->sid;
	}

	// gets string for inclusion in query string
	function get_querystring() {
		return "sid=".$this->sid;
	}

	// gets html for input hidden field with the sid (or other values) for inclusion in web forms
	function get_hiddenfield($name = "", $value = "") {
		if(strlen($name) == 0) {
			$name = "sid";
			$value = $this->sid;
		}
		return "<input type=\"hidden\" name=\"{$name}\" value=\"{$value}\">";
	}

	// returns a random string
	function get_hashy() {
		srand ((float) microtime() * 10000000);
		return substr(md5(rand(0,9999999)),0,$this->cfg["sid_howlong"]);
	}

	// returns the "strid" string, see the doc V1.5
	function get_strid() {
		if($this->cfg["use_addr"] == true) {
			$vals = array("REMOTE_ADDR","HTTP_USER_AGENT","HTTP_VIA");
			foreach($vals as $val) $strid .= $_SERVER[$val];
			return md5($strid);
		}elseif($this->cfg["use_addr"] == "old") {
			return $_SERVER["REMOTE_ADDR"];
		}else{
			return "";
		}
	}

	/* *******************************************************************************************
	misc
	*/

	// changes conf option during session
	function set_option($setting, $value) {
		if(array_key_exists($setting,$this->cfg) == TRUE && $setting != "sid_howlong") {
			$this->cfg[$setting] = $value;
			$this->var_save();
			return true;
		} else {
			return false;
		}
	}

	// kept for compatibility (<-- difficult word)
	function set_timeoutpage($value) {
		return $this->set_option("timeoutpage",$value);
	}

	// private; completes queries
	function complete_sql($value) {
		return $this->return_sql("{$value} AND `".$this->fname("addr")."`='".($this->get_strid())."'");
	}

	// private; returns sql
	function return_sql($value) {
		if(strtolower(substr($value,0,6)) == "update") return "{$value};";
		else return "{$value} LIMIT 0,1;";
	}

	// prints out arrays in a nice way in browsers..
	function p_array($arr, $pre = true) {
		if(is_array($arr)) {
			if($pre == true) $ins = array("<pre>","</pre><br>\n","");
			else $ins = array("","","\t");
			$backy = "{$ins[0]}Array (\n";
			foreach($arr as $key=>$value) {
				$value = $this->p_array_returnval($value);
				$backy .= "{$ins[2]}[{$key}] =&gt; {$value}\n";
			}
			$backy .= ");{$ins[1]}";
			return $backy;
		} else {
			return false;
		}
	}

	// subfunction for recursion..
	function p_array_returnval($value) {
		if(is_array($value)) return $this->p_array($value,false);
		else return $value;
	}

	// prints config
	function p_cfg() {
		echo $this->p_array($this->cfg);
	}

	/* *******************************************************************************************
	error stuff
	*/

	// prints out error & dies ;(
	function perror($errormsg) {
		echo "<b>Error orcurred:</b>&nbsp;{$errormsg}\n";
		die;
	}

	// tells if user is "logged in" - if session exists
	function logged_in() {
		if($this->sqlquery != $this->cfg["placeholder"] AND $this->logged_in == false) $this->perror("Session is not created. Call login() before trying to execute other session functions.");
		else return true;
	}

}

?>
