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
        <input type="hidden" name="searches" value="1" id="searches" />
        <div id="entryContainer">
            <p style="float:right;"><a href="#" style="font-size:15px;" onclick="javascript:BuildClone(); return false;">Add New Search</a> | <a href="#" style="font-size:15px;" onclick="javascript:RemoveClone(); return false;">Remove Last Search</a></p>
            <div id="entries0" style="clear:left;">
                <label for="entryType0">Entry Type: </label>
                <select name="entryType0" onchange="filterSelect(this)">
                <?
                    for ($i = 0; $i < count($types); $i++)
                    {
                        echo '<option value="'.$types[$i].'"';
                        if ($_POST['entryType'] == $types[$i])
                            echo ' selected';
                        echo '>'.$types[$i].'</option>';
                    }
                ?>
                </select>
                <label for="entry0">Entry: </label><input type="text" name="entry0" class="searchInput" />
                <p style="display:none;clear:both;" id="likesentries0"><input type="checkbox" name="likes0" value="1"> Use like instead of equals for opcode name.</p>
            </div>
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
<? 
if ($_POST['submit'])
{
    $sql = "SELECT Build,SniffName,ObjectType,Id,Data FROM SniffData";
    $where = "";
    $likes = $_POST['likes'];
    $wheres = array();
    for ($i = 0; $i < $_POST['searches'];$i++)
    {
        $type = $_POST['entryType'.$i];
        $value = $_POST['entry'.$i];
        if (!$value || $type == 'None')
            continue;
        if ($type == 'Opcode Name')
        {
            if ($_POST['likesentries'.$i])
                if (!in_array(array( 'Like' => true, 'opcode' => '%'.$value.'%'),$wheres[$type]))
                    array_push($wheres[$type], array('opcode' => '%'.$value.'%',  'Like' => true));
            else
                if (!in_array(array( 'Like' => false, 'opcode' => $value),$wheres[$type]))
                    array_push($wheres[$type], array('opcode' => $value,  'Like' => false));
        } else
        {
            if ($type == 'Opcode Number') $type = 'Opcode';
            if (empty($wheres[$type])) $wheres[$type] = array();
            if (in_array($value, $wheres[$type])) continue;
            array_push($wheres[$type],$value);
        }
    }
    
    print_r($wheres);
    
    foreach ($wheres as $key => $value)
    {
        foreach ($value as $valKey => $valValue)
        {
            if ($key == 'Opcode Name')
                $where .= 
        }
    }
    die();
    if ($_POST['opcodeName'])
    {
        $where .= "((ObjectType = 'Opcode' AND data";
        if ($likes)
            $where .= " like '%".$mysqlCon->escape_string($_POST['opcodeName'])."%')";
        else 
            $where .= " = '".$mysqlCon->escape_string($_POST['opcodeName'])."')";
    }
    
    if ($_POST['opcodeNum'])
    {
        if ($where)
        {
            $where .= " OR ";
            $where .= "(ObjectType = 'Opcode' AND id = '".$mysqlCon->escape_string($_POST['opcodeNum'])."'))";
        } else
            $where .= "(ObjectType = 'Opcode' AND id = '".$mysqlCon->escape_string($_POST['opcodeNum'])."')";
    } else
        $where .= ')';
    
    if ($_POST['entryType'] != 'None' && $_POST['entry'])
    {
        if ($where)
            $where .= " AND ";
        $where .= "(ObjectType = '".$_POST['entryType']."' AND id = '".$mysqlCon->escape_string($_POST['entry'])."')";
    }
    
    if ($_POST['builds'])
    {
        if ($where)
            $where .= " AND ";
        $where .= 'build in ('.$_POST['builds'].')';
    }
    if ($where) $sql .= " WHERE ";
    echo $sql.$where;
}
include 'includes/footer.php';
?>