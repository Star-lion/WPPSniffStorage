<?php
include 'includes/header.php';
if (!(isset($pagenum))) $pagenum = 1; 
$sql = "SELECT DISTINCT(Build) FROM SniffData";
$builds = "";
if ($result = $mysqlCon->query($sql))
    while ($myrow = $result->fetch_object())
    {
        $builds .= '<li><input type="checkbox" name="builds" value="'.$myrow->Build.'"';
        if (in_array($myrow->Build,explode(",",$_POST["builds"]))) $builds .= ' checked';
        $builds .= '> &nbsp; '.$buildVersions[$myrow->Build].'</li>';
    }
$searchcount = $_POST['searches'] ? $_POST['searches'] : 1;
?>
<script src="includes/jquery.js"></script>
<script>
function BuildClone() {
    var types = <? echo json_encode($types); ?>;
    var entriescount = parseInt($('#searches').val());
    var entriesDiv = $(document.createElement('div')).attr('id','entries'+entriescount);
    $(entriesDiv).append($(document.createElement('hr')).css('clear','both').css('margin-bottom','4px'));
    //$(entriesDiv).append($(document.createElement('p')).css('clear','both').append($(document.createElement('input')).attr('type','checkbox').attr('name','andor'+entriescount)).append(' Previous search OR new search (Defaults as AND)'));    
    $(entriesDiv).append($(document.createElement('label')).attr('for','entryType'+entriescount).text('Entry Type: '));
    var entriesSel = $(document.createElement('select')).attr('name','entryType'+entriescount);
    for (type in types) {
        $(entriesSel).append($(document.createElement('option')).val(types[type]).text(types[type]));
    }
    $(entriesDiv).append($(entriesSel));
    $(entriesDiv).append($(document.createElement('label')).attr('for','entry'+entriescount).text('Entry: '));
    $(entriesDiv).append($(document.createElement('input')).attr('type','text').attr('name','entry'+entriescount).addClass('searchInput'));
    $(entriesDiv).append($(document.createElement('p')).css('display','none').css('clear','both').attr('id','likesentries'+entriescount).append($(document.createElement('input')).attr('type','checkbox').attr('name','likes'+entriescount).val(1)).append(' Use like instead of equals for opcode name.'));    
    $(entriesDiv).append($(document.createElement('input')).attr('type','hidden').attr('name','existingEntries').val(entriescount));
    $('#searches').val(entriescount+1);
    $('#entryContainer').append(entriesDiv);
    $('#entries'+entriescount+' select').change( function(i) {filterSelect(this)});
}
function RemoveClone()
{
    var entriescount = parseInt($('#searches').val());
    if (entriescount > 1)
    {
        $('#entries'+(entriescount-1)).remove();
        $('#searches').val(entriescount-1);
    }
}
function filterSelect(select)
{
    var searchIndex = $(select).attr('name');
    searchIndex = searchIndex.replace('entryType','');
    if ($(select).children('option:selected').val() == 'Opcode Name')
        $('#likesentries'+searchIndex).show();
    else
        $('#likesentries'+searchIndex).hide();
}
</script>
<form name="search" method="post">
    <fieldset>
        <legend>Sniff Search</legend>
        <input type="hidden" name="searches" value="<?php echo $searchcount ?>" id="searches" />
        <div id="entryContainer">
            <p style="float:right;"><a href="#" style="font-size:15px;" onclick="javascript:BuildClone(); return false;">Add New Search</a> | <a href="#" style="font-size:15px;" onclick="javascript:RemoveClone(); return false;">Remove Last Search</a></p>
            <?php for ($l = 0; $l < $searchcount; $l++) ?>
            <div id="entries<?php echo $l ?>" style="clear:left;">
                <label for="entryType<?php echo $l ?>">Entry Type: </label>
                <select name="entryType<?php echo $l ?>" onchange="filterSelect(this)">
                <?
                    for ($i = 0; $i < count($types); $i++)
                    {
                        echo '<option value="'.$types[$i].'"';
                        if ($_POST['entryType'.$l] == $types[$i])
                            echo ' selected';
                        echo '>'.$types[$i].'</option>';
                    }
                ?>
                </select>
                <label for="entry<?php echo $l ?>">Entry: </label><input type="text" name="entry<?php echo $l ?>" class="searchInput" value="<? echo $_POST['entry'.$l] ?>"/>
                <p style="display:none;clear:both;" id="likesentries<?php echo $l ?>"><input type="checkbox" name="likes<?php echo $l ?>" value="1" <? if ($_POST['likes'.$l]) echo 'checked' ?>> Use like instead of equals for opcode name.</p>
            </div>
            <? } ?>
        </div>
        <fieldset class="innerfieldset">
        <legend> Client Version</legend>
            <ul class="buildList" name="buildList">
                <? echo $builds ?>
            </ul>
        </fieldset>
        <input type="submit" name="submit" class="submit" value="Submit" />
    </fieldset>
