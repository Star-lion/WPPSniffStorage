<?php
include '../includes/header.php';
?>
<form name="Import" action="import.php" method="post" enctype="multipart/form-data">
<input type="radio" name="importType" value="file" checked /><label for="file">Sql File:</label>
<input type="file" name="file" id="file" />
<br />
OR
<br />
<input type="radio" name="importType" value="text" /><label for="import">Sql Text:</label>
<textarea rows="20" cols="50" name="import"></textarea>
<input type="submit" name="submit" value="Submit" />
</form>
<br /><br /><br />
<?php 
if ($_POST['submit'])
{
	if ($_POST['importType'] == 'file')
	{
		$allowedExts = array("sql", "txt");
		$extension = end(explode(".", $_FILES["file"]["name"]));
		if (($_FILES["file"]["type"] == "application/octet-stream") && in_array($extension, $allowedExts))
		{
			if ($_FILES["file"]["error"] > 0)
			{
				echo "Error: " . $_FILES["file"]["error"] . "<br />";
			}
			else
			{
				$sql = explode(';', file_get_contents($_FILES["file"]["tmp_name"]));
				$n = count($sql) - 1;
				for ($i = 0; $i < $n; $i++) {
					$query = $sql[$i];
					if (strtolower(substr(trim($query),0,6)) == 'insert')
					{
						$result = $mysqlCon->query($query);
						if ($mysqlCon->error)
						{
							echo ('<p>Query: <br><tt>' . $query .
							'</tt><br>failed. MySQL error: ' . $mysqlCon->error);
							break;
						}
						
					}
				}
			}
		}
		else
		{
			echo "Invalid file";
		}
	} else {
		
		$sql = explode(';', str_replace('\\','',$_POST['import']));
				$n = count($sql) - 1;
				for ($i = 0; $i < $n; $i++) {
					$query = $sql[$i];
					if (strtolower(substr(trim($query),0,6)) == 'insert')
					{
						$result = $mysqlCon->query($query);
						if ($mysqlCon->error)
						{
							echo ('<p>Query: <br><tt>' . $query .
							'</tt><br>failed. MySQL error: ' . $mysqlCon->error);
							break;
						}
						
					}
				}
	}
}
include '../includes/footer.php';
?>
