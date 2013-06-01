<?php
/*  RuneScape HiScore Grabber
 *  ------------------------------------------
 *  Author: wutno (#/g/tv - Rizon)
 *
 *  GNU License Agreement
 *  ---------------------
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License version 2 as
 *  published by the Free Software Foundation.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 *  http://www.gnu.org/licenses/gpl.txt
 *
 *  SQL structure (NEEDED):
 *  CREATE TABLE IF NOT EXISTS `datapoints` (
 *   `id` int(11) NOT NULL AUTO_INCREMENT,
 *   `user_id` int(11) NOT NULL,
 *   `version` enum('new','old') NOT NULL,
 *   `data` text NOT NULL,
 *   `update_time` int(11) NOT NULL,
 *   PRIMARY KEY (`id`)
 *  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;
 *  CREATE TABLE IF NOT EXISTS `users` (
 *   `id` int(11) NOT NULL AUTO_INCREMENT,
 *   `username` varchar(12) NOT NULL,
 *   `first_added` int(11) NOT NULL,
 *   `last_updated` int(11) NOT NULL,
 *   `live` tinyint(1) NOT NULL,
 *   `oldschool` tinyint(1) NOT NULL,
 *   PRIMARY KEY (`id`)
 *  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;
 */
error_reporting(-1);

if(isset($_GET)){
	if(isset($_GET['version']) && isset($_GET['user'])){
		if(preg_replace("/[^a-zA-Z\d ]/", "", $_GET['user']) != $_GET['user'] || strlen($_GET['user']) > 25 || !in_array($_GET['version'], array("live","oldschool"))){
			die('Something went wrong.');
		}
		$db = new mysqli("localhost", "", "", "");
		$user = strtolower($_GET['user']);
		$version = $_GET['version'];
	}
	else{
		die('Something went wrong.');
	}
}
else{
	die('Something went wrong.');
}

class rs{
	public $version = '';
	public $username = '';
	public $newData = '';

	public function __construct($version, $username){
		$this->username = $username;
		$this->version = $version;
		$this->newData = $this->queryHiScores();
	}

	public function checkUser($data){
		global $db;
		$query = $db->query("SELECT `id` FROM `users` WHERE `username` ='".$this->username."'") or die ($db->error);
		if($query->num_rows != 0){
			$info = $query->fetch_row();
			$out = $this->queryDataPoints($info);
			if(isset($_GET['update']) && $_GET['update'] == "1"){
				$this->addDataPoint($info,$data);
			}
			return $out;
		}
		else{
			die("User not found in database"); //TODO: Have an insertUser that ACTUALLY works and adds datapoints
			//$this->insertUser($data);
		}
	}

	private function addDataPoint($id, $data){
		global $db;
		$db->query("INSERT INTO `datapoints` (`user_id` ,`data` ,`version`, `update_time`) VALUES ('".$id."', '".$data."', '".$this->version."', '".time()."')") or die ($db->error);
	}

	private function insertUser($version){ //live => 0/1, oldschool => 0/1
		global $db;
		$db->query("INSERT INTO `users` (`username`,`first_added`,`last_updated`,`live`,`oldschool`) VALUES ('".$this->username."','".time()."','".time()."','".$version['live']."','".$version['oldschool']."')") or die ($db->error);
	}

	private function queryDataPoints($id){ //Working?
		global $db;
		$query = $db->query("SELECT * FROM `datapoints` WHERE `user_id` ='".$id."' AND `version` ='".$this->version."' ORDER BY `update_time` DESC LIMIT 0,1") or die ($db->error);
		return $query->fetch_array(MYSQLI_ASSOC);
	}

	private function queryHiScores(){
		$get_page_info = curl_init('http://services.runescape.com/m=hiscore'.($this->version == "oldschool" ? '_oldschool' : '').'/index_lite.ws?player='.$this->username);
		$options = array(CURLOPT_RETURNTRANSFER => TRUE, CURLOPT_BINARYTRANSFER => TRUE);
		curl_setopt_array($get_page_info, $options);
		$page_info = curl_exec($get_page_info);
		$curl_info = curl_getinfo($get_page_info);
		if($curl_info['http_code'] == 200){
			curl_close($get_page_info);
			return $page_info;
		}
	}