</form>
<br />
<? 
if ($_POST['submit'])
{
    $sql = "SELECT Build,SniffName,ObjectType,Id,Data,name from (";
    $tmpsql = "SELECT a.Build,a.SniffName,a.ObjectType,a.Id,a.Data,b.name as name FROM SniffData as a LEFT OUTER JOIN objectnames as b on a.id = b.id and a.objecttype = b.objecttype";
    $wherearr = array();
    $likes = $_POST['likes'];
    $wheres = array();
    for ($i = 0; $i < $_POST['searches'];$i++)
    {
        $type = $_POST['entryType'.$i];
        $value = $_POST['entry'.$i];
        if (empty($value) || $type == 'None')
            continue;
        if ($type == 'Opcode Number') $type = 'Opcode';
        if (!$wheres[$type]) $wheres[$type] = array();
        if ($type == 'Opcode Name')
        {
            if (!empty($_POST['likes'.$i]))
            {
                if (!in_array(array( 'Like' => true, 'opcode' => '%'.$value.'%'),$wheres[$type]))
                    array_push($wheres[$type], array('opcode' => '%'.$value.'%',  'Like' => true));
            }
            else
            {
                if (!in_array(array( 'Like' => false, 'opcode' => $value),$wheres[$type]))
                    array_push($wheres[$type], array('opcode' => $value,  'Like' => false));
            }
        } else
        {
            if (in_array($value, $wheres[$type])) continue;
            array_push($wheres[$type],$value);
        }
    }
    foreach ($wheres as $key => $value)
    {
        $where = '';
        $type = $key;
        if ($type == 'Opcode Name' || $type == 'Opcode Number') $type = 'Opcode';
        for ($i = 0; $i < count($value);$i++)
        {
            $valValue = $value[$i];
            if ($key == 'Opcode Name')
            {
                if ($where) $where .= ' OR ';
                if ($valValue['Like']) $where .= "data LIKE '".$mysqlCon->escape_string($valValue['opcode'])."'";
                else $where .= "data = '".$valValue['opcode']."'";
            }
            else {
                if ($where) $where .= ' OR ';
                if (is_numeric($valValue)) $where .= ' a.Id = '.$valValue;
                else
                    $where .= " b.name LIKE '%".$mysqlCon->escape_string($valValue)."%'";
            } 
        }
        $where = "a.ObjectType = '".$type."' AND (".$where.")";
        array_push($wherearr,$where);
    }
    if (!empty($wherearr)){
        for ($i = 0; $i < count($wherearr); $i++)
        {
            if ($i) $sql.=' UNION ALL ';
            $sql .= $tmpsql." WHERE ".$wherearr[$i];
            if ($_POST['builds'])
            {
                $where .= ' AND build in ('.$_POST['builds'].')';
            }
        }
        $sql .= ') as SniffsData GROUP BY SniffName, Id, Data, ObjectType ORDER BY SniffName, ObjectType ASC';
        echo '<pre>';
        if ($result = $mysqlCon->query($sql))
        {
            if ($result->num_rows)
            {
                while ($row = $result->fetch_array(MYSQLI_ASSOC))
                    print_r($row);
            } else {
                echo('No Results Found');
            }
        } else {
            echo('No Results Found');
        }
        echo '</pre></br><br/>'.$sql;
    } else {
        echo "Nothing to Search For";
    }
}
include 'includes/footer.php';
?>  