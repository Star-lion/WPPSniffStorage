<?php
$config = parse_ini_file("config.ini");
$mysqlCon = new mysqli($config['DBhost'],$config['DBuser'],$config['DBpass'],$config['DBname']);
$types = array('None', 'Spell', 'Map', 'LFGDungeon', 'Battleground', 'Unit', 'GameObject', 'Item', 'Quest', 'PageText', 'NpcText', 'Gossip', 'Zone', 'Area', 'Phase', 'Player', 'Opcode Name', 'Opcode Number');
$buildVersions = array(0 => "Zero",5875 => "1.12.1 5875",6180 => "2.0.1 6180",6299 => "2.0.3 6299",6337 => "2.0.6 6337",6692 => "2.1.0 6692",6739 => "2.1.1 6739",6803 => "2.1.2 6803",6898 => "2.1.3 6898",7272 => "2.2.0 7272",7318 => "2.2.2 7318",7359 => "2.2.3 7359",7561 => "2.3.0 7561",7741 => "2.3.2 7741",7799 => "2.3.3 7799",8089 => "2.4.0 8089",8125 => "2.4.1 8125",8209 => "2.4.2 8209",8606 => "2.4.3 8606",9056 => "3.0.2 9056",9183 => "3.0.3 9183",9464 => "3.0.8 9464",9506 => "3.0.8a 9506",9551 => "3.0.9 9551",9767 => "3.1.0 9767",9806 => "3.1.1 9806",9835 => "3.1.1a 9835",9901 => "3.1.2 9901",9947 => "3.1.3 9947",10192 => "3.2.0 10192",10314 => "3.2.0a 10314",10482 => "3.2.2 10482",10505 => "3.2.2a 10505",10958 => "3.3.0 10958",11159 => "3.3.0a 11159",11685 => "3.3.3 11685",11723 => "3.3.3a 11723",12213 => "3.3.5 12213",12340 => "3.3.5a 12340",13164 => "4.0.1 13164",13205 => "4.0.1a 13205",13329 => "4.0.3 13329",13596 => "4.0.6 13596",13623 => "4.0.6a 13623",13914 => "4.1.0 13914",14007 => "4.1.0a 14007",14333 => "4.2.0 14333",14480 => "4.2.0a 14480",14545 => "4.2.2 14545",15005 => "4.3.0 15005",15050 => "4.3.0 15050",15211 => "4.3.2 15211",15354 => "4.3.3 15354",15595 => "4.3.4 15595");

function BuildSearchList($result, $nobuild)
{
    if ($nobuild) $display = " style='display:none'";
    $html = '<table id="searchresults" cellspacing="0"><tr class="headerrow"><td class="first">Sniff Name</td><td'.$display.'>Build</td><td>Type</td><td>Id</td><td>Name</td></tr>';
    $otherrow = false;
    while ($row = $result->fetch_array(MYSQLI_ASSOC))
    {
        
        $html .= '<tr';
        if ($otherrow) $html .= ' class="otherrow"';
        $html .= '><td class="first"><a href="'.$row['SniffName'].'" target="blank">'.$row['SniffName'].'</a></td><td'.$display.'>'.$row['Build'].'</td><td style="text-align:center;">'.$row['ObjectType'].'</td><td>'.$row['Id'].'</td><td>';
        if ($row['ObjectType'] == 'Opcode')
            $html .= $row['Data'];
        else
            $html .= $row['name'];
        $html .= '</td></tr>';
        $otherrow = !$otherrow;
    }
    $html .= '</table>';
    return $html;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="/includes/style.css" />
<title>WPP Sniff Data Storage</title>
</head>

<body>
    <div id="page">
      <div id="pagetop">
        <div class="links">    	
            <ul>
				<?php
				$directory = end(explode('/',getcwd()));
				if (strtolower($directory) == 'admin')
				{?>
					<li><a href="delete.php">Delete Sniff Data</a></li>
					<li><a href="import.php">Import New Sniff Data</a></li>					
				<?php
				} else 
				{?>
					<li><a href="admin">Admin</a></li>
				<?php }
				/* temporary placeholder for data
           	   	<li><a href="#">Home</a></li>
               	<li><a href="#">Products</a></li>
                <li><a href="#">Services</a></li>
                <li><a href="#">About</a></li>
                <li><a href="#">Contact</a></li>
				*/
				?>
				<li><a href="/index.php">Home</a></li>
            </ul>
        </div>
     </div>
	 <div id="header"></div>
	 <div id="main">
		<div class="content">
			<div class="main_top">&nbsp;</div>
           	<div class="main_body">