	public function compare($new, $old){
		$i = 0;
		foreach($new as $parsedNew){
			$parsedNew = explode(",", $parsedNew); //[0][1]([2])
			$parsedOld = explode(",", $old[$i]); //[0][1]([2])
			$j = 0;

			foreach($parsedNew as $checkDiffernce){
				$difference = $checkDiffernce - $parsedOld[$j];
				if($checkDiffernce == '-1'){//isn't ranked
					$formatDifference[$i][] = $checkDiffernce;
				}
				else if($difference == 0){//no difference
					$formatDifference[$i][] = number_format(abs($checkDiffernce));
				}
				else if($difference > 0){//up
					if($j == 0)
						$formatDifference[$i][] = number_format($checkDiffernce).' <font style="color:red;">-'.number_format(abs($difference)).'</font>';
					else
						$formatDifference[$i][] = number_format($checkDiffernce).' <font style="color:green;">+'.number_format(abs($difference)).'</font>';
				}
				else if($difference < 0){//down
					$formatDifference[$i][] = number_format($checkDiffernce).' <font style="color:green;">+'.number_format(abs($difference)).'</font>';
				}
				$j++;
			}
			$i++;
		}
		return $formatDifference;
	}

	public function outputSkills($old,$new){
		$data = $this->compare($old, $new);
		$skills = array_slice($data, 0, ($this->version == "old" ? 24 : 26), true);
		$i = 0;
		$out = '<table><tr><th></th><th>Skills</th><th>Rank</th><th>Level</th><th>XP</th></tr>';

		foreach($skills as $parsed){
			if($parsed[0] == '-1'){
				$out .= '<tr><td align="center"><img src="'.$this->skillImageLinks[$i].'"/></td><td>'.($this->version == "old" ? $this->skillNamesOld[$i] : $this->skillNamesNew[$i]).'</td><td colspan="3" align="right">Not Ranked</td></tr>';
				$i++;
				continue;
			}
			$out .= '<tr><td><img src="'.$this->skillImageLinks[$i].'"/></td><td>'.($this->version == "old" ? $this->skillNamesOld[$i] : $this->skillNamesNew[$i]).'</td><td>'.$parsed[0].'</td><td>'.$parsed[1].'</td><td>'.$parsed[2].'</td></tr>';
			$i++;
		}

		$out .= '</table>';
		return $out;
	}

	public function outputMiniGames($old,$new){
		$data = $this->compare($old, $new);
		$minigames = ($this->version == "old" ? array() : array_slice($data, 26, 15, true));
		$i = 0;
		$out = '<table><tr><th></th><th colspan="2">Game</th><th colspan="2">Rank</th><th>Score</th></tr>';

		foreach ($minigames as $parsed){
			if($parsed[0] == '-1'){
				$out .= '<tr><td align="center"><img src="'.($this->version == "old" ? $this->miniImagesLinksOld[$i] : $this->miniImagesLinksNew[$i]).'"/></td><td colspan="2">'.($this->version == "old" ? $this->miniNamesOld[$i] : $this->miniNamesNew[$i]).'</td><td colspan="3" align="right">Not Ranked</td></tr>';
				$i++;
				continue;
			}
			$out .= '<tr><td align="center"><img src="'.($this->version == "old" ? $this->miniImagesLinksOld[$i] : $this->miniImagesLinksNew[$i]).'"/></td><td colspan="2">'.($this->version == "old" ? $this->miniNamesOld[$i] : $this->miniNamesNew[$i]).'</td><td colspan="2">'.$parsed[0].'</td><td>'.$parsed[1].'</td></tr>';
			$i++;
		}

		$out .= '</table>';
		return $out;
	}

}

$rs = new rs($version,$user);
//$user_info = $rs->queryHiScores(); //grab hs info from RuneScape : Have this in __construct instead
if(!empty($rs->newData)){ //returned 200 - valid user
	$old_info = $rs->checkUser($rs->newData);
	$user_info = $rs->newData;
	$user_info = $rs->compare($user_info, $old_info);
}
else{ //something with the curl
	die($user." not a valid RuneScape user");
}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>RSHiScores - #<?=ucfirst($rs->username);?></title>
		<meta charset="UTF-8" />
		<meta name="robots" content="noindex, nofollow, noarchive, nosnippet, noodp" /> <!-- I fucking hate robots... -->
		<meta name="description" content="RuneScape HiScore Grabber" />
		<style type="text/css">
			html,body { background-color: #000 !important;color: #777 !important;font-size: 1.1em !important;padding: 1em 2em !important;font-family: Cambria !important; }
			div { float: right;text-align: right !important; }
			font{ font-weight:bold !important; }
		</style>
	</head>
	<body>
		<h1><?=ucfirst($rs->username);?></h1>
		<h5>
			Since: <?=date("j-F-Y g:i:s A", $old_info['last_updated']);?>
			<form name="input" action="<?=$_SERVER['PHP_SELF'];?>" method="get">
				<input type="hidden" name="user" value="<?=$rs->username;?>" />
				<input type="hidden" name="version" value="<?=$version;?>" />
				<input type="hidden" name="update" value="1" />
				<input type="submit" value="Update Now" />
			</form>
		</h5>
<?php
	echo $rs->outputSkills($user_info, $old_data);
	echo ($version == "oldschool" ? '' : $rs->outputMiniGames($user_info, $old_data));
?>
	</body>
</html>
